<?php

namespace App\Http\Controllers;

use App\Models\KlasifikasiSurat;
use App\Models\Berkas;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_surat', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_surat', '<=', $request->input('end_date'));
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
}
