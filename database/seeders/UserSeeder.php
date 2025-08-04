<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

use App\Models\Jabatan;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Super Admin (No position, no supervisor)
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERADMIN,
            'unit_id' => null,
            'atasan_id' => null,
            'status' => User::STATUS_ACTIVE,
        ]);

        // Helper function to create a user and assign them to a specific Jabatan
        $createUserForJabatan = function(string $jabatanName, string $userName, string $email, string $role, ?User $atasan) {
            $jabatan = Jabatan::where('name', $jabatanName)->whereNull('user_id')->first();
            if (!$jabatan) {
                $this->command->error("Jabatan '{$jabatanName}' tidak ditemukan atau sudah terisi.");
                return null;
            }

            $user = User::create([
                'name' => $userName,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => $role,
                'unit_id' => $jabatan->unit_id,
                'atasan_id' => $atasan ? $atasan->id : null,
                'status' => User::STATUS_ACTIVE,
            ]);

            $jabatan->user_id = $user->id;
            $jabatan->save();

            return $user;
        };

        // 2. Create users according to the hierarchy
        $eselon1 = $createUserForJabatan('Kepala Badan Perencanaan dan Pengembangan', 'Anwar Sanusi', 'menteri.digital@example.com', User::ROLE_ESELON_I, null);

        $eselon2_keuangan = $createUserForJabatan('Kepala Pusat Data dan Teknologi Informasi', 'Mokhammad Farid Makruf', 'ka.keuangan@example.com', User::ROLE_ESELON_II, $eselon1);
        $eselon2_sdm = $createUserForJabatan('Kepala Pusat Sumber Daya Manusia', 'Kepala Divisi SDM', 'ka.sdm@example.com', User::ROLE_ESELON_II, $eselon1);

        $koor_anggaran = $createUserForJabatan('Koordinator Perencanaan Anggaran', 'Ananto Wijoyo', 'koor.anggaran@example.com', User::ROLE_KOORDINATOR, $eselon2_keuangan);
        $koor_rekrutmen = $createUserForJabatan('Koordinator Rekrutmen dan Seleksi', 'Koordinator Rekrutmen', 'koor.rekrutmen@example.com', User::ROLE_KOORDINATOR, $eselon2_sdm);

        $subkoor_belanja = $createUserForJabatan('Analis Anggaran Belanja', 'Sub Koordinator Belanja', 'subkoor.belanja@example.com', User::ROLE_SUB_KOORDINATOR, $koor_anggaran);
        $subkoor_pendapatan = $createUserForJabatan('Analis Anggaran Pendapatan', 'Sub Koordinator Pendapatan', 'subkoor.pendapatan@example.com', User::ROLE_SUB_KOORDINATOR, $koor_anggaran);
        $subkoor_talenta = $createUserForJabatan('Analis Pengembangan Talenta', 'Sub Koordinator Pengembangan Talenta', 'subkoor.talenta@example.com', User::ROLE_SUB_KOORDINATOR, $koor_rekrutmen);

        // 3. Assign Staf to the remaining vacant "Staf Pelaksana" positions
        $vacantStaffPositions = Jabatan::where('name', 'Staf Pelaksana')->whereNull('user_id')->get();
        $all_managers = collect([$subkoor_belanja, $subkoor_pendapatan, $subkoor_talenta]);

        foreach ($vacantStaffPositions as $jabatan) {
            $user = User::factory()->create([
                'role' => User::ROLE_STAF,
                'password' => Hash::make('password'),
                'unit_id' => $jabatan->unit_id,
                'atasan_id' => $all_managers->random()->id,
            ]);

            $jabatan->user_id = $user->id;
            $jabatan->save();
        }
    }
}
