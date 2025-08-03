<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // Ambil unit yang sudah ada
        $units = Unit::all();
        if ($units->isEmpty()) {
            $this->command->info('Unit tidak ditemukan. Jalankan UnitSeeder dulu.');
            return;
        }

        $unitKementerian = $units->where('name', 'Kementerian Digital')->first();
        $unitKeuangan = $units->where('name', 'Divisi Keuangan')->first();
        $unitSdm = $units->where('name', 'Divisi SDM')->first();
        $unitAnggaran = $units->where('name', 'Koordinator Anggaran')->first();
        $unitRekrutmen = $units->where('name', 'Koordinator Rekrutmen')->first();
        $unitBelanja = $units->where('name', 'Sub Koordinator Belanja')->first();
        $unitPendapatan = $units->where('name', 'Sub Koordinator Pendapatan')->first();
        $unitTalenta = $units->where('name', 'Sub Koordinator Pengembangan Talenta')->first();

        // 1. Super Admin (No supervisor)
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERADMIN,
            'jabatan' => 'Administrator Sistem Utama',
            'unit_id' => null,
            'atasan_id' => null,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 2. Eselon I (No supervisor in this context)
        $eselon1 = User::create([
            'name' => 'Anwar Sanusi',
            'email' => 'menteri.digital@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_I,
            'jabatan' => 'Kepala Badan Perencanaan dan Pengembangan',
            'unit_id' => $unitKementerian->id,
            'atasan_id' => null,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 3. Eselon II (Supervisor is Eselon I)
        $eselon2_keuangan = User::create([
            'name' => 'Mokhammad Farid Makruf',
            'email' => 'ka.keuangan@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_II,
            'jabatan' => 'Kepala Pusat Data dan Teknologi Informasi',
            'unit_id' => $unitKeuangan->id,
            'atasan_id' => $eselon1->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        $eselon2_sdm = User::create([
            'name' => 'Kepala Divisi SDM',
            'email' => 'ka.sdm@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_II,
            'jabatan' => 'Kepala Pusat Sumber Daya Manusia',
            'unit_id' => $unitSdm->id,
            'atasan_id' => $eselon1->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 4. Koordinator (Supervisor is Eselon II)
        $koor_anggaran = User::create([
            'name' => 'Ananto Wijoyo',
            'email' => 'koor.anggaran@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KOORDINATOR,
            'jabatan' => 'Koordinator Perencanaan Anggaran',
            'unit_id' => $unitAnggaran->id,
            'atasan_id' => $eselon2_keuangan->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        $koor_rekrutmen = User::create([
            'name' => 'Koordinator Rekrutmen',
            'email' => 'koor.rekrutmen@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KOORDINATOR,
            'jabatan' => 'Koordinator Rekrutmen dan Seleksi',
            'unit_id' => $unitRekrutmen->id,
            'atasan_id' => $eselon2_sdm->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 5. Sub-Koordinator (Supervisor is Koordinator)
        $subkoor_belanja = User::create([
            'name' => 'Sub Koordinator Belanja',
            'email' => 'subkoor.belanja@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUB_KOORDINATOR,
            'jabatan' => 'Analis Anggaran Belanja',
            'unit_id' => $unitBelanja->id,
            'atasan_id' => $koor_anggaran->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        $subkoor_pendapatan = User::create([
            'name' => 'Sub Koordinator Pendapatan',
            'email' => 'subkoor.pendapatan@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUB_KOORDINATOR,
            'jabatan' => 'Analis Anggaran Pendapatan',
            'unit_id' => $unitPendapatan->id,
            'atasan_id' => $koor_anggaran->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        $subkoor_talenta = User::create([
            'name' => 'Sub Koordinator Pengembangan Talenta',
            'email' => 'subkoor.talenta@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUB_KOORDINATOR,
            'jabatan' => 'Analis Pengembangan Talenta',
            'unit_id' => $unitTalenta->id,
            'atasan_id' => $koor_rekrutmen->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $all_managers = collect([$eselon1, $eselon2_keuangan, $eselon2_sdm, $koor_anggaran, $koor_rekrutmen, $subkoor_belanja, $subkoor_pendapatan, $subkoor_talenta]);

        // 6. Staf (Generated using Factory)
        User::factory(31)->create([
            'role' => User::ROLE_STAF,
            'password' => Hash::make('password'),
        ])->each(function ($user) use ($units, $all_managers) {
            $user->jabatan = 'Staf Pelaksana';
            // Assign a random unit and a random manager as supervisor
            $user->unit_id = $units->whereNotNull('parent_unit_id')->random()->id;
            $user->atasan_id = $all_managers->random()->id;
            $user->save();
        });
    }
}
