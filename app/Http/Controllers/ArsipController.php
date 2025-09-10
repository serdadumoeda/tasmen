<?php

namespace App\Http\Controllers;

use App\Models\KlasifikasiSurat;
use App\Models\Berkas;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PageTitleService;
use App\Services\BreadcrumbService;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        $query = Surat::whereIn('status', ['disetujui', 'diarsipkan'])
            ->with(['klasifikasi', 'pembuat'])
            ->latest();

        // Keyword search
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('perihal', 'like', "%{$keyword}%")
                  ->orWhere('nomor_surat', 'like', "%{$keyword}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->input('date_range'));
            if (count($dates) > 0) {
                $startDate = trim($dates[0]);
                $endDate = isset($dates[1]) ? trim($dates[1]) : $startDate;

                $query->whereDate('tanggal_surat', '>=', $startDate)
                      ->whereDate('tanggal_surat', '<=', $endDate);
            }
        }

        // Filter by classification
        if ($request->filled('klasifikasi_id')) {
            $query->where('klasifikasi_id', $request->input('klasifikasi_id'));
        }

        // Filter by type
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        $suratList = $query->paginate(25)->withQueryString();
        $klasifikasi = KlasifikasiSurat::orderBy('kode')->get();
        $berkasList = Berkas::where('user_id', Auth::id())->orderBy('name')->get();

        return view('arsip.index', compact('suratList', 'klasifikasi', 'berkasList'));
    }

    public function storeBerkas(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Auth::user()->berkas()->create($validated);

        return back()->with('success', 'Berkas virtual berhasil dibuat.');
    }

    public function addSuratToBerkas(Request $request)
    {
        $validated = $request->validate([
            'berkas_id' => 'required|exists:berkas,id',
            'surat_ids' => 'required|array|min:1',
            'surat_ids.*' => 'exists:surat,id',
        ]);

        $berkas = Berkas::findOrFail($validated['berkas_id']);

        // Authorize that the user owns the Berkas
        if ($berkas->user_id !== Auth::id()) {
            abort(403);
        }

        $berkas->surat()->syncWithoutDetaching($validated['surat_ids']);

        return back()->with('success', count($validated['surat_ids']) . ' surat berhasil ditambahkan ke berkas "' . $berkas->name . '".');
    }

    public function showWorkflow(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Alur Kerja Arsip Digital');
        $breadcrumbService->add('Arsip Digital', route('arsip.index'));
        $breadcrumbService->add('Alur Kerja');
        return view('arsip.workflow');
    }
}
