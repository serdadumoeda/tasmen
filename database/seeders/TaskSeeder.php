<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::with('members')->get();

        if ($projects->isEmpty()) {
            $this->command->info('Tidak ada proyek untuk ditambahkan tugas. Jalankan ProjectSeeder dulu.');
            return;
        }

        Task::truncate();

        foreach ($projects as $project) {
            if ($project->members->isEmpty()) {
                continue; // Lewati proyek tanpa anggota
            }

            // Buat 5 sampai 10 tugas untuk setiap proyek
            $numberOfTasks = rand(5, 10);

            for ($i = 0; $i < $numberOfTasks; $i++) {
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                ]);

                // Lampirkan 1 sampai 3 anggota random dari proyek ke tugas ini
                $assignees = $project->members->random(rand(1, min(3, $project->members->count())));
                $task->assignees()->attach($assignees->pluck('id'));
            }
        }
    }
}