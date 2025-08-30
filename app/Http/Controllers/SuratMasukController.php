<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\LampiranSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    public function index()
    {
        $suratMasuk = Surat::where('jenis', 'masuk')->latest()->paginate(15);
        return view('suratmasuk.index', compact('suratMasuk'));
    }

    public function create()
    {
        return view('suratmasuk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'perihal' => 'required|string|max:255',
            'nomor_surat' => 'required|string|max:255|unique:surat,nomor_surat',
            'tanggal_surat' => 'required|date',
            'lampiran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        $surat = Surat::create([
            'perihal' => $validated['perihal'],
            'nomor_surat' => $validated['nomor_surat'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'jenis' => 'masuk',
            'status' => 'diarsipkan', // Default status for incoming mail
            'pembuat_id' => Auth::id(),
        ]);

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $path = $file->store('lampiran-surat', 'public');

            LampiranSurat::create([
                'surat_id' => $surat->id,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'tipe_file' => $file->getClientMimeType(),
                'ukuran_file' => $file->getSize(),
            ]);
        }

        return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil diarsipkan.');
    }

    public function show(Surat $surat)
    {
        if ($surat->jenis !== 'masuk') {
            abort(404);
        }

        // Ambil bawahan dari user yang sedang login untuk pilihan disposisi
        $dispositionUsers = Auth::user()->bawahan;

        $surat->load('lampiran', 'disposisi.penerima');

        return view('suratmasuk.show', compact('surat', 'dispositionUsers'));
    }
}
