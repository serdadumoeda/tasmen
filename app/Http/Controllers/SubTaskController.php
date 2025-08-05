<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SubTaskController extends Controller
{
    public function store(Request $request, Task $task)
    {
        Gate::authorize('update', $task);
        $request->validate(['title' => 'required|string|max:255']);
        $subTask = $task->subTasks()->create($request->only('title'));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Rincian tugas berhasil ditambahkan.',
                'subtask_html' => view('partials._subtask-item', compact('subTask'))->render()
            ]);
        }

        return back()->with('success', 'Rincian tugas berhasil ditambahkan.');
    }

    public function update(Request $request, SubTask $subTask)
    {
        Gate::authorize('update', $subTask->task);

        // Gunakan data dari request jika ada, jika tidak toggle
        $isCompleted = $request->has('is_completed') ? $request->is_completed : !$subTask->is_completed;
        $subTask->update(['is_completed' => $isCompleted]);

        // Hitung ulang progress tugas utama
        $subTask->task->recalculateProgress();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Status rincian tugas diperbarui.',
                'task_progress' => $subTask->task->progress
            ]);
        }
        return back();
    }

    public function destroy(SubTask $subTask)
    {
        Gate::authorize('update', $subTask->task);
        $subTask->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Rincian tugas berhasil dihapus.']);
        }

        return back()->with('success', 'Rincian tugas berhasil dihapus.');
    }
}