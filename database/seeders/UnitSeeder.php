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
        Unit::create([
            'name' => 'Kementerian Digital',
            'level' => Unit::LEVEL_ESELON_I,
            'parent_unit_id' => null,
        ]);

        // 2. Eselon II
        $eselon1 = Unit::where('name', 'Kementerian Digital')->first();
        Unit::create([
            'name' => 'Divisi Keuangan',
            'level' => Unit::LEVEL_ESELON_II,
            'parent_unit_id' => $eselon1->id,
        ]);
        Unit::create([
            'name' => 'Divisi SDM',
            'level' => Unit::LEVEL_ESELON_II,
            'parent_unit_id' => $eselon1->id,
        ]);

        // 3. Koordinator
        $eselon2_keuangan = Unit::where('name', 'Divisi Keuangan')->first();
        Unit::create([
            'name' => 'Koordinator Anggaran',
            'level' => Unit::LEVEL_KOORDINATOR,
            'parent_unit_id' => $eselon2_keuangan->id,
        ]);

        $eselon2_sdm = Unit::where('name', 'Divisi SDM')->first();
        Unit::create([
            'name' => 'Koordinator Rekrutmen',
            'level' => Unit::LEVEL_KOORDINATOR,
            'parent_unit_id' => $eselon2_sdm->id,
        ]);

        // 4. Sub-Koordinator
        $koordinator_anggaran = Unit::where('name', 'Koordinator Anggaran')->first();
        Unit::create([
            'name' => 'Sub Koordinator Belanja',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_anggaran->id,
        ]);
        Unit::create([
            'name' => 'Sub Koordinator Pendapatan',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_anggaran->id,
        ]);
    }
}
