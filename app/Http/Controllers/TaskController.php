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
use App\Services\BreadcrumbService;
use App\Services\PageTitleService;

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
            'priority_level_id' => 'required|exists:priority_levels,id',
            'assignees' => 'required|array',
            'assignees.*' => 'exists:users,id',
        ]);

        // Get the default status for a new task
        $defaultStatus = \App\Models\TaskStatus::where('key', 'pending')->first();

        $task = $project->tasks()->create([
            'title' => $validated['title'],
            'deadline' => $validated['deadline'],
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'priority_level_id' => $validated['priority_level_id'],
            'task_status_id' => $defaultStatus->id,
        ]);
        
        // Simpan relasi many-to-many
        $task->assignees()->sync($validated['assignees']);

        // Kirim notifikasi ke semua user yang ditugaskan
        $usersToNotify = User::find($validated['assignees']);
        Notification::send($usersToNotify, new TaskAssigned($task));

        return redirect()->route('projects.show', $project)->with('success', 'Tugas baru berhasil ditambahkan!');
    }

    public function edit(Task $task, BreadcrumbService $breadcrumbService, PageTitleService $pageTitleService)
    {
        // Eager load relasi untuk efisiensi
    $task->load('assignees', 'attachments', 'project.members', 'status');
        $this->authorize('update', $task);
        
        $user = Auth::user();
        $assignableUsers = collect();
        $pageTitleService->setTitle('Edit Tugas: ' . $task->title);

        if ($task->project_id) {
            // Breadcrumb untuk tugas proyek
            $breadcrumbService->add('Dashboard', route('dashboard'));
            $breadcrumbService->add($task->project->name, route('projects.show', $task->project));
            $breadcrumbService->add('Edit Tugas');
            // Untuk tugas proyek, ambil anggota tim proyek
            $assignableUsers = $task->project->members()->orderBy('name')->get();
        } else {
            // Breadcrumb untuk tugas ad-hoc
            $breadcrumbService->add('Dashboard', route('dashboard'));
            $breadcrumbService->add('Tugas Harian', route('adhoc-tasks.index'));
            $breadcrumbService->add('Edit Tugas');
            // Untuk tugas ad-hoc, gunakan logika dari AdHocTaskController
            if ($user->canManageUsers()) {
                // Manajer bisa menugaskan ke diri sendiri dan semua bawahan
                $subordinateIds = $user->getAllSubordinateIds();
                $subordinateIds->push($user->id);
                $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
            } else {
                // Staf hanya bisa menugaskan ke diri sendiri
                $assignableUsers->push($user);
            }
        }

        // Data untuk dropdown
        $statuses = \App\Models\TaskStatus::all();
        $priorities = \App\Models\PriorityLevel::all();

        // Gunakan view 'tasks.edit' yang lebih superior untuk kedua jenis tugas
        return view('tasks.edit', compact('task', 'assignableUsers', 'statuses', 'priorities'));
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
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'progress' => 'required|integer|min:0|max:100',
            'estimated_hours' => 'nullable|numeric|min:0',
            'task_status_id' => 'required|exists:task_statuses,id',
            'priority_level_id' => 'required|exists:priority_levels,id',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'file_upload' => 'nullable|' . config('tasmen.file_uploads.tasks.rules'),
            'is_outside_office_hours' => 'nullable|boolean',
        ]);

        $task->fill($validated);
        $task->is_outside_office_hours = $request->has('is_outside_office_hours');

        // --- LOGIKA ALUR PERSETUJUAN (jika tugas proyek) ---
        // Eager load status to check its key
        $task->load('status');
        if($task->project_id){
            $user = auth()->user();
            // Jika progress 100% dan status sebelumnya BUKAN 'completed', jalankan alur persetujuan.
            if ((int)$validated['progress'] === 100 && $task->status->key !== 'completed') {
                $forReviewStatus = \App\Models\TaskStatus::where('key', 'for_review')->first();
                $completedStatus = \App\Models\TaskStatus::where('key', 'completed')->first();

                if ($user->id !== $task->project->leader_id && $user->id !== $task->project->owner_id) {
                    $task->task_status_id = $forReviewStatus->id;
                } else {
                    $task->task_status_id = $completedStatus->id;
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

        $completedStatus = \App\Models\TaskStatus::where('key', 'completed')->firstOrFail();

        // Setujui tugasnya
        $task->update([
            'task_status_id' => $completedStatus->id,
            'progress' => 100,
        ]);

        return redirect()->back()->with('success', 'Tugas telah disetujui dan diselesaikan.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $task->load('assignees', 'project.owner', 'project.leader');
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', 'string', 'exists:task_statuses,key'],
        ]);

        $newStatusKey = $validated['status'];
        $newStatus = \App\Models\TaskStatus::where('key', $newStatusKey)->firstOrFail();
        
        $task->task_status_id = $newStatus->id;

        if ($newStatus->key === 'completed') {
            $task->progress = 100;
        } elseif ($newStatus->key === 'pending') {
            $task->progress = 0;
        }
        // The arbitrary "90" has been removed. Progress should be recalculated
        // based on subtasks or other logic, not set to a magic number.

        $task->save();

        return response()->json([
            'message' => 'Status tugas berhasil diperbarui ke: ' . $newStatus->label
        ]);
    }

    public function storeAttachment(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'file' => 'required|' . config('tasmen.file_uploads.tasks.rules'),
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
        // PERBAIKAN: Eager load relasi untuk menghindari N+1 saat otorisasi dan re-kalkulasi
        $subTask->load('task.subTasks');

        // Otorisasi: Pastikan pengguna yang login boleh mengubah tugas ini
        $this->authorize('update', $subTask->task);

        // Ubah status selesai (jika true jadi false, jika false jadi true)
        $subTask->update(['is_completed' => !$subTask->is_completed]);

        // Hitung ulang progress tugas utama
        $subTask->task->recalculateProgress();

        // Siapkan data untuk dikirim kembali sebagai JSON
        // Gunakan relasi yang sudah di-load untuk efisiensi
        $completed_subtasks = $subTask->task->subTasks->where('is_completed', true)->count();
        $total_subtasks = $subTask->task->subTasks->count();

        return response()->json([
            'message' => 'Status sub-tugas berhasil diperbarui.',
            'task_progress' => $subTask->task->progress,
            'task_status' => $subTask->task->status,
            'completed_subtasks' => $completed_subtasks,
            'total_subtasks' => $total_subtasks,
        ]);
    }

    public function quickComplete(Task $task)
    {
        $this->authorize('update', $task);

        $completedStatus = \App\Models\TaskStatus::where('key', 'completed')->firstOrFail();

        // Don't do anything if the task is already complete
        if ($task->task_status_id === $completedStatus->id) {
            return response()->json([
                'message' => 'Tugas sudah selesai.',
                'task_progress' => $task->progress,
                'task_status' => $completedStatus->key,
            ]);
        }

        $task->update([
            'task_status_id' => $completedStatus->id,
            'progress' => 100,
        ]);

        return response()->json([
            'message' => 'Tugas ditandai sebagai selesai.',
            'task_progress' => 100,
            'task_status' => $completedStatus->key,
        ]);
    }
}