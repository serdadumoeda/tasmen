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
            RoleSeeder::class,
            OrganizationalDataSeeder::class,
            LeaveTypesSeeder::class,
            TaskStatusSeeder::class,
            PriorityLevelSeeder::class,
            LeaveRequestSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
            TimeLogSeeder::class,
            SpecialAssignmentSeeder::class,
            AdHocTaskSeeder::class,
        ]);

        // Panggil PerformanceCalculatorService untuk memastikan data kinerja terisi
        if (!app()->environment('testing')) {
            $this->command->info('');
            $this->command->info('--- Calculating Performance Scores ---');
            $calculator = new \App\Services\PerformanceCalculatorService();
            $calculator->calculateForAllUsers();
            $this->command->info('Performance scores calculated successfully.');
        }
    }
}
