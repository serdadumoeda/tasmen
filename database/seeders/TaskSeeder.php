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

            // Buat 5 sampai 15 tugas untuk setiap proyek
            $numberOfTasks = rand(5, 15);

            for ($i = 0; $i < $numberOfTasks; $i++) {
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                ]);

                // Lampirkan 1 sampai 3 anggota random dari proyek ke tugas ini
                $assignees = $project->members->random(rand(1, min(3, $project->members->count())));
                $task->assignees()->attach($assignees->pluck('id'));
            }
        }

        // --- Logic to overload the test user ---
        $this->command->info('Overloading test user...');
        $testUser = User::where('email', 'staf.test@example.com')->first();
        $project = Project::first(); // Assign to the first project

        if ($testUser && $project) {
            if (!$project->members->contains($testUser)) {
                $project->members()->attach($testUser->id);
            }

            for ($i = 0; $i < 10; $i++) {
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                    'estimated_hours' => 20, // 10 tasks * 20 hours = 200 hours
                    'title' => 'Tugas Beban Kerja Ekstra ' . ($i + 1),
                ]);
                $task->assignees()->attach($testUser->id);
            }
            $this->command->info('Test user has been overloaded with 10 extra tasks.');
        }
    }
}