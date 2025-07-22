<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Mengosongkan tabel user dengan aman
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // Ambil unit yang sudah ada dari UnitSeeder
        $eselon1 = Unit::where('name', 'Kementerian Digital')->first();
        $eselon2_keuangan = Unit::where('name', 'Divisi Keuangan')->first();
        $koordinator_anggaran = Unit::where('name', 'Koordinator Anggaran')->first();
        $sub_koordinator_belanja = Unit::where('name', 'Sub Koordinator Belanja')->first();

        // 1. SUPER ADMIN (Non-hierarki)
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERADMIN,
            'unit_id' => null, // Superadmin tidak terikat unit spesifik
            'status' => User::STATUS_ACTIVE,
        ]);

        // 2. PENGGUNA DENGAN PERAN DAN UNIT
        User::create([
            'name' => 'Kepala Kementerian',
            'email' => 'ka.kementerian@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_I,
            'unit_id' => $eselon1->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        User::create([
            'name' => 'Kepala Divisi Keuangan',
            'email' => 'ka.keuangan@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_II,
            'unit_id' => $eselon2_keuangan->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        User::create([
            'name' => 'Koordinator Anggaran',
            'email' => 'koor.anggaran@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KOORDINATOR,
            'unit_id' => $koordinator_anggaran->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        User::create([
            'name' => 'Sub Koordinator Belanja',
            'email' => 'subkoor.belanja@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUB_KOORDINATOR,
            'unit_id' => $sub_koordinator_belanja->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        User::create([
            'name' => 'Staf Belanja 1',
            'email' => 'staf.belanja1@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STAF,
            'unit_id' => $sub_koordinator_belanja->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        User::create([
            'name' => 'Staf Belanja 2',
            'email' => 'staf.belanja2@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STAF,
            'unit_id' => $sub_koordinator_belanja->id,
            'status' => User::STATUS_ACTIVE,
        ]);
    }
}
