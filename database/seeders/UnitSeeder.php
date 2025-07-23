<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Buat Pengguna Superadmin (tidak memiliki unit)
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERADMIN,
            'status' => User::STATUS_ACTIVE,
            'unit_id' => null,
        ]);

        // 2. Buat Unit dan Pengguna Eselon I
        $unitEselonI = Unit::create([
            'name' => 'Kementerian Ketenagakerjaan',
            'level' => User::ROLE_ESELON_I,
            'parent_unit_id' => null,
        ]);
        $userEselonI = User::create([
            'name' => 'Kepala Kementerian',
            'email' => 'ka.kementerian@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_I,
            'status' => User::STATUS_ACTIVE,
            'unit_id' => $unitEselonI->id,
        ]);

        // 3. Buat Unit dan Pengguna Eselon II di bawah Eselon I
        $unitEselonII = Unit::create([
            'name' => 'Direktorat Jenderal Pembinaan Pelatihan Vokasi dan Produktivitas',
            'level' => User::ROLE_ESELON_II,
            'parent_unit_id' => $unitEselonI->id,
        ]);
        $userEselonII = User::create([
            'name' => 'Dirjen Binalavotas',
            'email' => 'dirjen.binalavotas@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_II,
            'status' => User::STATUS_ACTIVE,
            'unit_id' => $unitEselonII->id,
        ]);

        // 4. Buat Unit dan Pengguna Koordinator di bawah Eselon II
        $unitKoordinator = Unit::create([
            'name' => 'Koordinator Bidang Produktivitas',
            'level' => User::ROLE_KOORDINATOR,
            'parent_unit_id' => $unitEselonII->id,
        ]);
        $userKoordinator = User::create([
            'name' => 'Koordinator Produktivitas',
            'email' => 'koor.produktivitas@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KOORDINATOR,
            'status' => User::STATUS_ACTIVE,
            'unit_id' => $unitKoordinator->id,
        ]);

        // 5. Buat Unit dan Pengguna Sub Koordinator di bawah Koordinator
        $unitSubKoordinator = Unit::create([
            'name' => 'Sub-Koordinator Peningkatan Produktivitas',
            'level' => User::ROLE_SUB_KOORDINATOR,
            'parent_unit_id' => $unitKoordinator->id,
        ]);
        $userSubKoordinator = User::create([
            'name' => 'Sub-Koordinator Peningkatan',
            'email' => 'subkoor.peningkatan@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUB_KOORDINATOR,
            'status' => User::STATUS_ACTIVE,
            'unit_id' => $unitSubKoordinator->id,
        ]);

        // 6. Buat Pengguna Staf di bawah Sub Koordinator
        $unitStaf = Unit::create([
            'name' => 'Staf Pelaksana Produktivitas', // Jabatan Staf
            'level' => User::ROLE_STAF,
            'parent_unit_id' => $unitSubKoordinator->id,
        ]);
        User::create([
            'name' => 'Andi Staf',
            'email' => 'andi.staf@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STAF,
            'status' => User::STATUS_ACTIVE,
            'unit_id' => $unitStaf->id,
        ]);
    }
}
