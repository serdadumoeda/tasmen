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

        // Ambil unit yang sudah ada
        $units = Unit::whereNotNull('parent_unit_id')->get(); // Ambil unit level bawah untuk staf
        if ($units->isEmpty()) {
            $this->command->info('Unit tidak ditemukan. Jalankan UnitSeeder dulu.');
            return;
        }

        $unitKementerian = Unit::where('name', 'Kementerian Digital')->first();
        $unitKeuangan = Unit::where('name', 'Divisi Keuangan')->first();
        $unitSdm = Unit::where('name', 'Divisi SDM')->first();
        $unitAnggaran = Unit::where('name', 'Koordinator Anggaran')->first();
        $unitRekrutmen = Unit::where('name', 'Koordinator Rekrutmen')->first();
        $unitBelanja = Unit::where('name', 'Sub Koordinator Belanja')->first();
        $unitPendapatan = Unit::where('name', 'Sub Koordinator Pendapatan')->first();
        $unitTalenta = Unit::where('name', 'Sub Koordinator Pengembangan Talenta')->first();

        // 1. Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERADMIN,
            'unit_id' => null,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 2. Eselon I
        User::create([
            'name' => 'Menteri Digital',
            'email' => 'menteri.digital@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_I,
            'unit_id' => $unitKementerian->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 3. Eselon II
        User::create([
            'name' => 'Kepala Divisi Keuangan',
            'email' => 'ka.keuangan@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_II,
            'unit_id' => $unitKeuangan->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::create([
            'name' => 'Kepala Divisi SDM',
            'email' => 'ka.sdm@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ESELON_II,
            'unit_id' => $unitSdm->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 4. Koordinator
        User::create([
            'name' => 'Koordinator Anggaran',
            'email' => 'koor.anggaran@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KOORDINATOR,
            'unit_id' => $unitAnggaran->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::create([
            'name' => 'Koordinator Rekrutmen',
            'email' => 'koor.rekrutmen@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KOORDINATOR,
            'unit_id' => $unitRekrutmen->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 5. Sub-Koordinator
        User::create([
            'name' => 'Sub Koordinator Belanja',
            'email' => 'subkoor.belanja@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUB_KOORDINATOR,
            'unit_id' => $unitBelanja->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::create([
            'name' => 'Sub Koordinator Pendapatan',
            'email' => 'subkoor.pendapatan@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUB_KOORDINATOR,
            'unit_id' => $unitPendapatan->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::create([
            'name' => 'Sub Koordinator Pengembangan Talenta',
            'email' => 'subkoor.talenta@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUB_KOORDINATOR,
            'unit_id' => $unitTalenta->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        // 6. Staf (Generated using Factory)
        // Kita sudah membuat sekitar 9 user, jadi kita buat 31 lagi untuk mencapai 40.
        User::factory(31)->create([
            'role' => User::ROLE_STAF,
            'password' => Hash::make('password'),
        ])->each(function ($user) use ($units) {
            // Assign a random unit to each new staff member
            $user->unit_id = $units->random()->id;
            $user->save();
        });
    }
}
