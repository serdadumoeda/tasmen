<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\TimeLog;
use Carbon\Carbon;

class TimeLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus data lama untuk menghindari duplikasi saat re-seed
        TimeLog::truncate();

        $tasks = Task::with('assignees')->get();

        if ($tasks->isEmpty()) {
            $this->command->info('Tidak ada tugas untuk ditambahkan time log. Jalankan TaskSeeder dulu.');
            return;
        }

        $this->command->getOutput()->progressStart($tasks->count());

        foreach ($tasks as $task) {
            if ($task->assignees->isEmpty()) {
                continue; // Lewati tugas tanpa penanggung jawab
            }

            // Jangan buat log untuk tugas yang belum dimulai
            if ($task->status === 'pending' || $task->progress === 0) {
                continue;
            }

            foreach ($task->assignees as $assignee) {
                $estimated = $task->estimated_hours;
                $actualHours = 0;

                // --- Logic to sabotage the test user ---
                if ($assignee->email === 'staf.test@example.com') {
                    // Make this user inefficient
                    $actualHours = $estimated * 1.5; // 150% of estimated time
                } else {
                    // Other users remain realistic
                    $variance = (rand(-20, 20) / 100); // Variansi antara -20% dan +20%
                    $actualHours = $estimated * (1 + $variance);
                }

                $actualHours = max(1, round($actualHours, 1));

                $startTime = Carbon::now()->subDays(rand(1, 7))->subHours(rand(1, 8));
                $endTime = $startTime->copy()->addHours($actualHours);

                TimeLog::create([
                    'task_id' => $task->id,
                    'user_id' => $assignee->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
            }
            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info(TimeLog::count() . ' time logs berhasil dibuat.');
    }
}
