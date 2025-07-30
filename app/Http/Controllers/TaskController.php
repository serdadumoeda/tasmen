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
use App\Models\SubTask;

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
        $task->assignees()->sync($validated['assignees']);

        // Kirim notifikasi ke semua user yang ditugaskan
        $usersToNotify = User::find($validated['assignees']);
        Notification::send($usersToNotify, new TaskAssigned($task));

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
        // Otorisasi, pastikan user yang berhak yang bisa memindahkan kartu.
        $this->authorize('update', $task);

        // Validasi input status dari frontend.
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,in_progress,for_review,completed'],
        ]);

        $newStatus = $validated['status'];
        
        // Perbarui status dan progress secara logis dan sederhana.
        $task->status = $newStatus;

        if ($newStatus === 'completed') {
            $task->progress = 100;
        } elseif ($newStatus === 'pending') {
            $task->progress = 0;
        } elseif ($task->progress == 100) {
            // Jika kartu ditarik dari 'Selesai' ke kolom lain, set progress agar tidak 100% lagi.
            $task->progress = 90;
        }

        $task->save();

        // Kirim respons sukses.
        return response()->json([
            'message' => 'Status tugas berhasil diperbarui ke: ' . $newStatus
        ]);
    }

    public function toggleSubTask(SubTask $subTask)
    {
        // Otorisasi: Pastikan pengguna yang login boleh mengubah tugas ini
        $this->authorize('update', $subTask->task);

        // Ubah status selesai (jika true jadi false, jika false jadi true)
        $subTask->update(['is_completed' => !$subTask->is_completed]);

        // Hitung ulang progress tugas utama
        $subTask->task->recalculateProgress();

        // Siapkan data untuk dikirim kembali sebagai JSON
        $completed_subtasks = $subTask->task->subTasks()->where('is_completed', true)->count();
        $total_subtasks = $subTask->task->subTasks()->count();

        return response()->json([
            'message' => 'Status sub-tugas berhasil diperbarui.',
            'task_progress' => $subTask->task->progress,
            'completed_subtasks' => $completed_subtasks,
            'total_subtasks' => $total_subtasks,
        ]);
    }

}