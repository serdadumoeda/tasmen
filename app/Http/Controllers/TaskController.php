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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskRequiresApproval;
use App\Models\SubTask;
use App\Models\Unit; // Tambahkan model Unit
use App\Models\TaskStatus;
use App\Models\PriorityLevel;

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
            'assignees' => 'required|array',
            'assignees.*' => 'exists:users,id',
        ]);

        $defaultStatus = TaskStatus::where('key', 'pending')->first();
        $defaultPriority = PriorityLevel::where('key', 'medium')->first();

        $task = $project->tasks()->create([
            'title' => $validated['title'],
            'deadline' => $validated['deadline'],
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'task_status_id' => $defaultStatus->id,
            'priority_level_id' => $defaultPriority->id,
        ]);
        
        $task->assignees()->sync($validated['assignees']);

        $usersToNotify = User::find($validated['assignees']);
        Notification::send($usersToNotify, new TaskAssigned($task));

        return redirect()->route('projects.show', $project)->with('success', 'Tugas baru berhasil ditambahkan!');
    }

    public function edit(Task $task)
    {
        $task->load('assignees', 'attachments', 'project.members');
        $this->authorize('update', $task);
        
        $user = Auth::user();
        $assignableUsers = collect();

        if ($task->project_id) {
            $assignableUsers = $task->project->members()->orderBy('name')->get();
        } else {
            if ($user->canManageUsers()) {
                $subordinateIds = $user->getAllSubordinateIds();
                $subordinateIds->push($user->id);
                $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
            } else {
                $assignableUsers->push($user);
            }
        }

        $statuses = TaskStatus::all();
        $priorities = PriorityLevel::all();

        return view('tasks.edit', [
            'task' => $task,
            'projectMembers' => $assignableUsers,
            'statuses' => $statuses,
            'priorities' => $priorities,
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'progress' => 'required|integer|min:0|max:100',
            'task_status_id' => 'required|exists:task_statuses,id',
            'priority_level_id' => 'required|exists:priority_levels,id',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ]);

        $task->fill($validated);
        
        if($task->project_id){
            $user = auth()->user();
            $originalStatusKey = $task->getOriginal('task_status_id') ? TaskStatus::find($task->getOriginal('task_status_id'))->key : null;
            $newStatus = TaskStatus::find($validated['task_status_id']);

            if ((int)$validated['progress'] === 100 && $originalStatusKey !== 'completed') {
                if ($user->id !== $task->project->leader_id && $user->id !== $task->project->owner_id) {
                    $reviewStatus = TaskStatus::where('key', 'for_review')->first();
                    if ($reviewStatus) $task->task_status_id = $reviewStatus->id;
                } else {
                    $completedStatus = TaskStatus::where('key', 'completed')->first();
                    if ($completedStatus) $task->task_status_id = $completedStatus->id;
                }
            }
        }
        
        $task->save();

        if ($request->has('assignees')) {
            $task->assignees()->sync($request->input('assignees', []));
        }
       
        $redirectRoute = $task->project_id
            ? route('projects.show', $task->project_id)
            : route('adhoc-tasks.index');

        $redirect = redirect($redirectRoute);

        if ($request->hasFile('file_upload')) {
            try {
                $file = $request->file('file_upload');
                $path = $file->store('attachments', 'public');
                $task->attachments()->create([
                    'user_id' => auth()->id(),
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path
                ]);
                $redirect->with('success', 'Tugas berhasil diperbarui dan file berhasil diunggah.');
            } catch (\Exception $e) {
                $redirect->with('error', 'Tugas berhasil diperbarui, tetapi file gagal diunggah: ' . $e->getMessage());
            }
        } else {
            $redirect->with('success', 'Tugas berhasil diperbarui.');
        }

        return $redirect;
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        $projectId = $task->project_id;
        $task->assignees()->detach();
        $task->delete();
        
        if ($projectId) {
            return redirect()->route('projects.show', $projectId)->with('success', 'Tugas berhasil dihapus.');
        } else {
            return redirect()->route('adhoc-tasks.index')->with('success', 'Tugas harian berhasil dihapus.');
        }
    }

    public function approve(Task $task)
    {
        $this->authorize('approve', $task);
        $completedStatus = TaskStatus::where('key', 'completed')->first();

        if ($completedStatus) {
            $task->update([
                'task_status_id' => $completedStatus->id,
                'progress' => 100,
            ]);
        }

        return redirect()->back()->with('success', 'Tugas telah disetujui dan diselesaikan.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $task->load('assignees', 'project.owner', 'project.leader');
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::exists('task_statuses', 'key')],
        ]);

        $newStatus = TaskStatus::where('key', $validated['status'])->first();
        
        if ($newStatus) {
            $task->task_status_id = $newStatus->id;

            if ($newStatus->key === 'completed') {
                $task->progress = 100;
            } elseif ($newStatus->key === 'pending') {
                $task->progress = 0;
            } elseif ($task->progress == 100) {
                $task->progress = 90;
            }
            $task->save();
        }

        return response()->json([
            'message' => 'Status tugas berhasil diperbarui ke: ' . ($newStatus->label ?? '')
        ]);
    }

    public function storeAttachment(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('attachments', 'public');

            $attachment = $task->attachments()->create([
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path
            ]);

            return response()->json([
                'success' => true,
                'attachment_html' => view('partials._attachment-item', compact('attachment'))->render(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleSubTask(SubTask $subTask)
    {
        $subTask->load('task.subTasks');
        $this->authorize('update', $subTask->task);

        $subTask->update(['is_completed' => !$subTask->is_completed]);
        $subTask->task->recalculateProgress();

        $completed_subtasks = $subTask->task->subTasks->where('is_completed', true)->count();
        $total_subtasks = $subTask->task->subTasks->count();

        return response()->json([
            'message' => 'Status sub-tugas berhasil diperbarui.',
            'task_progress' => $subTask->task->progress,
            'task_status' => $subTask->task->status->key,
            'completed_subtasks' => $completed_subtasks,
            'total_subtasks' => $total_subtasks,
        ]);
    }

    public function quickComplete(Task $task)
    {
        $this->authorize('update', $task);
        $completedStatus = TaskStatus::where('key', 'completed')->first();

        if ($task->task_status_id === $completedStatus->id) {
            return response()->json([
                'message' => 'Tugas sudah selesai.',
                'task_progress' => $task->progress,
                'task_status' => $task->status->key,
            ]);
        }

        if ($completedStatus){
            $task->update([
                'task_status_id' => $completedStatus->id,
                'progress' => 100,
            ]);
        }

        return response()->json([
            'message' => 'Tugas ditandai sebagai selesai.',
            'task_progress' => 100,
            'task_status' => 'completed',
        ]);
    }
}