<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdHocTaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar tugas ad-hoc.
     */
    public function index()
    {
        $user = Auth::user();
        
        $query = Task::whereNull('project_id')->with('assignees')->latest();

        // Jika Pimpinan, tampilkan semua tugas ad-hoc yang melibatkan dirinya atau bawahannya.
        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds[] = $user->id; // Termasuk diri sendiri

            $query->whereHas('assignees', function ($q) use ($subordinateIds) {
                $q->whereIn('user_id', $subordinateIds);
            });
        } else {
            // Jika Staf, hanya tampilkan tugas yang ditugaskan kepadanya.
            $query->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $assignedTasks = $query->get();

        return view('adhoc-tasks.index', compact('assignedTasks'));
    }

    /**
     * Menampilkan form untuk membuat tugas ad-hoc baru.
     */
    public function create()
    {
        $user = Auth::user();
        $assignableUsers = collect();

        // Jika manajer, bisa menugaskan ke diri sendiri dan bawahan
        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds[] = $user->id; 
            $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        } else {
            // Jika staf, list hanya berisi diri sendiri (meskipun tidak ditampilkan di form)
            $assignableUsers->push($user);
        }
        
        return view('adhoc-tasks.create', compact('assignableUsers'));
    }

    /**
     * Menyimpan tugas ad-hoc baru ke database.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'estimated_hours' => 'required|numeric|min:0.1',
            // PERBAIKAN: Memastikan status yang dikirim valid.
            'status' => 'required|in:pending,in_progress,completed',
            'progress' => 'required|integer|min:0|max:100',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120',
        ]);
        
        $assigneeIds = [];
        if ($user->canManageUsers()) {
            $request->validate(['assignees' => 'required|array', 'assignees.*' => 'exists:users,id']);
            $assigneeIds = $request->assignees;
        } else {
            $assigneeIds[] = $user->id;
        }

        // Menggunakan fill untuk keamanan dan kemudahan
        $task = new Task();
        $task->fill($validated);
        $task->project_id = null; // Menandakan ini tugas ad-hoc
        $task->save();
        
        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $path = $file->store('attachments', 'public');
            $task->attachments()->create([
                'user_id' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'path' => $path
            ]);
        }
        
        $task->assignees()->sync($assigneeIds);
        $usersToNotify = User::find($assigneeIds);
        foreach ($usersToNotify as $recipient) {
            $recipient->notify(new \App\Notifications\TaskAssigned($task));
        }

       
        return redirect()->route('adhoc-tasks.index')->with('success', 'Tugas harian berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        // Gunakan TaskPolicy untuk otorisasi
        $this->authorize('update', $task);

        // Pastikan ini adalah ad-hoc task
        if ($task->project_id) {
            abort(404);
        }

        $task->load('assignees');

        $user = Auth::user();
        $assignableUsers = collect();
        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds->push($user->id);
            $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        } else {
            $assignableUsers->push($user);
        }

        return view('adhoc-tasks.edit', compact('task', 'assignableUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'progress' => 'required|integer|min:0|max:100',
            'priority' => 'required|in:low,medium,high',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120',
        ]);

        $task->update($validated);

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

        return redirect()->route('adhoc-tasks.index')->with('success', 'Tugas harian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        // Pastikan ini adalah ad-hoc task
        if ($task->project_id) {
            abort(404);
        }

        $task->assignees()->detach();
        $task->delete();

        return redirect()->route('adhoc-tasks.index')->with('success', 'Tugas harian berhasil dihapus.');
    }
}