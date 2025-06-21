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

        if ($users->count() < 5) { // Butuh lebih banyak user
            $this->command->info('Tidak cukup user untuk membuat proyek. Jalankan UserSeeder dulu.');
            return;
        }

        // Ambil beberapa user pimpinan untuk menjadi owner/leader
        $leader1 = User::where('role', 'Koordinator')->first() ?? $users->get(2);
        $leader2 = User::where('role', 'Eselon II')->first() ?? $users->get(1);

        // Proyek pertama
        $project1 = Project::create([
            'name' => 'Desain Ulang Website Internal',
            'description' => 'Proyek untuk merombak tampilan dan fungsionalitas website utama.',
            'leader_id' => $leader1->id,
            'owner_id' => $leader1->id, // PERBAIKAN: Tetapkan owner
        ]);
        $project1->members()->attach($users->pluck('id')->slice(2, 4));

        // Proyek kedua
        $project2 = Project::create([
            'name' => 'Aplikasi Mobile Presensi Karyawan',
            'description' => 'Membangun aplikasi mobile untuk mempermudah proses presensi.',
            'leader_id' => $leader2->id,
            'owner_id' => $leader2->id, // PERBAIKAN: Tetapkan owner
        ]);
        $project2->members()->attach($users->pluck('id')->slice(1, 5));
    }
}