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
        $query = Surat::whereIn('status', ['disetujui', 'diarsipkan', 'terverifikasi'])
            ->with(['klasifikasi', 'pembuat', 'berkas']) // Eager load berkas relationship
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

    public function updateBerkas(Request $request, Berkas $berkas)
    {
        // Authorize that the user owns the Berkas
        if ($berkas->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $berkas->update($validated);

        return back()->with('success', 'Berkas berhasil diperbarui.');
    }

    public function destroyBerkas(Berkas $berkas)
    {
        // Authorize that the user owns the Berkas
        if ($berkas->user_id !== Auth::id()) {
            abort(403);
        }

        // Set berkas_id to null for all associated surat
        $berkas->surat()->update(['berkas_id' => null]);

        $berkas->delete();

        return redirect()->route('arsip.index')->with('success', 'Berkas berhasil dihapus.');
    }

    public function moveSuratToBerkas(Request $request)
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

        Surat::whereIn('id', $validated['surat_ids'])->update([
            'berkas_id' => $validated['berkas_id'],
            'status' => 'diarsipkan',
        ]);

        return back()->with('success', count($validated['surat_ids']) . ' surat berhasil dipindahkan ke berkas "' . $berkas->name . '".');
    }

    public function showWorkflow(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Alur Kerja Arsip Digital');
        $breadcrumbService->add('Arsip Digital', route('arsip.index'));
        $breadcrumbService->add('Alur Kerja');
        return view('arsip.workflow');
    }

    public function showBerkas(Request $request, Berkas $berkas)
    {
        // Authorize that the user owns the Berkas
        if ($berkas->user_id !== Auth::id()) {
            abort(403);
        }

        $suratQuery = $berkas->surat()->with('klasifikasi');

        // Apply filters
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $suratQuery->where(function ($q) use ($keyword) {
                $q->where('perihal', 'like', "%{$keyword}%")
                  ->orWhere('nomor_surat', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('klasifikasi_id')) {
            $suratQuery->where('klasifikasi_id', $request->input('klasifikasi_id'));
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->input('date_range'));
            if (count($dates) > 0) {
                $startDate = trim($dates[0]);
                $endDate = isset($dates[1]) ? trim($dates[1]) : $startDate;
                $suratQuery->whereDate('tanggal_surat', '>=', $startDate)
                           ->whereDate('tanggal_surat', '<=', $endDate);
            }
        }

        $suratList = $suratQuery->latest()->paginate(15)->withQueryString();
        $klasifikasi = KlasifikasiSurat::orderBy('kode')->get();
        $berkasList = Berkas::where('user_id', Auth::id())->orderBy('name')->get();


        return view('arsip.show_berkas', compact('berkas', 'suratList', 'klasifikasi', 'berkasList'));
    }
}
