<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            UnitSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
            TimeLogSeeder::class,
            SpecialAssignmentSeeder::class,
            AdHocTaskSeeder::class,
        ]);

        // Panggil PerformanceCalculatorService untuk memastikan data kinerja terisi
        $this->command->info('Calculating performance scores for all users...');
        $calculator = new \App\Services\PerformanceCalculatorService();
        $calculator->calculateForAllUsers();
        $this->command->info('Performance scores calculated.');
    }
}
