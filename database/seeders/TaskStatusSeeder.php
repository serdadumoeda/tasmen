<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TaskStatus;
use App\Enums\TaskStatusKey;

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
            ['key' => TaskStatusKey::PENDING->value, 'label' => 'Menunggu'],
            ['key' => TaskStatusKey::IN_PROGRESS->value, 'label' => 'Dikerjakan'],
            ['key' => TaskStatusKey::FOR_REVIEW->value, 'label' => 'Perlu Direview'],
            ['key' => TaskStatusKey::COMPLETED->value, 'label' => 'Selesai'],
        ];

        foreach ($statuses as $status) {
            TaskStatus::create($status);
        }
    }
}
