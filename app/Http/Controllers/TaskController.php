<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Notifications\TaskAssigned;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to_id' => 'required|exists:users,id',
            'deadline' => 'required|date',
        ]);

        $task = $project->tasks()->create($validated);

        $userToNotify = $task->assignedTo;
        $userToNotify->notify(new TaskAssigned($task));

        return redirect()->route('projects.show', $project)->with('success', 'Tugas baru berhasil ditambahkan!');
    }

    public function edit(Task $task)
    {
        $project = $task->project;
        Gate::authorize('view', $project);
        
        $projectMembers = $project->members()->orderBy('name')->get();

        return view('tasks.edit', compact('task', 'projectMembers'));
    }

    public function update(Request $request, Task $task)
    {
        $project = $task->project;
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to_id' => 'required|exists:users,id',
            'deadline' => 'required|date',
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'required|string',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);
        
        $task->update($validated);

        return redirect()->route('projects.show', $task->project)->with('success', 'Tugas berhasil diperbarui!');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        $task->delete();
        
        return redirect()->route('projects.show', $task->project)->with('success', 'Tugas berhasil dihapus.');
    }
}