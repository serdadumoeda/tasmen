<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CutiBersama;
use Illuminate\Http\Request;

class CutiBersamaController extends Controller
{
    public function index()
    {
        $cutiBersamas = CutiBersama::orderBy('date', 'desc')->get();
        return view('admin.cuti_bersama.index', compact('cutiBersamas'));
    }

    public function create()
    {
        return view('admin.cuti_bersama.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:cuti_bersamas,date',
            'description' => 'nullable|string',
        ]);

        CutiBersama::create($validated);

        return redirect()->route('admin.cuti_bersama.index')->with('success', 'Tanggal Cuti Bersama berhasil ditambahkan.');
    }

    public function edit(CutiBersama $cutiBersama)
    {
        return view('admin.cuti_bersama.edit', compact('cutiBersama'));
    }

    public function update(Request $request, CutiBersama $cutiBersama)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:cuti_bersamas,date,' . $cutiBersama->id,
            'description' => 'nullable|string',
        ]);

        $cutiBersama->update($validated);

        return redirect()->route('admin.cuti_bersama.index')->with('success', 'Tanggal Cuti Bersama berhasil diperbarui.');
    }

    public function destroy(CutiBersama $cutiBersama)
    {
        $cutiBersama->delete();

        return redirect()->route('admin.cuti_bersama.index')->with('success', 'Tanggal Cuti Bersama berhasil dihapus.');
    }

    public function showWorkflow()
    {
        return view('admin.cuti_bersama.workflow');
    }
}
