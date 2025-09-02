<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PriorityLevel;

class PriorityLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('priority_levels')->truncate();

        $priorities = [
            ['name' => 'Low', 'level' => 1],
            ['name' => 'Medium', 'level' => 2],
            ['name' => 'High', 'level' => 3],
            ['name' => 'Critical', 'level' => 4],
        ];

        foreach ($priorities as $priority) {
            PriorityLevel::create($priority);
        }
    }
}
