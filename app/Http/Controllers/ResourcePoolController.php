<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourcePoolController extends Controller
{
    /**
     * Menampilkan halaman manajemen resource pool.
     */
    public function index()
    {
        $manager = Auth::user();
        $teamMembers = $manager->getAllSubordinates();
        $standardHours = config('tasmen.workload.standard_hours', 37.5);

        $workloadData = $teamMembers->map(function ($member) use ($standardHours) {
            // Hitung total jam dari tugas yang belum selesai
            $totalAssignedHours = $member->tasks()
                ->whereHas('status', function ($q) {
                    $q->where('key', '!=', 'completed');
                })
                ->sum('estimated_hours');

            // Hitung persentase beban kerja
            $workloadPercentage = ($standardHours > 0)
                ? ($totalAssignedHours / $standardHours) * 100
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
                        ->where('id', '!=', Auth::id()) // Jangan tampilkan diri sendiri
                        ->with('atasan', 'roles') // Muat relasi atasan dan peran
                        ->get(['id', 'name', 'pool_availability_notes', 'atasan_id']);

        // Tambahkan nama peran secara manual ke dalam response
        $members->each(function ($member) {
            $member->role_name = $member->roles->first()->name ?? 'N/A';
        });

        return response()->json($members);
    }
}