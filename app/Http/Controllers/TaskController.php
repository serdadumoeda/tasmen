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

    public function update(Request $request, Task $task)
    {
        $user = auth()->user();
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'required|string',
            'estimated_hours' => 'nullable|numeric|min:0',
            'assignees' => 'required|array',
            'assignees.*' => 'exists:users,id',
        ]);

        $newStatus = $request->status;
        $newProgress = $request->progress;

        // LOGIKA INTI ALUR PERSETUJUAN
        try {
            // Kasus 1: Staf menyelesaikan tugas (mengajukan untuk review)
            if ($newProgress == 100 && $task->status != 'completed' && !$this->authorizer->isAllowed('approve', $task)) {
                $newStatus = 'pending_review';
            
                // Kirim notifikasi ke pimpinan
                if ($task->project && $task->project->leader) {
                    $task->project->leader->notify(new TaskRequiresApproval($task, $user));
                }
                // Tambahkan logika notifikasi untuk atasan tugas ad-hoc jika perlu
            }
            
            // Kasus 2: Pimpinan melakukan review (menyetujui atau menolak)
            if ($task->status === 'pending_review' && $this->authorizer->isAllowed('approve', $task)) {
                // Pimpinan hanya boleh mengubah ke 'completed' atau kembali ke 'in_progress'
                if (!in_array($newStatus, ['completed', 'in_progress'])) {
                    return back()->with('error', 'Aksi tidak valid untuk tugas yang sedang direview.');
                }
                // Jika ditolak (kembali in_progress), progress jangan 100%
                if ($newStatus === 'in_progress' && $newProgress == 100) {
                    $newProgress = 90; // Set progress kembali ke 90%
                }
            }
        } catch (\Exception $e) {
            // Tangani jika authorizer tidak ditemukan
        }


        // Update tugas dengan data yang sudah diproses
        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline' => $validated['deadline'],
            'estimated_hours' => $validated['estimated_hours'],
            'status' => $newStatus,
            'progress' => $newProgress,
        ]);
        
        $task->assignees()->sync($request->assignees);

        // Redirect kembali sesuai jenis tugas
        if ($task->project_id) {
            return redirect()->route('projects.show', $task->project)->with('success', 'Tugas berhasil diperbarui!');
        } else {
            return redirect()->route('adhoc-tasks.index')->with('success', 'Tugas harian berhasil diperbarui!');
        }
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
}