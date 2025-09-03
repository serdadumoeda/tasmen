<?php

namespace App\Services;

use App\Models\KlasifikasiSurat;
use App\Models\LeaveRequest;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SuratCutiGenerator
{
    protected NomorSuratService $nomorSuratService;

    public function __construct(NomorSuratService $nomorSuratService)
    {
        $this->nomorSuratService = $nomorSuratService;
    }

    /**
     * Generate an official SK Cuti for a given leave request.
     *
     * @param LeaveRequest $leaveRequest The approved leave request.
     * @param User $approver The user who gave the final approval.
     * @return Surat|null The generated letter, or null on failure.
     * @throws \Exception If a suitable template or classification is not found.
     */
    public function generate(LeaveRequest $leaveRequest, User $approver): ?Surat
    {
        // 1. Find a suitable template for SK Cuti.
        $template = TemplateSurat::where('judul', 'LIKE', '%SK Cuti%')
            ->orWhere('judul', 'LIKE', '%Surat Cuti%')
            ->first();

        if (!$template) {
            Log::error('Template SK Cuti tidak ditemukan.');
            throw new \Exception('Template SK Cuti tidak ditemukan.');
        }

        // 2. Find a suitable classification (e.g., Kepegawaian).
        $klasifikasi = KlasifikasiSurat::where('kode', 'LIKE', 'KP%') // KP for Kepegawaian
            ->orWhere('nama', 'LIKE', '%Kepegawaian%')
            ->first();

        if (!$klasifikasi) {
            Log::error('Klasifikasi surat untuk Kepegawaian (KP) tidak ditemukan.');
            throw new \Exception('Klasifikasi surat untuk Kepegawaian (KP) tidak ditemukan.');
        }

        // 3. Generate the letter number.
        // The letter is generated on behalf of the final approver.
        $nomorSurat = $this->nomorSuratService->generate($klasifikasi, $approver);

        // 4. Create the Surat record.
        $surat = new Surat([
            'nomor_surat' => $nomorSurat,
            'perihal' => 'Surat Keputusan Izin Cuti - ' . $leaveRequest->user->name,
            'tanggal_surat' => now(),
            'jenis' => 'KELUAR',
            'status' => 'TERVERIFIKASI', // Automatically verified as it's system-generated
            'pembuat_id' => $approver->id,
            'penyetuju_id' => $approver->id,
            'konten' => $this->replacePlaceholders($template->konten, $leaveRequest, $approver),
            'klasifikasi_id' => $klasifikasi->id,
        ]);

        // 5. Associate with the LeaveRequest and save.
        $surat->suratable()->associate($leaveRequest);
        $surat->save();

        Log::info("Generated SK Cuti #{$nomorSurat} for Leave Request #{$leaveRequest->id}");

        return $surat;
    }

    /**
     * Replace placeholders in the letter template with actual data.
     *
     * @param string $content The template content.
     * @param LeaveRequest $leaveRequest The leave request data.
     * @param User $approver The approver data.
     * @return string The content with placeholders replaced.
     */
    private function replacePlaceholders(string $content, LeaveRequest $leaveRequest, User $approver): string
    {
        $replacements = [
            '{{nama_pegawai}}' => $leaveRequest->user->name,
            '{{jabatan_pegawai}}' => $leaveRequest->user->jabatan->nama ?? 'N/A',
            '{{unit_kerja_pegawai}}' => $leaveRequest->user->unit->nama ?? 'N/A',
            '{{jenis_cuti}}' => $leaveRequest->leaveType->name,
            '{{tanggal_mulai_cuti}}' => $leaveRequest->start_date->isoFormat('D MMMM Y'),
            '{{tanggal_selesai_cuti}}' => $leaveRequest->end_date->isoFormat('D MMMM Y'),
            '{{durasi_cuti}}' => $leaveRequest->duration_days . ' hari',
            '{{alasan_cuti}}' => $leaveRequest->reason,
            '{{nama_pejabat_penyetuju}}' => $approver->name,
            '{{jabatan_pejabat_penyetuju}}' => $approver->jabatan->nama ?? 'N/A',
            '{{tanggal_penetapan}}' => now()->isoFormat('D MMMM Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
