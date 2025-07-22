<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Eselon I
        $eselon1 = Unit::create([
            'name' => 'Kementerian Digital',
            'level' => Unit::LEVEL_ESELON_I,
        ]);

        // Eselon II
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

        // Koordinator
        $koordinator_anggaran = Unit::create([
            'name' => 'Koordinator Anggaran',
            'level' => Unit::LEVEL_KOORDINATOR,
            'parent_unit_id' => $eselon2_keuangan->id,
        ]);

        // Sub Koordinator
        $sub_koordinator_belanja = Unit::create([
            'name' => 'Sub Koordinator Belanja',
            'level' => Unit::LEVEL_SUB_KOORDINATOR,
            'parent_unit_id' => $koordinator_anggaran->id,
        ]);
    }
}
