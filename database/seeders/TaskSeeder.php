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
        $project1 = Project::find(1);
        $project2 = Project::find(2);

        if(!$project1 || !$project2) {
             $this->command->info('Proyek tidak ditemukan. Jalankan ProjectSeeder dulu.');
            return;
        }

        // ==================================================================
        // PERBAIKAN LOGIKA SEEDING TUGAS
        // ==================================================================

        // Tugas untuk Proyek 1
        // Langkah 1: Buat tugasnya dulu, tanpa 'assigned_to_id'
        $task1 = Task::create([
            'title' => 'Membuat Mockup UI/UX',
            'project_id' => $project1->id,
            'deadline' => now()->addWeeks(2),
            'progress' => 50,
            'status' => 'in_progress',
        ]);
        // Langkah 2: Lampirkan satu atau lebih user ke tugas tersebut
        $task1->assignees()->attach($project1->members->get(1)->id);

        // --- Tugas Kedua ---
        $task2 = Task::create([
            'title' => 'Setup Frontend Project',
            'project_id' => $project1->id,
            'deadline' => now()->addWeeks(3),
            'progress' => 20,
            'status' => 'pending',
        ]);
        // Kita bisa lampirkan lebih dari satu orang, contoh:
        $task2->assignees()->attach([
            $project1->members->get(1)->id,
            $project1->members->get(2)->id
        ]);


        // Tugas untuk Proyek 2
        $task3 = Task::create([
            'title' => 'Riset API Eksternal',
            'project_id' => $project2->id,
            'deadline' => now()->addDays(10),
            'progress' => 100,
            'status' => 'completed',
        ]);
        $task3->assignees()->attach($project2->members->get(1)->id);
    }
}