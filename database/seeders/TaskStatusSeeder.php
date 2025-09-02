<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TaskStatus;

class TaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Using truncate to ensure a clean slate on every seed.
        DB::table('task_statuses')->truncate();

        $statuses = [
            ['key' => 'pending', 'label' => 'Menunggu'],
            ['key' => 'in_progress', 'label' => 'Dikerjakan'],
            ['key' => 'for_review', 'label' => 'Perlu Direview'],
            ['key' => 'completed', 'label' => 'Selesai'],
        ];

        foreach ($statuses as $status) {
            TaskStatus::create($status);
        }
    }
}
