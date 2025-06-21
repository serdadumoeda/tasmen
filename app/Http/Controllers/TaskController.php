<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Notifications\TaskAssigned;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
        $this->authorize('update', $task);
        
        $project = $task->project;
        $projectMembers = $project->members()->orderBy('name')->get();

        return view('tasks.edit', compact('task', 'projectMembers'));
    }

    public function update(Request $request, Task $task)
    {
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
        
        $task->update($request->except('assignees'));
        
        // Perbarui relasi many-to-many
        $task->assignees()->sync($request->assignees);

        return redirect()->route('projects.show', $task->project)->with('success', 'Tugas berhasil diperbarui!');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        $task->assignees()->detach(); // Hapus relasi di pivot table
        $task->delete();
        
        return redirect()->route('projects.show', $task->project)->with('success', 'Tugas berhasil dihapus.');
    }
}