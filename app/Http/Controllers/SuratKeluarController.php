<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SuratKeluarController extends Controller
{
    public function index()
    {
        $suratKeluar = Surat::where('jenis', 'keluar')->latest()->paginate(15);
        return view('suratkeluar.index', compact('suratKeluar'));
    }

    public function create(Request $request)
    {
        // Jika ada parameter template_id, tampilkan langkah 2 (form pembuatan)
        if ($request->has('template_id')) {
            $template = TemplateSurat::findOrFail($request->template_id);
            return view('suratkeluar.create-step2', compact('template'));
        }

        // Jika tidak, tampilkan langkah 1 (pemilihan template)
        $templates = TemplateSurat::all();
        return view('suratkeluar.create-step1', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'template_id' => 'required|exists:template_surat,id',
            'placeholders' => 'nullable|array',
            'konten_final' => 'required|string',
        ]);

        $surat = Surat::create([
            'perihal' => $validated['perihal'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'jenis' => 'keluar',
            'status' => 'draft',
            'pembuat_id' => Auth::id(),
            'konten' => $validated['konten_final'],
        ]);

        return redirect()->route('surat-keluar.show', $surat)->with('success', 'Draf surat berhasil disimpan.');
    }

    public function show(Surat $surat)
    {
        // Pastikan hanya surat keluar yang bisa diakses melalui controller ini
        if ($surat->jenis !== 'keluar') {
            abort(404);
        }

        return view('suratkeluar.show', compact('surat'));
    }

    public function approve(Request $request, Surat $surat)
    {
        $request->validate([
            'penyetuju_id' => 'required|exists:users,id',
            'with_signature' => 'nullable|boolean',
        ]);

        // 1. Update status surat
        $surat->status = 'disetujui';
        $surat->penyetuju_id = $request->penyetuju_id;
        $surat->save();

        $penyetuju = User::find($request->penyetuju_id);

        // 2. Siapkan data untuk PDF
        $verificationUrl = route('surat.verify', ['id' => $surat->id]); // Asumsi ada route verifikasi
        $qrCode = base64_encode(QrCode::format('png')->size(100)->generate($verificationUrl));

        $signatureImagePath = null;
        if ($request->boolean('with_signature') && $penyetuju->signature_image_path) {
            $signatureImagePath = storage_path('app/public/' . $penyetuju->signature_image_path);
        }

        // 3. Generate PDF
        $pdf = Pdf::loadView('pdf.surat', [
            'surat' => $surat,
            'qrCode' => $qrCode,
            'signatureImagePath' => $signatureImagePath,
        ]);

        // 4. Simpan PDF ke storage
        $filename = 'surat-keluar-' . $surat->id . '-' . time() . '.pdf';
        Storage::disk('public')->put('surat-final/' . $filename, $pdf->output());

        // 5. Simpan path PDF ke database
        $surat->final_pdf_path = 'surat-final/' . $filename;
        $surat->save();

        return redirect()->route('surat-keluar.show', $surat)->with('success', 'Surat berhasil disetujui dan PDF telah digenerate.');
    }
}
