<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriorityLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PriorityLevelController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_settings');
        $priorities = PriorityLevel::orderBy('weight')->get();
        return view('admin.priority_levels.index', compact('priorities'));
    }

    public function create()
    {
        Gate::authorize('manage_settings');
        return view('admin.priority_levels.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'key' => 'required|string|unique:priority_levels,key|alpha_dash',
            'label' => 'required|string|max:255',
            'weight' => 'required|integer|min:0',
        ]);

        PriorityLevel::create($validated);

        return redirect()->route('admin.priority-levels.index')->with('success', 'Level prioritas baru berhasil dibuat.');
    }

    public function edit(PriorityLevel $priorityLevel)
    {
        Gate::authorize('manage_settings');
        return view('admin.priority_levels.edit', ['priority' => $priorityLevel]);
    }

    public function update(Request $request, PriorityLevel $priorityLevel)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'key' => 'required|string|alpha_dash|unique:priority_levels,key,' . $priorityLevel->id,
            'label' => 'required|string|max:255',
            'weight' => 'required|integer|min:0',
        ]);

        $priorityLevel->update($validated);

        return redirect()->route('admin.priority-levels.index')->with('success', 'Level prioritas berhasil diperbarui.');
    }

    public function destroy(PriorityLevel $priorityLevel)
    {
        Gate::authorize('manage_settings');

        if ($priorityLevel->tasks()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus level prioritas yang sedang digunakan oleh tugas.');
        }

        $priorityLevel->delete();

        return redirect()->route('admin.priority-levels.index')->with('success', 'Level prioritas berhasil dihapus.');
    }
}
