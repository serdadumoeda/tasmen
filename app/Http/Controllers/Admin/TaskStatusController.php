<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('manage_settings');
        $statuses = TaskStatus::all();
        return view('admin.task_statuses.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('manage_settings');
        return view('admin.task_statuses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'key' => 'required|string|unique:task_statuses,key|alpha_dash',
            'label' => 'required|string|max:255',
        ]);

        TaskStatus::create($validated);

        return redirect()->route('admin.task-statuses.index')->with('success', 'Status baru berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskStatus $taskStatus)
    {
        return redirect()->route('admin.task_statuses.edit', $taskStatus);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskStatus $taskStatus)
    {
        Gate::authorize('manage_settings');
        return view('admin.task_statuses.edit', ['status' => $taskStatus]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskStatus $taskStatus)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'key' => 'required|string|alpha_dash|unique:task_statuses,key,' . $taskStatus->id,
            'label' => 'required|string|max:255',
        ]);

        $taskStatus->update($validated);

        return redirect()->route('admin.task_statuses.index')->with('success', 'Status berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskStatus $taskStatus)
    {
        Gate::authorize('manage_settings');

        if ($taskStatus->tasks()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus status yang sedang digunakan oleh tugas.');
        }

        $taskStatus->delete();

        return redirect()->route('admin.task_statuses.index')->with('success', 'Status berhasil dihapus.');
    }
}
