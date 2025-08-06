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
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskRequiresApproval;
use App\Models\SubTask;
use App\Models\Unit; // Tambahkan model Unit

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

        // PERBAIKAN: Hindari Mass Assignment Vulnerability dengan menggunakan data yang sudah divalidasi.
        $task = $project->tasks()->create([
            'title' => $validated['title'],
            'deadline' => $validated['deadline'],
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            // Properti lain yang aman bisa ditambahkan di sini.
        ]);
        
        // Simpan relasi many-to-many
        $task->assignees()->sync($validated['assignees']);

        // Kirim notifikasi ke semua user yang ditugaskan
        $usersToNotify = User::find($validated['assignees']);
        Notification::send($usersToNotify, new TaskAssigned($task));

        return redirect()->route('projects.show', $project)->with('success', 'Tugas baru berhasil ditambahkan!');
    }

    public function edit(Task $task)
    {
        // Eager load relasi untuk efisiensi
        $task->load('assignees', 'attachments', 'project.members');
        $this->authorize('update', $task);
        
        $user = Auth::user();
        $assignableUsers = collect();

        if ($task->project_id) {
            // Untuk tugas proyek, ambil anggota tim proyek
            $assignableUsers = $task->project->members()->orderBy('name')->get();
        } else {
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

        // Gunakan view 'tasks.edit' yang lebih superior untuk kedua jenis tugas
        return view('tasks.edit', ['task' => $task, 'projectMembers' => $assignableUsers]);
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
            'status' => 'required|string|in:pending,in_progress,completed,pending_review',
            'priority' => 'required|in:low,medium,high',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            // PERBAIKAN: Menambahkan validasi untuk unggahan file
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ]);

        $task->fill($validated);
        
        // --- LOGIKA ALUR PERSETUJUAN (jika tugas proyek) ---
        if($task->project_id){
            $user = auth()->user();
            // Jika progress 100% dan status sebelumnya BUKAN 'completed', jalankan alur persetujuan.
            if ((int)$validated['progress'] === 100 && $task->getOriginal('status') !== 'completed') {
                if ($user->id !== $task->project->leader_id && $user->id !== $task->project->owner_id) {
                    $task->status = 'pending_review';
                } else {
                    $task->status = 'completed';
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
                $path = $file->store('public/attachments');
                $task->attachments()->create([
                    'user_id' => auth()->id(),
                    'filename' => $file->getClientOriginalName(),
                    'path' => \Illuminate\Support\Facades\Storage::url($path)
                ]);
                $redirect->with('success', 'Tugas berhasil diperbarui dan file berhasil diunggah.');
            } catch (\Exception $e) {
                $redirect->with('error', 'Tugas berhasil diperbarui, tetapi file gagal diunggah.');
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
        // Eager load relasi yang dibutuhkan oleh TaskPolicy untuk menghindari N+1 query problem
        // dan memastikan data tersedia saat otorisasi.
        $task->load('assignees', 'project.owner', 'project.leader');

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

    public function storeAttachment(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->store('public/attachments');

        $attachment = $task->attachments()->create([
            'user_id' => auth()->id(),
            'filename' => $file->getClientOriginalName(),
            'path' => \Illuminate\Support\Facades\Storage::url($path)
        ]);

        return response()->json([
            'success' => true,
            'attachment_html' => view('partials._attachment-item', compact('attachment'))->render(),
        ]);
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

}