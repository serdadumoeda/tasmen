<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkloadAnalysisController extends Controller
{
    use AuthorizesRequests;

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
    public function updateBehavior(Request $request, User $user, \App\Services\PerformanceCalculatorService $calculator)
    {
        // Otorisasi dipindahkan ke UserPolicy untuk konsistensi dan perbaikan bug.
        $this->authorize('rateBehavior', $user);

        $validated = $request->validate([
            'work_behavior_rating' => 'required|string|in:Diatas Ekspektasi,Sesuai Ekspektasi,Dibawah Ekspektasi',
        ]);

        $user->update($validated);

        // PERBAIKAN: Panggil service untuk menghitung ulang skor kinerja user ini dan atasannya.
        // Ini memastikan perubahan pada bawahan langsung terefleksi pada skor manajerial atasan.
        $calculator->calculateForSingleUserAndParents($user);

        return back()->with('success', "Penilaian perilaku kerja untuk {$user->name} berhasil diperbarui.");
    }
}