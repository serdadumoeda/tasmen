<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WeeklyWorkloadController;

class ResourcePoolController extends Controller
{
    /**
     * Menampilkan halaman manajemen resource pool.
     */
    public function index()
    {
        $manager = Auth::user();
        $teamMembers = $manager->getAllSubordinates();

        $workloadData = $teamMembers->map(function ($member) {
            // Hitung total jam dari tugas yang belum selesai
            $totalAssignedHours = $member->tasks()
                ->where('status', '!=', 'completed')
                ->sum('estimated_hours');

            // Hitung persentase beban kerja
            $workloadPercentage = (WeeklyWorkloadController::STANDARD_WEEKLY_HOURS > 0)
                ? ($totalAssignedHours / WeeklyWorkloadController::STANDARD_WEEKLY_HOURS) * 100
                : 0;

            return [
                'user' => $member,
                'workload_percentage' => round($workloadPercentage)
            ];
        });

        return view('resource_pool.index', [
            'workloadData' => $workloadData,
        ]);
    }

    /**
     * Memperbarui status resource pool seorang pengguna.
     */
    public function update(Request $request, User $user)
    {
        // Prevent users from updating their own status, unless they are a Superadmin
        if (Auth::id() === $user->id && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat mengubah status resource pool diri sendiri.'], 403);
        }

        if (!Auth::user()->isSuperAdmin() && !Auth::user()->is($user->atasan) && !$user->isSubordinateOf(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berwenang mengubah status pengguna ini.'], 403);
        }
        

        $request->validate([
            'is_in_resource_pool' => 'required|boolean',
            'pool_availability_notes' => 'nullable|string|max:500',
        ]);

        $user->update([
            'is_in_resource_pool' => $request->is_in_resource_pool,
            'pool_availability_notes' => $request->pool_availability_notes,
        ]);

        return response()->json(['success' => true, 'message' => 'Status anggota berhasil diperbarui.']);
    }
    /**
     * API untuk mengambil daftar anggota yang tersedia di pool
     * untuk digunakan di halaman pembuatan proyek.
     */
    public function getAvailableMembers()
    {
        $members = User::where('is_in_resource_pool', true)
                        ->where('id', '!=', Auth::id())
                        ->with('atasan:id,name') // Still only load id and name to be safe
                        ->get(['id','name','role','pool_availability_notes','atasan_id']);

        // Transform to an array, but maintain the nested structure the frontend expects
        $formatted = $members->map(function ($user) {
            return [
                'id'   => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'pool_availability_notes' => $user->pool_availability_notes,
                'atasan_id'   => $user->atasan_id,
                'atasan' => $user->atasan ? [         // Recreate the nested atasan object
                    'id' => $user->atasan->id,
                    'name' => $user->atasan->name,
                ] : null,
            ];
        });

        return response()->json($formatted);
    }
}