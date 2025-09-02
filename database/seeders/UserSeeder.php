<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Jabatan::truncate();
        Unit::truncate();
        Schema::enableForeignKeyConstraints();

        // --- PREPARE ROLES AND SUPERADMIN ---
        $roles = Role::pluck('id', 'name')->toArray();
        $superAdminRole = $roles['superadmin'] ?? null;

        if (!$superAdminRole) {
            $this->command->error('Superadmin role not found. Please run the roles seeder first.');
            return;
        }

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $superAdminRole,
            'status' => 'active'
        ]);
        Auth::login($superAdmin);

        // --- UNIT AND JABATAN CREATION ---
        $menteri_unit = Unit::create(['name' => 'Kementerian']);
        $menteri_unit->jabatans()->create(['name' => 'Menteri']);

        $eselon1 = Unit::create(['name' => 'Badan Perencanaan dan Pengembangan', 'parent_unit_id' => $menteri_unit->id]);
        $eselon1->jabatans()->create(['name' => 'Kepala Badan Perencanaan dan Pengembangan']);

        $eselon2_keuangan = Unit::create(['name' => 'Divisi Keuangan', 'parent_unit_id' => $eselon1->id]);
        $eselon2_keuangan->jabatans()->create(['name' => 'Kepala Pusat Data dan Teknologi Informasi']);

        $eselon2_sdm = Unit::create(['name' => 'Divisi SDM', 'parent_unit_id' => $eselon1->id]);
        $eselon2_sdm->jabatans()->create(['name' => 'Kepala Pusat Sumber Daya Manusia']);

        $koordinator_anggaran = Unit::create(['name' => 'Koordinator Anggaran', 'parent_unit_id' => $eselon2_keuangan->id]);
        $koordinator_anggaran->jabatans()->create(['name' => 'Koordinator Perencanaan Anggaran']);

        $koordinator_rekrutmen = Unit::create(['name' => 'Koordinator Rekrutmen', 'parent_unit_id' => $eselon2_sdm->id]);
        $koordinator_rekrutmen->jabatans()->create(['name' => 'Koordinator Rekrutmen dan Seleksi']);

        $sub_koordinator_belanja = Unit::create(['name' => 'Sub Koordinator Belanja', 'parent_unit_id' => $koordinator_anggaran->id]);
        $sub_koordinator_belanja->jabatans()->create(['name' => 'Analis Anggaran Belanja']);

        $sub_koordinator_pendapatan = Unit::create(['name' => 'Sub Koordinator Pendapatan', 'parent_unit_id' => $koordinator_anggaran->id]);
        $sub_koordinator_pendapatan->jabatans()->create(['name' => 'Analis Anggaran Pendapatan']);

        $sub_koordinator_talenta = Unit::create(['name' => 'Sub Koordinator Pengembangan Talenta', 'parent_unit_id' => $koordinator_rekrutmen->id]);
        $sub_koordinator_talenta->jabatans()->create(['name' => 'Analis Pengembangan Talenta']);

        $sub_koordinator_units = collect([$sub_koordinator_belanja, $sub_koordinator_pendapatan, $sub_koordinator_talenta]);
        for ($i = 0; $i < 40; $i++) {
            $sub_koordinator_units->random()->jabatans()->create(['name' => 'Staf Pelaksana']);
        }

        // --- USER CREATION ---
        $createUserForJabatan = function(string $jabatanName, string $userName, string $email, string $roleName, ?User $atasan) use ($roles) {
            $jabatan = Jabatan::where('name', $jabatanName)->whereNull('user_id')->first();
            if (!$jabatan) { return null; }

            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) { return null; }

            $user = User::create([
                'name' => $userName,
                'email' => $email,
                'password' => Hash::make('password'),
                'role_id' => $roleId,
                'unit_id' => $jabatan->unit_id,
                'atasan_id' => $atasan ? $atasan->id : null,
                'status' => 'active'
            ]);
            $jabatan->update(['user_id' => $user->id]);
            return $user;
        };

        $menteri_user = $createUserForJabatan('Menteri', 'Budi Arie Setiadi', 'menteri@example.com', 'menteri', null);
        $eselon1_user = $createUserForJabatan('Kepala Badan Perencanaan dan Pengembangan', 'Anwar Sanusi', 'eselon1@example.com', 'eselon_i', $menteri_user);
        $eselon2_keuangan_user = $createUserForJabatan('Kepala Pusat Data dan Teknologi Informasi', 'Mokhammad Farid Makruf', 'ka.keuangan@example.com', 'eselon_ii', $eselon1_user);
        $eselon2_sdm_user = $createUserForJabatan('Kepala Pusat Sumber Daya Manusia', 'Kepala Divisi SDM', 'ka.sdm@example.com', 'eselon_ii', $eselon1_user);
        $koor_anggaran_user = $createUserForJabatan('Koordinator Perencanaan Anggaran', 'Ananto Wijoyo', 'koor.anggaran@example.com', 'koordinator', $eselon2_keuangan_user);
        $koor_rekrutmen_user = $createUserForJabatan('Koordinator Rekrutmen dan Seleksi', 'Koordinator Rekrutmen', 'koor.rekrutmen@example.com', 'koordinator', $eselon2_sdm_user);
        $subkoor_belanja_user = $createUserForJabatan('Analis Anggaran Belanja', 'Sub Koordinator Belanja', 'subkoor.belanja@example.com', 'sub_koordinator', $koor_anggaran_user);
        $subkoor_pendapatan_user = $createUserForJabatan('Analis Anggaran Pendapatan', 'Sub Koordinator Pendapatan', 'subkoor.pendapatan@example.com', 'sub_koordinator', $koor_anggaran_user);
        $subkoor_talenta_user = $createUserForJabatan('Analis Pengembangan Talenta', 'Sub Koordinator Pengembangan Talenta', 'subkoor.talenta@example.com', 'sub_koordinator', $koor_rekrutmen_user);

        $vacantStaffPositions = Jabatan::where('name', 'Staf Pelaksana')->whereNull('user_id')->with('unit.parentUnit.jabatans.user')->get();
        $stafRoleId = $roles['staf'] ?? null;

        if ($stafRoleId) {
            $testUserCreated = false;
            if ($vacantStaffPositions->isNotEmpty()) {
                $firstJabatan = $vacantStaffPositions->first();
                $supervisor = $firstJabatan->unit->parentUnit->jabatans->first()->user ?? null;
                if ($supervisor) {
                    $testUser = User::factory()->create([
                        'name' => 'Staf Uji Coba',
                        'email' => 'staf.test@example.com',
                        'role_id' => $stafRoleId,
                        'password' => Hash::make('password'),
                        'unit_id' => $firstJabatan->unit_id,
                        'atasan_id' => $supervisor->id
                    ]);
                    $firstJabatan->update(['user_id' => $testUser->id]);
                    $testUserCreated = true;
                }
            }

            $remainingPositions = $testUserCreated ? $vacantStaffPositions->skip(1) : $vacantStaffPositions;
            foreach ($remainingPositions as $jabatan) {
                $supervisor = $jabatan->unit->parentUnit->jabatans->first()->user ?? null;
                if (!$supervisor) {
                    $this->command->warn("Could not find supervisor for a staff in unit: " . $jabatan->unit->name);
                    continue;
                }
                $user = User::factory()->create([
                    'role_id' => $stafRoleId,
                    'password' => Hash::make('password'),
                    'unit_id' => $jabatan->unit_id,
                    'atasan_id' => $supervisor->id
                ]);
                $jabatan->update(['user_id' => $user->id]);
            }
        }
    }
}
