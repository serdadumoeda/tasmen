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

        // Tugas untuk Proyek 1
        Task::create([
            'title' => 'Membuat Mockup UI/UX',
            'project_id' => $project1->id,
            'assigned_to_id' => $project1->members->get(1)->id, // Anggota kedua
            'deadline' => now()->addWeeks(2),
            'progress' => 50,
            'status' => 'in_progress',
        ]);
        Task::create([
            'title' => 'Setup Frontend Project',
            'project_id' => $project1->id,
            'assigned_to_id' => $project1->members->get(2)->id, // Anggota ketiga
            'deadline' => now()->addWeeks(3),
            'progress' => 20,
            'status' => 'pending',
        ]);

        // Tugas untuk Proyek 2
        Task::create([
            'title' => 'Riset API Eksternal',
            'project_id' => $project2->id,
            'assigned_to_id' => $project2->members->get(1)->id,
            'deadline' => now()->addDays(10),
            'progress' => 100,
            'status' => 'completed',
        ]);
    }
}