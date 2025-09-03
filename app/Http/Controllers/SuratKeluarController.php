<?php

namespace App\Http\Controllers;

use App\Models\KlasifikasiSurat;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\User;
use App\Models\LampiranSurat;
use App\Models\Setting;
use App\Services\NomorSuratService;
use App\Services\Tte\TteManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        $klasifikasi = KlasifikasiSurat::orderBy('kode')->get();

        if ($request->has('template_id')) {
            $template = TemplateSurat::findOrFail($request->template_id);
            $settings = Setting::pluck('value', 'key')->all();
            return view('suratkeluar.create-step2', compact('template', 'settings', 'klasifikasi'));
        }

        $templates = TemplateSurat::all();
        return view('suratkeluar.create-step1', compact('templates'));
    }

    public function createUpload()
    {
        $klasifikasi = KlasifikasiSurat::orderBy('kode')->get();
        return view('suratkeluar.create-upload', compact('klasifikasi'));
    }

    public function store(Request $request, NomorSuratService $nomorSuratService)
    {
        // Base validation rules for all submission types
        $baseRules = [
            'perihal' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'klasifikasi_id' => 'required|exists:klasifikasi_surat,id',
        ];

        // Type-specific validation rules
        $specificRules = [];
        if ($request->input('submission_type') === 'upload') {
            $specificRules = ['lampiran' => 'required|file|mimes:pdf|max:5120'];
        } else {
            $specificRules = [
                'template_id' => 'required|exists:template_surat,id',
                'placeholders' => 'nullable|array',
                'konten_final' => 'required|string',
            ];
        }

        $validated = $request->validate(array_merge($baseRules, $specificRules));

        // --- Automatic Numbering ---
        $klasifikasi = KlasifikasiSurat::find($validated['klasifikasi_id']);
        $nomorSurat = $nomorSuratService->generate($klasifikasi, Auth::user());
        // --- End Automatic Numbering ---

        $suratData = [
            'perihal' => $validated['perihal'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'klasifikasi_id' => $validated['klasifikasi_id'],
            'nomor_surat' => $nomorSurat,
            'jenis' => 'keluar',
            'status' => 'draft',
            'pembuat_id' => Auth::id(),
            'konten' => $validated['konten_final'] ?? null,
        ];

        $surat = Surat::create($suratData);

        if ($request->input('submission_type') === 'upload') {
            if ($request->hasFile('lampiran')) {
                $file = $request->file('lampiran');
                $path = $file->store('lampiran-surat', 'public');
                // For uploaded letters, the lampiran is the main content.
                // We'll save it to the final_pdf_path to unify the logic.
                $surat->final_pdf_path = $path;
                $surat->save();
            }
            $message = 'Surat keluar berhasil diunggah dan diarsipkan dengan nomor ' . $nomorSurat;
        } else {
            $message = 'Draf surat berhasil disimpan dengan nomor ' . $nomorSurat;
        }

        return redirect()->route('surat-keluar.show', $surat)->with('success', $message);
    }


    public function show(Surat $surat)
    {
        if ($surat->jenis !== 'keluar') {
            abort(404);
        }
        $surat->load('pembuat', 'penyetuju', 'lampiran', 'klasifikasi');
        return view('suratkeluar.show', compact('surat'));
    }

    public function approve(Request $request, Surat $surat, TteManager $tteManager)
    {
        $validated = $request->validate([
            'penyetuju_id' => 'required|exists:users,id',
            'with_signature' => 'nullable|boolean',
        ]);

        // 1. Update status surat & penyetuju
        $surat->status = 'disetujui';
        $surat->penyetuju_id = $validated['penyetuju_id'];
        $surat->save();

        $penyetuju = User::find($validated['penyetuju_id']);

        // 2. Siapkan data untuk PDF
        $verificationUrl = route('surat.verify', ['id' => $surat->id]);
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->generate($verificationUrl));

        $signatureImagePath = null;
        if ($request->boolean('with_signature') && $penyetuju->signature_image_path) {
            $signatureImagePath = storage_path('app/public/' . $penyetuju->signature_image_path);
        }

        $pdfData = [
            'surat' => $surat,
            'qrCode' => $qrCode,
            'signatureImagePath' => $signatureImagePath,
            'settings' => Setting::pluck('value', 'key')->all(),
        ];

        try {
            // 3. Panggil TTE Service untuk menandatangani PDF
            $signedPdfPath = $tteManager->sign($surat, $pdfData);

            // 4. Simpan path PDF final ke database
            $surat->final_pdf_path = $signedPdfPath;
            $surat->save();

            return redirect()->route('surat-keluar.show', $surat)->with('success', 'Surat berhasil disetujui dan PDF telah ditandatangani.');

        } catch (\Exception $e) {
            // Jika TTE gagal, kembalikan status surat ke draft dan tampilkan error
            $surat->status = 'draft';
            $surat->save();

            return redirect()->route('surat-keluar.show', $surat)->with('error', 'Gagal menandatangani surat: ' . $e->getMessage());
        }
    }

    public function destroy(Surat $surat)
    {
        $this->authorize('delete', $surat);

        if ($surat->final_pdf_path) {
            Storage::disk('public')->delete($surat->final_pdf_path);
        }

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

    /**
     * Redirect to the special assignment creation form with pre-filled data.
     *
     * @param Surat $surat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createAssignment(Surat $surat)
    {
        $this->authorize('create', \App\Models\SpecialAssignment::class);

        // Check if the letter is eligible to be converted into an assignment
        if ($surat->status !== 'disetujui' || $surat->suratable_id !== null) {
            return back()->with('error', 'Surat ini tidak dapat dijadikan penugasan khusus atau sudah terhubung dengan entitas lain.');
        }

        return redirect()->route('special-assignments.create')
            ->with('surat_id', $surat->id)
            ->with('prefill_title', $surat->perihal)
            ->with('prefill_description', "Dibuat berdasarkan surat nomor " . $surat->nomor_surat . " perihal " . $surat->perihal . ".");
    }
}
