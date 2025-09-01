<?php

namespace App\Http\Controllers;

use App\Models\KlasifikasiSurat;
use App\Models\Surat;
use Illuminate\Http\Request;

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

        return view('arsip.index', compact('suratList', 'klasifikasi'));
    }
}
