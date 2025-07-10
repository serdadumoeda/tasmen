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
        // Ambil ID pengguna yang sedang login
        $manager = Auth::user();

        // --- PERBAIKAN DI SINI ---
        // Ganti 'atasan_id' menjadi 'parent_id' sesuai dengan skema database Anda.
        $teamMembers = $manager->getAllSubordinates();

        return view('resource_pool.index', compact('teamMembers'));
    }

    /**
     * Memperbarui status resource pool seorang pengguna.
     */
    public function update(Request $request, User $user)
    {
        // --- PERBAIKAN DI SINI ---
        // Validasi keamanan: Pastikan yang mengubah adalah atasan langsung
        // Ganti 'atasan_id' menjadi 'parent_id'.
        if ($user->parent_id != Auth::id()) {
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
                        ->with('parent') // Muat relasi atasan (jika diperlukan)
                        ->get(['id', 'name', 'pool_availability_notes', 'role', 'parent_id']); // Sertakan 'role'

        return response()->json($members);
    }
}