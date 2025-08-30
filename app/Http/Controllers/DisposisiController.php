<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisposisiController extends Controller
{
    public function store(Request $request, Surat $surat)
    {
        $validated = $request->validate([
            'penerima_id' => 'required|array|min:1',
            'penerima_id.*' => 'exists:users,id',
            'instruksi' => 'nullable|string',
        ]);

        foreach ($validated['penerima_id'] as $penerimaId) {
            Disposisi::create([
                'surat_id' => $surat->id,
                'pengirim_id' => Auth::id(),
                'penerima_id' => $penerimaId,
                'instruksi' => $validated['instruksi'],
                'tanggal_disposisi' => now(),
            ]);

            // TODO: Send notification to user with ID $penerimaId
        }

        return redirect()->route('surat-masuk.show', $surat)->with('success', 'Surat berhasil didisposisikan.');
    }
}
