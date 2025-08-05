<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkloadAnalysisController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $manager = Auth::user();
        $search = $request->input('search');

        if (!$manager->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // Dapatkan query dasar untuk bawahan
        if ($manager->role === 'Superadmin') {
            $subordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            // Replikasi logika dari getAllSubordinates untuk mendapatkan query builder
            $subordinateUnitIds = $manager->unit ? $manager->unit->getAllSubordinateUnitIds() : [];
            $subordinatesQuery = User::whereIn('unit_id', $subordinateUnitIds)->where('id', '!=', $manager->id);
        }

        // Terapkan filter pencarian jika ada
        if ($search) {
            $subordinatesQuery->where('name', 'like', '%' . $search . '%');
        }

        // Ambil hasil dengan paginasi dan pertahankan query string
        $subordinates = $subordinatesQuery->paginate(20)->withQueryString();
        
        return view('workload-analysis.index', compact('manager', 'subordinates', 'search'));
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

        // Panggil service untuk menghitung ulang skor kinerja user ini dan atasannya.
        $calculator->calculateForSingleUserAndParents($user);

        // PERBAIKAN: Kembalikan respons JSON untuk permintaan AJAX.
        if ($request->ajax() || $request->wantsJson()) {
            // Muat ulang data user untuk mendapatkan nilai-nilai yang sudah dihitung ulang.
            $user->refresh();
            return response()->json(['success' => true, 'user' => $user]);
        }

        return back()->with('success', "Penilaian perilaku kerja untuk {$user->name} berhasil diperbarui.");
    }
}