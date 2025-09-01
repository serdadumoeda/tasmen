<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\User;
use App\Models\LampiranSurat;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SuratKeluarController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $suratKeluar = Surat::where('jenis', 'keluar')->latest()->paginate(15);
        return view('suratkeluar.index', compact('suratKeluar'));
    }

    public function create(Request $request)
    {
        return view('suratkeluar.pilih-metode');
    }

    public function createFromTemplate(Request $request)
    {
        // Jika ada parameter template_id, tampilkan langkah 2 (form pembuatan)
        if ($request->has('template_id')) {
            $template = TemplateSurat::findOrFail($request->template_id);
            $settings = Setting::pluck('value', 'key')->all();
            return view('suratkeluar.create-step2', compact('template', 'settings'));
        }

        // Jika tidak, tampilkan langkah 1 (pemilihan template)
        $templates = TemplateSurat::all();
        return view('suratkeluar.create-step1', compact('templates'));
    }

    public function createUpload()
    {
        return view('suratkeluar.create-upload');
    }

    public function store(Request $request)
    {
        if ($request->input('submission_type') === 'upload') {
            // Logic for uploaded file
            $validated = $request->validate([
                'perihal' => 'required|string|max:255',
                'nomor_surat' => 'nullable|string|max:255|unique:surat,nomor_surat',
                'tanggal_surat' => 'required|date',
                'lampiran' => 'required|file|mimes:pdf|max:5120', // Max 5MB, PDF only
            ]);

            $surat = Surat::create([
                'perihal' => $validated['perihal'],
                'nomor_surat' => $validated['nomor_surat'],
                'tanggal_surat' => $validated['tanggal_surat'],
                'jenis' => 'keluar',
                'status' => 'draft', // Or maybe 'diarsipkan' directly, user decision
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
            return redirect()->route('surat-keluar.show', $surat)->with('success', 'Surat keluar berhasil diunggah dan diarsipkan.');

        } else {
            // Logic for template-based letter
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
    }

    public function show(Surat $surat)
    {
        // Pastikan hanya surat keluar yang bisa diakses melalui controller ini
        if ($surat->jenis !== 'keluar') {
            abort(404);
        }

        $surat->load('pembuat', 'penyetuju', 'lampiran');

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
        // Generate SVG instead of PNG to avoid imagick dependency
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->generate($verificationUrl));

        $signatureImagePath = null;
        if ($request->boolean('with_signature') && $penyetuju->signature_image_path) {
            $signatureImagePath = storage_path('app/public/' . $penyetuju->signature_image_path);
        }

        // Ambil pengaturan umum
        $settings = Setting::pluck('value', 'key')->all();

        // 3. Generate PDF
        $pdf = Pdf::loadView('pdf.surat', [
            'surat' => $surat,
            'qrCode' => $qrCode,
            'signatureImagePath' => $signatureImagePath,
            'settings' => $settings,
        ]);

        // 4. Simpan PDF ke storage
        $filename = 'surat-keluar-' . $surat->id . '-' . time() . '.pdf';
        Storage::disk('public')->put('surat-final/' . $filename, $pdf->output());

        // 5. Simpan path PDF ke database
        $surat->final_pdf_path = 'surat-final/' . $filename;
        $surat->save();

        return redirect()->route('surat-keluar.show', $surat)->with('success', 'Surat berhasil disetujui dan PDF telah digenerate.');
    }

    public function destroy(Surat $surat)
    {
        $this->authorize('delete', $surat);

        // Hapus file PDF final jika ada
        if ($surat->final_pdf_path) {
            Storage::disk('public')->delete($surat->final_pdf_path);
        }

        // Hapus file lampiran (jika surat keluar di-upload)
        foreach ($surat->lampiran as $lampiran) {
            Storage::disk('public')->delete($lampiran->path_file);
        }

        $surat->delete();

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus.');
    }

    public function download(Surat $surat)
    {
        $this->authorize('download', $surat);

        if (!$surat->final_pdf_path || !Storage::disk('public')->exists($surat->final_pdf_path)) {
            return back()->with('error', 'File PDF final tidak ditemukan.');
        }

        return Storage::disk('public')->download($surat->final_pdf_path);
    }
}
