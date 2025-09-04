<?php

namespace App\Listeners;

use App\Events\SuratPeminjamanDisetujui;
use App\Models\PeminjamanRequest;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreatePeminjamanRequest implements ShouldQueue
{
    public function handle(SuratPeminjamanDisetujui $event): void
    {
        $surat = $event->surat;
        // Asumsi data user yang dipinjam dan tanggal disimpan di konten surat
        // Anda perlu mengekstrak informasi ini.
        // Untuk contoh ini, kita anggap ada di properti meta.
        $meta = $surat->meta_data; // Asumsi Anda menyimpan data ini sebagai JSON

        PeminjamanRequest::create([
            'requester_id' => $surat->user_id,
            'borrowed_user_id' => $meta['borrowed_user_id'],
            'project_id' => $meta['project_id'] ?? null,
            'start_date' => $meta['start_date'],
            'end_date' => $meta['end_date'],
            'reason' => $surat->perihal,
            'status' => 'approved', // Langsung approved karena suratnya sudah disetujui
            'surat_id' => $surat->id, // Tautkan ke surat resminya
        ]);
    }
}
