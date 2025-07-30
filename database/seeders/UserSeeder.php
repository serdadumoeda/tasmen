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
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        $eselon1 = Unit::where('name', 'Kementerian Digital')->first();
        $eselon2_keuangan = Unit::where('name', 'Divisi Keuangan')->first();
        $koordinator_anggaran = Unit::where('name', 'Koordinator Anggaran')->first();
        $sub_koordinator_belanja = Unit::where('name', 'Sub Koordinator Belanja')->first();

        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'unit_id' => null,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Kepala Kementerian',
            'email' => 'ka.kementerian@example.com',
            'password' => Hash::make('password'),
            'role' => 'eselon_1',
            'unit_id' => $eselon1->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Kepala Divisi Keuangan',
            'email' => 'ka.keuangan@example.com',
            'password' => Hash::make('password'),
            'role' => 'eselon_2',
            'unit_id' => $eselon2_keuangan->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Koordinator Anggaran',
            'email' => 'koor.anggaran@example.com',
            'password' => Hash::make('password'),
            'role' => 'koordinator',
            'unit_id' => $koordinator_anggaran->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Sub Koordinator Belanja',
            'email' => 'subkoor.belanja@example.com',
            'password' => Hash::make('password'),
            'role' => 'sub_koordinator',
            'unit_id' => $sub_koordinator_belanja->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Staf Belanja 1',
            'email' => 'staf.belanja1@example.com',
            'password' => Hash::make('password'),
            'role' => 'staf',
            'unit_id' => $sub_koordinator_belanja->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Staf Belanja 2',
            'email' => 'staf.belanja2@example.com',
            'password' => Hash::make('password'),
            'role' => 'staf',
            'unit_id' => $sub_koordinator_belanja->id,
            'status' => 'active',
        ]);
    }
}
