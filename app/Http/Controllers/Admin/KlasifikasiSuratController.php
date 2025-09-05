<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KlasifikasiSurat;
use Illuminate\Http\Request;
use App\Services\PageTitleService;
use App\Services\BreadcrumbService;

class KlasifikasiSuratController extends Controller
{
    public function index()
    {
        $klasifikasi = KlasifikasiSurat::with('parent')->orderBy('kode')->paginate(20);
        return view('admin.klasifikasi.index', compact('klasifikasi'));
    }

    public function create()
    {
        $parents = KlasifikasiSurat::orderBy('kode')->get();
        return view('admin.klasifikasi.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:klasifikasi_surat,kode',
            'deskripsi' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:klasifikasi_surat,id',
        ]);

        KlasifikasiSurat::create($validated);

        return redirect()->route('admin.klasifikasi.index')->with('success', 'Klasifikasi berhasil ditambahkan.');
    }

    public function edit(KlasifikasiSurat $klasifikasi)
    {
        $parents = KlasifikasiSurat::where('id', '!=', $klasifikasi->id)->orderBy('kode')->get();
        return view('admin.klasifikasi.edit', compact('klasifikasi', 'parents'));
    }

    public function update(Request $request, KlasifikasiSurat $klasifikasi)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:klasifikasi_surat,kode,' . $klasifikasi->id,
            'deskripsi' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:klasifikasi_surat,id',
        ]);

        $klasifikasi->update($validated);

        return redirect()->route('admin.klasifikasi.index')->with('success', 'Klasifikasi berhasil diperbarui.');
    }

    public function destroy(KlasifikasiSurat $klasifikasi)
    {
        // Add a check to prevent deletion if it has children? For now, we allow it.
        $klasifikasi->delete();
        return redirect()->route('admin.klasifikasi.index')->with('success', 'Klasifikasi berhasil dihapus.');
    }

    public function showWorkflow(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Alur Kerja Klasifikasi Surat');
        $breadcrumbService->add('Klasifikasi Surat', route('admin.klasifikasi.index'));
        $breadcrumbService->add('Alur Kerja');
        return view('admin.klasifikasi.workflow');
    }
}
