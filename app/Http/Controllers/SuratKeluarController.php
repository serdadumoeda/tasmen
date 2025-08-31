<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\PeminjamanRequest;
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
        $suratable_id = $request->query('suratable_id');
        $suratable_type = $request->query('suratable_type');

        return view('suratkeluar.pilih-metode', compact('suratable_id', 'suratable_type'));
    }

    public function createFromTemplate(Request $request)
    {
        $suratable_id = $request->query('suratable_id');
        $suratable_type = $request->query('suratable_type');

        if ($request->has('template_id')) {
            $template = TemplateSurat::findOrFail($request->template_id);
            $settings = Setting::pluck('value', 'key')->all();
            return view('suratkeluar.create-step2', compact('template', 'settings', 'suratable_id', 'suratable_type'));
        }

        $templates = TemplateSurat::all();
        return view('suratkeluar.create-step1', compact('templates', 'suratable_id', 'suratable_type'));
    }

    public function createUpload(Request $request)
    {
        $suratable_id = $request->query('suratable_id');
        $suratable_type = $request->query('suratable_type');

        return view('suratkeluar.create-upload', compact('suratable_id', 'suratable_type'));
    }

    public function store(Request $request)
    {
        $suratableRules = [
            'suratable_id' => 'nullable|integer|required_with:suratable_type',
            'suratable_type' => [
                'nullable',
                'string',
                'required_with:suratable_id',
                \Illuminate\Validation\Rule::in(['App\Models\Project', 'App\Models\SpecialAssignment', 'App\Models\LeaveRequest', 'App\Models\PeminjamanRequest'])
            ],
        ];

        if ($request->input('submission_type') === 'upload') {
            $validated = $request->validate(array_merge($suratableRules, [
                'perihal' => 'required|string|max:255',
                'nomor_surat' => 'nullable|string|max:255|unique:surat,nomor_surat',
                'tanggal_surat' => 'required|date',
                'lampiran' => 'required|file|mimes:pdf|max:5120',
            ]));

            $suratData = array_merge($validated, [
                'jenis' => 'keluar', 'status' => 'draft', 'pembuat_id' => Auth::id(),
            ]);
            $surat = Surat::create($suratData);

            if ($request->hasFile('lampiran')) {
                $file = $request->file('lampiran');
                $path = $file->store('lampiran-surat', 'public');
                LampiranSurat::create(['surat_id' => $surat->id, 'nama_file' => $file->getClientOriginalName(), 'path_file' => $path, 'tipe_file' => $file->getClientMimeType(), 'ukuran_file' => $file->getSize()]);
            }
            return redirect()->route('surat-keluar.show', $surat)->with('success', 'Surat keluar berhasil diunggah dan diarsipkan.');
        } else {
            $validated = $request->validate(array_merge($suratableRules, [
                'perihal' => 'required|string|max:255',
                'tanggal_surat' => 'required|date',
                'template_id' => 'required|exists:template_surat,id',
                'placeholders' => 'nullable|array',
                'konten_final' => 'required|string',
            ]));

            $suratData = array_merge($validated, [
                'jenis' => 'keluar',
                'status' => 'draft',
                'pembuat_id' => Auth::id(),
                'konten' => $validated['konten_final'],
            ]);

            unset($suratData['placeholders'], $suratData['konten_final']);

            $surat = Surat::create($suratData);

            return redirect()->route('surat-keluar.show', $surat)->with('success', 'Draf surat berhasil disimpan.');
        }
    }

    public function show(Surat $surat)
    {
        if ($surat->jenis !== 'keluar') { abort(404); }
        $this->authorize('view', $surat);
        $surat->load('pembuat', 'penyetuju', 'lampiran', 'suratable');
        return view('suratkeluar.show', compact('surat'));
    }

    public function approve(Request $request, Surat $surat)
    {
        $this->authorize('approve', $surat);

        $possibleApprovers = User::whereIn('role', [User::ROLE_MENTERI, User::ROLE_SUPERADMIN, User::ROLE_ESELON_I, User::ROLE_ESELON_II, User::ROLE_KOORDINATOR])->pluck('id');

        $validated = $request->validate([
            'penyetuju_id' => ['required', \Illuminate\Validation\Rule::in($possibleApprovers)],
            'with_signature' => 'nullable|boolean',
        ]);

        $penyetuju = User::find($validated['penyetuju_id']);
        $surat->status = 'disetujui';
        $surat->penyetuju_id = $penyetuju->id;
        $surat->save();

        $this->generateFinalPdf($surat, $penyetuju, $request->boolean('with_signature'));

        return redirect()->route('surat-keluar.show', $surat)->with('success', 'Surat berhasil disetujui dan PDF telah digenerate.');
    }

    public function createFromLeave(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('create', Surat::class); // General authorization to create letters

        if ($leaveRequest->status !== 'approved' || $leaveRequest->surat) {
            return back()->with('error', 'Surat tidak dapat dibuat untuk permintaan cuti ini.');
        }

        try {
            $cutiTemplate = TemplateSurat::where('judul', 'Surat Izin Cuti')->firstOrFail();
            $penyetuju = Auth::user();

            $content = str_replace(
                ['{{nama_pegawai}}', '{{jabatan}}', '{{unit_kerja}}', '{{jenis_cuti}}', '{{tanggal_mulai}}', '{{tanggal_selesai}}', '{{durasi}}'],
                [$leaveRequest->user->name, $leaveRequest->user->jabatan->name ?? 'N/A', $leaveRequest->user->unit->name ?? 'N/A', $leaveRequest->leaveType->name, $leaveRequest->start_date->format('d M Y'), $leaveRequest->end_date->format('d M Y'), $leaveRequest->duration_days],
                $cutiTemplate->konten
            );

            $surat = $leaveRequest->surat()->create([
                'perihal' => 'Surat Izin Cuti a.n. ' . $leaveRequest->user->name,
                'tanggal_surat' => now(),
                'jenis' => 'keluar',
                'status' => 'disetujui',
                'pembuat_id' => $penyetuju->id,
                'penyetuju_id' => $penyetuju->id,
                'konten' => $content,
            ]);

            $this->generateFinalPdf($surat, $penyetuju, true);

            return redirect()->route('leaves.show', $leaveRequest)->with('success', 'Surat Izin Cuti berhasil digenerate.');
        } catch (\Exception $e) {
            \Log::error('Gagal membuat Surat Izin Cuti: ' . $e->getMessage());
            return back()->with('warning', 'Gagal membuat surat izin cuti. Pastikan template "Surat Izin Cuti" sudah ada.');
        }
    }

    public function createFromPeminjaman(Request $request, PeminjamanRequest $peminjamanRequest)
    {
        $this->authorize('create', Surat::class); // General authorization to create letters

        if ($peminjamanRequest->status !== 'approved' || $peminjamanRequest->surat) {
            return back()->with('error', 'Surat tidak dapat dibuat untuk permintaan ini.');
        }

        try {
            $peminjamanTemplate = TemplateSurat::where('judul', 'Surat Perintah Penugasan')->firstOrFail();
            $penyetuju = Auth::user();

            $content = str_replace(
                ['{{nama_pegawai}}', '{{jabatan}}', '{{unit_kerja}}', '{{nama_kegiatan}}', '{{tanggal_mulai}}', '{{tanggal_selesai}}'],
                [
                    $peminjamanRequest->requestedUser->name,
                    $peminjamanRequest->requestedUser->jabatan->name ?? 'N/A',
                    $peminjamanRequest->requestedUser->unit->name ?? 'N/A',
                    $peminjamanRequest->project->name,
                    $peminjamanRequest->project->start_date->format('d M Y'),
                    $peminjamanRequest->project->end_date->format('d M Y')
                ],
                $peminjamanTemplate->konten
            );

            $surat = $peminjamanRequest->surat()->create([
                'perihal' => 'Surat Penugasan untuk Kegiatan ' . $peminjamanRequest->project->name,
                'tanggal_surat' => now(),
                'jenis' => 'keluar',
                'status' => 'disetujui',
                'pembuat_id' => $penyetuju->id,
                'penyetuju_id' => $penyetuju->id,
                'konten' => $content,
            ]);

            $this->generateFinalPdf($surat, $penyetuju, true);

            return redirect()->route('peminjaman-requests.my-requests')->with('success', 'Surat Penugasan berhasil digenerate.');
        } catch (\Exception $e) {
            \Log::error('Gagal membuat Surat Penugasan: ' . $e->getMessage());
            return back()->with('warning', 'Gagal membuat surat penugasan. Pastikan template "Surat Perintah Penugasan" sudah ada.');
        }
    }

    private function generateFinalPdf(Surat $surat, User $penyetuju, bool $withSignature)
    {
        $verificationUrl = route('surat.verify', ['id' => $surat->id]);
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->generate($verificationUrl));

        $signatureImagePath = null;
        if ($withSignature && $penyetuju->signature_image_path) {
            $signatureImagePath = storage_path('app/public/' . $penyetuju->signature_image_path);
        }

        $settings = Setting::pluck('value', 'key')->all();

        $pdf = Pdf::loadView('pdf.surat', compact('surat', 'qrCode', 'signatureImagePath', 'settings'));

        $filename = 'surat-keluar-' . $surat->id . '-' . time() . '.pdf';
        Storage::disk('public')->put('surat-final/' . $filename, $pdf->output());

        $surat->final_pdf_path = 'surat-final/' . $filename;
        $surat->save();
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
        if ($surat->jenis !== 'keluar') { abort(404); }
        $this->authorize('download', $surat);

        if (!$surat->final_pdf_path || !Storage::disk('public')->exists($surat->final_pdf_path)) {
            return back()->with('error', 'File PDF final tidak ditemukan.');
        }
        return Storage::disk('public')->download($surat->final_pdf_path);
    }
}
