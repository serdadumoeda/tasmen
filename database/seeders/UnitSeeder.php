<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Unit::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Eselon I
        $eselon1 = Unit::create([
            'name' => 'Kementerian Digital',
            'level' => Unit::LEVEL_ESELON_I,
            'parent_unit_id' => null,
        ]);

        // 2. Eselon II
        $eselon2_keuangan = Unit::create([
            'name' => 'Divisi Keuangan',
            'level' => Unit::LEVEL_ESELON_II,
            'parent_unit_id' => $eselon1->id,
        ]);
        $eselon2_sdm = Unit::create([
            'name' => 'Divisi SDM',
            'level' => Unit::LEVEL_ESELON_II,
            'parent_unit_id' => $eselon1->id,
        ]);

        // 3. Koordinator
        $koordinator_anggaran = Unit::create([
            'name' => 'Koordinator Anggaran',
            'level' => Unit::LEVEL_KOORDINATOR,
            'parent_unit_id' => $eselon2_keuangan->id,
        ]);
        $koordinator_rekrutmen = Unit::create([
            'name' => 'Koordinator Rekrutmen',
            'level' => Unit::LEVEL_KOORDINATOR,
            'parent_unit_id' => $eselon2_sdm->id,
        ]);

        // 4. Sub-Koordinator
        $sub_koordinator_belanja = Unit::create([
            'name' => 'Sub Koordinator Belanja',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_anggaran->id,
        ]);
        $sub_koordinator_pendapatan = Unit::create([
            'name' => 'Sub Koordinator Pendapatan',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_anggaran->id,
        ]);
        $sub_koordinator_talenta = Unit::create([
            'name' => 'Sub Koordinator Pengembangan Talenta',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_rekrutmen->id,
        ]);
    }
}
