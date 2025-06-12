<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->count() < 2) {
            $this->command->info('Tidak cukup user untuk membuat proyek. Jalankan UserSeeder dulu.');
            return;
        }

        // Proyek pertama
        $project1 = Project::create([
            'name' => 'Desain Ulang Website',
            'description' => 'Proyek untuk merombak tampilan dan fungsionalitas website utama.',
            'leader_id' => $users->first()->id, // User pertama jadi ketua
        ]);
        // Tambahkan user 1, 2, 3 sebagai anggota
        $project1->members()->attach($users->pluck('id')->slice(0, 3));

        // Proyek kedua
        $project2 = Project::create([
            'name' => 'Aplikasi Mobile Presensi',
            'description' => 'Membangun aplikasi mobile untuk presensi.',
            'leader_id' => $users->get(1)->id, // User kedua jadi ketua
        ]);
        // Tambahkan user 2, 4, 5 sebagai anggota
        $project2->members()->attach($users->pluck('id')->slice(1, 3));
    }
}