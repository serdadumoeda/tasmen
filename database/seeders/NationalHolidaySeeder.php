<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\NationalHoliday;

class NationalHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = now()->year;

        $holidays = [
            ['name' => 'Tahun Baru', 'date' => "{$year}-01-01"],
            ['name' => 'Isra Mi\'raj', 'date' => "{$year}-02-18"],
            ['name' => 'Hari Raya Nyepi', 'date' => "{$year}-03-22"],
            ['name' => 'Wafat Isa Almasih', 'date' => "{$year}-04-07"],
            ['name' => 'Hari Buruh', 'date' => "{$year}-05-01"],
            ['name' => 'Hari Raya Idul Fitri', 'date' => "{$year}-04-22"],
            ['name' => 'Kenaikan Isa Almasih', 'date' => "{$year}-05-18"],
            ['name' => 'Hari Lahir Pancasila', 'date' => "{$year}-06-01"],
            ['name' => 'Hari Raya Waisak', 'date' => "{$year}-06-04"],
            ['name' => 'Hari Raya Idul Adha', 'date' => "{$year}-06-29"],
            ['name' => 'Tahun Baru Islam', 'date' => "{$year}-07-19"],
            ['name' => 'Hari Kemerdekaan', 'date' => "{$year}-08-17"],
            ['name' => 'Maulid Nabi Muhammad SAW', 'date' => "{$year}-09-28"],
            ['name' => 'Hari Raya Natal', 'date' => "{$year}-12-25"],
        ];

        foreach ($holidays as $holiday) {
            NationalHoliday::updateOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                $holiday
            );
        }
    }
}
