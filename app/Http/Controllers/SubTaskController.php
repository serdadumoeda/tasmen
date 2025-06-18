<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $task->subTasks()->create($request->only('title'));
        return back()->with('success', 'Rincian tugas berhasil ditambahkan.');
    }

    public function update(Request $request, SubTask $subTask)
    {
        $subTask->update(['is_completed' => !$subTask->is_completed]);
        return back();
    }

    public function destroy(SubTask $subTask)
    {
        $subTask->delete();
        return back()->with('success', 'Rincian tugas berhasil dihapus.');
    }
}