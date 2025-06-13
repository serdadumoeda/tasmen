<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Disarankan untuk menjalankan seeder dengan migrate:fresh
        
        // Membuat user Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin', // Role tertinggi
        ]);

        // Membuat user Manager (sebelumnya Kepala Pusdatik)
        User::create([
            'name' => 'Project Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager', // Role pengawas proyek
        ]);

        // Membuat user Ketua Tim
        User::create([
            'name' => 'Ketua Tim Alpha',
            'email' => 'ketua.alpha@example.com',
            'password' => Hash::make('password'),
            'role' => 'leader',
        ]);
        
        // Membuat user anggota biasa
        User::factory(3)->create([
            'role' => 'user' // Role default
        ]);
    }
}