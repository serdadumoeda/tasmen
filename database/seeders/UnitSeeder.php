<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Jabatan::truncate();
        Unit::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Eselon I
        $eselon1 = Unit::create([
            'name' => 'Kementerian Digital',
            'level' => Unit::LEVEL_ESELON_I,
        ]);
        $eselon1->jabatans()->create(['name' => 'Kepala Badan Perencanaan dan Pengembangan']);

        // 2. Eselon II
        $eselon2_keuangan = Unit::create([
            'name' => 'Divisi Keuangan',
            'level' => Unit::LEVEL_ESELON_II,
            'parent_unit_id' => $eselon1->id,
        ]);
        $eselon2_keuangan->jabatans()->create(['name' => 'Kepala Pusat Data dan Teknologi Informasi']);

        $eselon2_sdm = Unit::create([
            'name' => 'Divisi SDM',
            'level' => Unit::LEVEL_ESELON_II,
            'parent_unit_id' => $eselon1->id,
        ]);
        $eselon2_sdm->jabatans()->create(['name' => 'Kepala Pusat Sumber Daya Manusia']);

        // 3. Koordinator
        $koordinator_anggaran = Unit::create([
            'name' => 'Koordinator Anggaran',
            'level' => Unit::LEVEL_KOORDINATOR,
            'parent_unit_id' => $eselon2_keuangan->id,
        ]);
        $koordinator_anggaran->jabatans()->create(['name' => 'Koordinator Perencanaan Anggaran']);

        $koordinator_rekrutmen = Unit::create([
            'name' => 'Koordinator Rekrutmen',
            'level' => Unit::LEVEL_KOORDINATOR,
            'parent_unit_id' => $eselon2_sdm->id,
        ]);
        $koordinator_rekrutmen->jabatans()->create(['name' => 'Koordinator Rekrutmen dan Seleksi']);

        // 4. Sub-Koordinator
        $sub_koordinator_belanja = Unit::create([
            'name' => 'Sub Koordinator Belanja',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_anggaran->id,
        ]);
        $sub_koordinator_belanja->jabatans()->create(['name' => 'Analis Anggaran Belanja']);

        $sub_koordinator_pendapatan = Unit::create([
            'name' => 'Sub Koordinator Pendapatan',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_anggaran->id,
        ]);
        $sub_koordinator_pendapatan->jabatans()->create(['name' => 'Analis Anggaran Pendapatan']);

        $sub_koordinator_talenta = Unit::create([
            'name' => 'Sub Koordinator Pengembangan Talenta',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_rekrutmen->id,
        ]);
        $sub_koordinator_talenta->jabatans()->create(['name' => 'Analis Pengembangan Talenta']);

        // Create 40 general staff positions in the sub-coordinator units
        $sub_koordinator_units = collect([$sub_koordinator_belanja, $sub_koordinator_pendapatan, $sub_koordinator_talenta]);
        for ($i = 0; $i < 40; $i++) {
            $sub_koordinator_units->random()->jabatans()->create(['name' => 'Staf Pelaksana']);
        }
    }
}
