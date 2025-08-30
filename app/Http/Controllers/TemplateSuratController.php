<?php

namespace App\Http\Controllers;

use App\Models\TemplateSurat;
use Illuminate\Http\Request;

class TemplateSuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = TemplateSurat::latest()->paginate(10);
        return view('templatesurat.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('templatesurat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'konten' => 'required|string',
        ]);

        TemplateSurat::create($validated);

        return redirect()->route('templatesurat.index')->with('success', 'Template surat berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TemplateSurat $templateSurat)
    {
        // Placeholder
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TemplateSurat $templatesurat)
    {
        return view('templatesurat.edit', compact('templatesurat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TemplateSurat $templatesurat)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'konten' => 'required|string',
        ]);

        $templatesurat->update($validated);

        return redirect()->route('templatesurat.index')->with('success', 'Template surat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TemplateSurat $templatesurat)
    {
        $templatesurat->delete();

        return redirect()->route('templatesurat.index')->with('success', 'Template surat berhasil dihapus.');
    }
}
