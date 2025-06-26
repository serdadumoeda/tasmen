<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Notifications\TaskAssigned;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth; 
use App\Notifications\TaskRequiresApproval; 

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'required|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'assignees' => 'required|array', // Ubah dari assigned_to_id menjadi assignees
            'assignees.*' => 'exists:users,id', // Validasi setiap item dalam array
        ]);

        $task = $project->tasks()->create($request->except('assignees'));
        
        // Simpan relasi many-to-many
        $task->assignees()->sync($request->assignees);

        // Kirim notifikasi ke semua user yang ditugaskan
        $usersToNotify = User::find($request->assignees);
        foreach ($usersToNotify as $user) {
            $user->notify(new TaskAssigned($task));
        }

        return redirect()->route('projects.show', $project)->with('success', 'Tugas baru berhasil ditambahkan!');
    }

    public function edit(Task $task)
    {
        $task->load('assignees', 'attachments');
        $this->authorize('update', $task);
        
        // MODIFIKASI: Jika tidak ada project, jangan ambil projectMembers
        if ($task->project_id) {
            $project = $task->project;
            $projectMembers = $project->members()->orderBy('name')->get();
        } else {
            // Untuk tugas ad-hoc, penerima tugas adalah pembuat dan bawahannya
            $user = Auth::user();
            $subordinateIds = $user->children()->pluck('id')->toArray();
            $subordinateIds[] = $user->id;
            $projectMembers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        }

        return view('tasks.edit', compact('task', 'projectMembers'));
    }

    /**
     * Memperbarui tugas yang ada di database.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        // Validasi, termasuk file unggahan baru
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'progress' => 'required|integer|min:0|max:100',
            'priority' => 'required|in:low,medium,high',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            // PERBAIKAN: Menambahkan validasi untuk unggahan file
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120',
        ]);

        $task->fill($validated);
        
        // --- LOGIKA ALUR PERSETUJUAN (jika tugas proyek) ---
        if($task->project_id){
            $user = auth()->user();
            if ((int)$validated['progress'] === 100) {
                if ($user->id !== $task->project->leader_id && $user->id !== $task->project->owner_id) {
                    $task->status = 'pending_review';
                } else {
                    $task->status = 'completed';
                }
            } elseif ($task->progress == 100 && (int)$validated['progress'] < 100) {
                // Jika progres diubah dari 100 ke < 100
                $task->status = 'in_progress';
            }
        }
        
        $task->save();

        if ($request->has('assignees')) {
            $task->assignees()->sync($request->input('assignees', []));
        }
        
       
        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $path = $file->store('attachments', 'public');
            $task->attachments()->create([
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path
            ]);
        }

        return redirect()->back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        $projectId = $task->project_id;
        $task->assignees()->detach();
        $task->delete();
        
        // MODIFIKASI: Logika redirect cerdas
        if ($projectId) {
            return redirect()->route('projects.show', $projectId)->with('success', 'Tugas berhasil dihapus.');
        } else {
            return redirect()->route('adhoc-tasks.index')->with('success', 'Tugas harian berhasil dihapus.');
        }
    }

    public function approve(Task $task)
    {
        // Otorisasi: Hanya user yang berhak bisa menyetujui
        $this->authorize('approve', $task);

        // Setujui tugasnya
        $task->update([
            'progress' => 100,
            'pending_review' => false,
        ]);

        // Beri notifikasi ke anggota tim bahwa tugas mereka telah disetujui (opsional)
        // foreach ($task->assignees as $assignee) {
        //     $assignee->notify(new \App\Notifications\TaskApproved($task));
        // }

        return redirect()->back()->with('success', 'Tugas telah disetujui dan diselesaikan.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => 'required|string|in:pending,in_progress,for_review,completed',
        ]);

        $status = $request->input('status');
        $updateData = [];

        // Logika baru yang lebih eksplisit untuk setiap status
        switch ($status) {
            case 'pending':
                $updateData = ['progress' => 0, 'pending_review' => false];
                break;
            case 'in_progress':
                // Jika tugas belum pernah dikerjakan (progres 0), set ke 10%. Jika sudah, biarkan.
                $updateData = ['progress' => $task->progress > 0 ? $task->progress : 10, 'pending_review' => false];
                break;
            case 'for_review':
                // Ini memperbaiki masalah "tidak bisa diletakkan di review".
                // Jika digeser ke 'Review', set progres ke 100% dan tandai untuk direview.
                $updateData = ['progress' => 100, 'pending_review' => true];
                break;
            case 'completed':
                 // Hanya pimpinan yang bisa langsung geser ke 'completed'
                if (auth()->id() === $task->project->leader_id || auth()->id() === $task->project->owner_id) {
                    $updateData = ['progress' => 100, 'pending_review' => false];
                } else {
                    // Jika staf yang menggeser, akan tetap masuk ke 'for_review' sebagai pengajuan.
                    $updateData = ['progress' => 100, 'pending_review' => true];
                }
                break;
        }

        if (!empty($updateData)) {
            $task->update($updateData);
        }

        return response()->json(['message' => 'Status tugas berhasil diperbarui.']);
    }
}