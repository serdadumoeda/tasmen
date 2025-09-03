<?php

namespace App\Services;

use App\Models\KlasifikasiSurat;
use App\Models\PeminjamanRequest;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SuratPeminjamanGenerator
{
    /**
     * Generate a draft official letter for a given employee loan request.
     *
     * @param PeminjamanRequest $peminjamanRequest The loan request.
     * @return Surat|null The generated letter, or null on failure.
     * @throws \Exception If a suitable template or classification is not found.
     */
    public function generate(PeminjamanRequest $peminjamanRequest): ?Surat
    {
        // 1. Find a suitable template for this type of letter.
        $template = TemplateSurat::where('judul', 'LIKE', '%Peminjaman Pegawai%')
            ->orWhere('judul', 'LIKE', '%Nota Dinas Permohonan%')
            ->first();

        if (!$template) {
            Log::error('Template Surat Peminjaman Pegawai tidak ditemukan.');
            throw new \Exception('Template Surat Peminjaman Pegawai tidak ditemukan.');
        }

        // 2. Find a suitable classification (e.g., Kepegawaian).
        $klasifikasi = KlasifikasiSurat::where('kode', 'LIKE', 'KP%') // KP for Kepegawaian
            ->orWhere('nama', 'LIKE', '%Kepegawaian%')
            ->first();

        if (!$klasifikasi) {
            Log::error('Klasifikasi surat untuk Kepegawaian (KP) tidak ditemukan.');
            throw new \Exception('Klasifikasi surat untuk Kepegawaian (KP) tidak ditemukan.');
        }

        // 3. Create the Surat record as a draft.
        $surat = new Surat([
            'perihal' => 'Permohonan Peminjaman Pegawai a.n. ' . $peminjamanRequest->requestedUser->name,
            'tanggal_surat' => now(),
            'jenis' => 'KELUAR',
            'status' => 'draft', // Created as a draft, to be finalized upon approval.
            'pembuat_id' => $peminjamanRequest->requester_id,
            'penyetuju_id' => $peminjamanRequest->approver_id, // The approver is pre-determined
            'konten' => $this->replacePlaceholders($template->konten, $peminjamanRequest),
            'klasifikasi_id' => $klasifikasi->id,
        ]);

        // 4. Associate with the PeminjamanRequest and save.
        $surat->suratable()->associate($peminjamanRequest);
        $surat->save();

        Log::info("Generated draft Surat Peminjaman #{$surat->id} for PeminjamanRequest #{$peminjamanRequest->id}");

        return $surat;
    }

    /**
     * Replace placeholders in the letter template with actual data.
     */
    private function replacePlaceholders(string $content, PeminjamanRequest $peminjamanRequest): string
    {
        $replacements = [
            '{{nama_pemohon}}' => $peminjamanRequest->requester->name,
            '{{jabatan_pemohon}}' => $peminjamanRequest->requester->jabatan->name ?? 'N/A',
            '{{unit_pemohon}}' => $peminjamanRequest->requester->unit->name ?? 'N/A',
            '{{nama_pegawai_dipinjam}}' => $peminjamanRequest->requestedUser->name,
            '{{jabatan_pegawai_dipinjam}}' => $peminjamanRequest->requestedUser->jabatan->name ?? 'N/A',
            '{{unit_pegawai_dipinjam}}' => $peminjamanRequest->requestedUser->unit->name ?? 'N/A',
            '{{nama_proyek}}' => $peminjamanRequest->project->name,
            '{{tanggal_surat}}' => now()->isoFormat('D MMMM Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
