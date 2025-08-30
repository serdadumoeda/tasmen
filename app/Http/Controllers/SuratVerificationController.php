<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;

class SuratVerificationController extends Controller
{
    public function verify($id)
    {
        $surat = Surat::findOrFail($id);

        // Pastikan hanya surat yang sudah disetujui yang bisa diverifikasi
        if ($surat->status !== 'disetujui') {
            abort(404, 'Surat tidak ditemukan atau belum disetujui.');
        }

        return view('surat.verify', compact('surat'));
    }
}
