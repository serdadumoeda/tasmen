<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Surat;
use App\Models\User;
use App\Notifications\SuratDisposisiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DisposisiController extends Controller
{
    public function store(Request $request, Surat $surat)
    {
        $validated = $request->validate([
            'penerima_id' => 'required|array|min:1',
            'penerima_id.*' => 'exists:users,id',
            'tembusan_id' => 'nullable|array',
            'tembusan_id.*' => 'exists:users,id',
            'instruksi' => 'nullable|string',
            'parent_disposisi_id' => 'nullable|exists:disposisi,id',
        ]);

        $pengirim = Auth::user();
        $newDisposisiIds = [];

        foreach ($validated['penerima_id'] as $penerimaId) {
            $disposisi = Disposisi::create([
                'surat_id' => $surat->id,
                'pengirim_id' => $pengirim->id,
                'penerima_id' => $penerimaId,
                'instruksi' => $validated['instruksi'],
                'tanggal_disposisi' => now(),
                'parent_id' => $validated['parent_disposisi_id'] ?? null,
            ]);
            $newDisposisiIds[] = $disposisi->id;
        }

        // Handle Tembusan (CC)
        if (!empty($validated['tembusan_id']) && !empty($newDisposisiIds)) {
            // Attach the CC'd users to all dispositions created in this action
            $disposisiUtama = Disposisi::find($newDisposisiIds[0]);
            $disposisiUtama->tembusanUsers()->sync($validated['tembusan_id']);
        }

        // Send Notifications
        $penerimaUsers = User::find($validated['penerima_id']);
        Notification::send($penerimaUsers, new SuratDisposisiNotification($surat, $pengirim, false));

        if (!empty($validated['tembusan_id'])) {
            $tembusanUsers = User::find($validated['tembusan_id']);
            Notification::send($tembusanUsers, new SuratDisposisiNotification($surat, $pengirim, true));
        }

        return redirect()->route('surat-masuk.show', $surat)->with('success', 'Surat berhasil didisposisikan.');
    }

    public function lacak(Surat $surat)
    {
        $this->authorize('view', $surat);

        // Fetch top-level dispositions for the given letter
        // and recursively load all children and their relationships.
        $disposisiTree = Disposisi::where('surat_id', $surat->id)
            ->whereNull('parent_id') // Get only root dispositions
            ->with('childrenRecursive', 'pengirim', 'penerima', 'tembusanUsers')
            ->get();

        return view('suratmasuk.lacak_disposisi', compact('surat', 'disposisiTree'));
    }
}
