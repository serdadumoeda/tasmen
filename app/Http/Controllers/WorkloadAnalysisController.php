<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkloadAnalysisController extends Controller
{
    public function index()
    {
        $manager = Auth::user();

        if (!$manager->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        if ($manager->role === 'Superadmin') {
            $subordinates = User::where('id', '!=', $manager->id)->get();
        } else {
            $subordinates = $manager->getAllSubordinates();
        }
        
        return view('workload-analysis.index', compact('manager', 'subordinates'));
    }

    /**
     * Update penilaian perilaku kerja oleh atasan.
     * Logika otorisasi diubah sesuai aturan baru.
     */
    public function updateBehavior(Request $request, User $user)
    {
        $manager = Auth::user();
        $canRate = false;

        // Aturan 1: Eselon I bisa menilai Eselon II
        if ($manager->role === 'Eselon I' && $user->role === 'Eselon II' && $user->parent_id === $manager->id) {
            $canRate = true;
        }

        // Aturan 2: Eselon II bisa menilai SEMUA di bawah hierarkinya
        if ($manager->role === 'Eselon II') {
            // Cek apakah user yang dinilai ada dalam daftar bawahan si manajer
            if ($manager->getAllSubordinateIds()->contains($user->id)) {
                $canRate = true;
            }
        }
        
        // Jika tidak memenuhi syarat, tolak akses
        if (!$canRate) {
            abort(403, 'Anda tidak memiliki hak untuk menilai pegawai ini.');
        }

        $validated = $request->validate([
            'work_behavior_rating' => 'required|string|in:Diatas Ekspektasi,Sesuai Ekspektasi,Dibawah Ekspektasi',
        ]);

        $user->update($validated);

        return back()->with('success', "Penilaian perilaku kerja untuk {$user->name} berhasil diperbarui.");
    }
}