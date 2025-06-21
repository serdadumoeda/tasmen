<?php

namespace App\Http\Controllers;

use App\Models\SpecialAssignment;
use Illuminate\Http\Request;

class SpecialAssignmentController extends Controller
{
    public function index()
    {
        $assignments = auth()->user()->specialAssignments()->latest()->get();
        return view('special-assignments.index', compact('assignments'));
    }

    public function create()
    {
        $assignment = new SpecialAssignment();
        return view('special-assignments.create', compact('assignment'));
    }

    public function store(Request $request)
    {
        // PERBAIKAN: Tambahkan validasi untuk 'status'
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sk_number' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => 'required|in:AKTIF,SELESAI', // <-- Tambahan
        ]);

        $validated['user_id'] = auth()->id();
        SpecialAssignment::create($validated);

        return redirect()->route('special-assignments.index')->with('success', 'SK Penugasan berhasil ditambahkan.');
    }

    public function edit(SpecialAssignment $specialAssignment)
    {
        if ($specialAssignment->user_id !== auth()->id()) {
            abort(403);
        }
        return view('special-assignments.edit', ['assignment' => $specialAssignment]);
    }

    public function update(Request $request, SpecialAssignment $specialAssignment)
    {
        if ($specialAssignment->user_id !== auth()->id()) {
            abort(403);
        }
        
        // PERBAIKAN: Tambahkan validasi untuk 'status'
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sk_number' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => 'required|in:AKTIF,SELESAI', // <-- Tambahan
        ]);

        $specialAssignment->update($validated);

        return redirect()->route('special-assignments.index')->with('success', 'SK Penugasan berhasil diperbarui.');
    }

    public function destroy(SpecialAssignment $specialAssignment)
    {
        if ($specialAssignment->user_id !== auth()->id()) {
            abort(403);
        }
        $specialAssignment->delete();
        return back()->with('success', 'SK Penugasan berhasil dihapus.');
    }
}