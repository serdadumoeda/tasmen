<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
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

        // --- UNIT AND JABATAN CREATION ---
        $eselon1 = Unit::create(['name' => 'Kementerian Digital', 'level' => Unit::LEVEL_ESELON_I]);
        $eselon1->jabatans()->create(['name' => 'Kepala Badan Perencanaan dan Pengembangan']);

        $eselon2_keuangan = Unit::create(['name' => 'Divisi Keuangan', 'level' => Unit::LEVEL_ESELON_II, 'parent_unit_id' => $eselon1->id]);
        $eselon2_keuangan->jabatans()->create(['name' => 'Kepala Pusat Data dan Teknologi Informasi']);

        $eselon2_sdm = Unit::create(['name' => 'Divisi SDM', 'level' => Unit::LEVEL_ESELON_II, 'parent_unit_id' => $eselon1->id]);
        $eselon2_sdm->jabatans()->create(['name' => 'Kepala Pusat Sumber Daya Manusia']);

        $koordinator_anggaran = Unit::create(['name' => 'Koordinator Anggaran', 'level' => Unit::LEVEL_KOORDINATOR, 'parent_unit_id' => $eselon2_keuangan->id]);
        $koordinator_anggaran->jabatans()->create(['name' => 'Koordinator Perencanaan Anggaran']);

        $koordinator_rekrutmen = Unit::create(['name' => 'Koordinator Rekrutmen', 'level' => Unit::LEVEL_KOORDINATOR, 'parent_unit_id' => $eselon2_sdm->id]);
        $koordinator_rekrutmen->jabatans()->create(['name' => 'Koordinator Rekrutmen dan Seleksi']);

        $sub_koordinator_belanja = Unit::create(['name' => 'Sub Koordinator Belanja', 'level' => Unit::LEVEL_SUB_KOORDINATOR, 'parent_unit_id' => $koordinator_anggaran->id]);
        $sub_koordinator_belanja->jabatans()->create(['name' => 'Analis Anggaran Belanja']);

        $sub_koordinator_pendapatan = Unit::create(['name' => 'Sub Koordinator Pendapatan', 'level' => Unit::LEVEL_SUB_KOORDINATOR, 'parent_unit_id' => $koordinator_anggaran->id]);
        $sub_koordinator_pendapatan->jabatans()->create(['name' => 'Analis Anggaran Pendapatan']);

        $sub_koordinator_talenta = Unit::create(['name' => 'Sub Koordinator Pengembangan Talenta', 'level' => Unit::LEVEL_SUB_KOORDINATOR, 'parent_unit_id' => $koordinator_rekrutmen->id]);
        $sub_koordinator_talenta->jabatans()->create(['name' => 'Analis Pengembangan Talenta']);

        $sub_koordinator_units = collect([$sub_koordinator_belanja, $sub_koordinator_pendapatan, $sub_koordinator_talenta]);
        for ($i = 0; $i < 40; $i++) {
            $sub_koordinator_units->random()->jabatans()->create(['name' => 'Staf Pelaksana']);
        }

        // --- USER CREATION ---

        User::create(['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'password' => Hash::make('password'), 'role' => User::ROLE_SUPERADMIN, 'status' => User::STATUS_ACTIVE]);

        $createUserForJabatan = function(string $jabatanName, string $userName, string $email, string $role, ?User $atasan) {
            $jabatan = Jabatan::where('name', $jabatanName)->whereNull('user_id')->first();
            if (!$jabatan) { return; }
            $user = User::create(['name' => $userName, 'email' => $email, 'password' => Hash::make('password'), 'role' => $role, 'unit_id' => $jabatan->unit_id, 'atasan_id' => $atasan ? $atasan->id : null, 'status' => User::STATUS_ACTIVE]);
            $jabatan->update(['user_id' => $user->id]);
            return $user;
        };

        $eselon1_user = $createUserForJabatan('Kepala Badan Perencanaan dan Pengembangan', 'Anwar Sanusi', 'menteri.digital@example.com', User::ROLE_ESELON_I, null);
        $eselon2_keuangan_user = $createUserForJabatan('Kepala Pusat Data dan Teknologi Informasi', 'Mokhammad Farid Makruf', 'ka.keuangan@example.com', User::ROLE_ESELON_II, $eselon1_user);
        $eselon2_sdm_user = $createUserForJabatan('Kepala Pusat Sumber Daya Manusia', 'Kepala Divisi SDM', 'ka.sdm@example.com', User::ROLE_ESELON_II, $eselon1_user);
        $koor_anggaran_user = $createUserForJabatan('Koordinator Perencanaan Anggaran', 'Ananto Wijoyo', 'koor.anggaran@example.com', User::ROLE_KOORDINATOR, $eselon2_keuangan_user);
        $koor_rekrutmen_user = $createUserForJabatan('Koordinator Rekrutmen dan Seleksi', 'Koordinator Rekrutmen', 'koor.rekrutmen@example.com', User::ROLE_KOORDINATOR, $eselon2_sdm_user);
        $subkoor_belanja_user = $createUserForJabatan('Analis Anggaran Belanja', 'Sub Koordinator Belanja', 'subkoor.belanja@example.com', User::ROLE_SUB_KOORDINATOR, $koor_anggaran_user);
        $subkoor_pendapatan_user = $createUserForJabatan('Analis Anggaran Pendapatan', 'Sub Koordinator Pendapatan', 'subkoor.pendapatan@example.com', User::ROLE_SUB_KOORDINATOR, $koor_anggaran_user);
        $subkoor_talenta_user = $createUserForJabatan('Analis Pengembangan Talenta', 'Sub Koordinator Pengembangan Talenta', 'subkoor.talenta@example.com', User::ROLE_SUB_KOORDINATOR, $koor_rekrutmen_user);

        $vacantStaffPositions = Jabatan::where('name', 'Staf Pelaksana')->whereNull('user_id')->get();
        $all_managers = collect([$subkoor_belanja_user, $subkoor_pendapatan_user, $subkoor_talenta_user]);

        foreach ($vacantStaffPositions as $jabatan) {
            $user = User::factory()->create(['role' => User::ROLE_STAF, 'password' => Hash::make('password'), 'unit_id' => $jabatan->unit_id, 'atasan_id' => $all_managers->random()->id]);
            $jabatan->update(['user_id' => $user->id]);
        }
    }
}
