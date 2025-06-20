<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // KOSONGKAN TABEL USER DULU
        // User::query()->delete(); // Hapus baris ini jika Anda tidak ingin menghapus user lama

        // --- ROLE NON-HIERARKI ---
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'parent_id' => null,
        ]);

        // --- ESELON I ---
        $eselon1 = User::create([
            'name' => 'Kepala Badan', // 
            'email' => 'kepala.badan@example.com',
            'password' => Hash::make('password'),
            'role' => 'Eselon I',
            'parent_id' => null,
        ]);

        // --- ESELON II ---
        $sekban = User::create([
            'name' => 'Sekretaris Badan', // 
            'email' => 'sekban@example.com',
            'password' => Hash::make('password'),
            'role' => 'Eselon II',
            'parent_id' => $eselon1->id,
        ]);

        $kapusren = User::create([
            'name' => 'Kepala Pusat Perencanaan Ketenagakerjaan', // 
            'email' => 'kapusren@example.com',
            'password' => Hash::make('password'),
            'role' => 'Eselon II',
            'parent_id' => $eselon1->id,
        ]);

        $kapusdatin = User::create([
            'name' => 'Kepala Pusat Data dan TI Ketenagakerjaan', // 
            'email' => 'kapusdatin@example.com',
            'password' => Hash::make('password'),
            'role' => 'Eselon II',
            'parent_id' => $eselon1->id,
        ]);

        // --- KOORDINATOR (di bawah Kapusdatin sebagai contoh) ---
        $koorPengembanganSI = User::create([
            'name' => 'Koordinator Bid. Pengembangan Sistem Informasi Ketenagakerjaan', // 
            'email' => 'koor.si@example.com',
            'password' => Hash::make('password'),
            'role' => 'Koordinator',
            'parent_id' => $kapusdatin->id,
        ]);
        
        // --- KETUA TIM (di bawah Koordinator, role baru sesuai permintaan) ---
        $ketuaTimA = User::create([
            'name' => 'Ketua Tim Proyek A',
            'email' => 'ketuatim.a@example.com',
            'password' => Hash::make('password'),
            'role' => 'Ketua Tim',
            'parent_id' => $koorPengembanganSI->id,
        ]);

        // --- SUB KOORDINATOR (di bawah Ketua Tim) ---
        $subkoorPerancangan = User::create([
            'name' => 'Sub Koord. Bid. Perancangan Sistem Informasi Ketenagakerjaan', // 
            'email' => 'subkoor.perancangan@example.com',
            'password' => Hash::make('password'),
            'role' => 'Sub Koordinator',
            'parent_id' => $ketuaTimA->id,
        ]);
        
        $subkoorPengembangan = User::create([
            'name' => 'Sub Koord. Bid. Pengembangan Sistem Informasi Ketenagakerjaan', // 
            'email' => 'subkoor.pengembangan@example.com',
            'password' => Hash::make('password'),
            'role' => 'Sub Koordinator',
            'parent_id' => $ketuaTimA->id,
        ]);

        // --- STAFF (di bawah Sub Koordinator) ---
        User::create([
            'name' => 'Staff Perancangan 1',
            'email' => 'staff.perancangan1@example.com',
            'password' => Hash::make('password'),
            'role' => 'Staff',
            'parent_id' => $subkoorPerancangan->id,
        ]);

        User::create([
            'name' => 'Staff Pengembangan 1',
            'email' => 'staff.pengembangan1@example.com',
            'password' => Hash::make('password'),
            'role' => 'Staff',
            'parent_id' => $subkoorPengembangan->id,
        ]);
    }
}