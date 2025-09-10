<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateUnitKodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('units')
            ->where('name', 'Pusat Data dan Teknologi Informasi Ketenagakerjaan')
            ->update(['kode' => 'Pusdatin']);
    }
}
