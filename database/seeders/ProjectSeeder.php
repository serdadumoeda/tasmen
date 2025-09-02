<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua user yang bisa jadi pimpinan/pemilik proyek
        $potential_leaders = User::whereIn('role', [User::ROLE_ESELON_I, User::ROLE_ESELON_II, User::ROLE_KOORDINATOR])->get();
        $all_users = User::all();

        if ($potential_leaders->isEmpty()) {
            $this->command->info('Tidak ada user dengan peran manajerial untuk dijadikan pimpinan proyek. Jalankan UserSeeder dulu.');
            return;
        }

        Project::truncate();

        for ($i = 0; $i < 20; $i++) {
            $leader = $potential_leaders->random();
            $project = Project::factory()->create([
                'owner_id' => $leader->id,
                'leader_id' => $leader->id,
            ]);

            // Assign 3 to 10 random users to the project
            $members = $all_users->random(rand(3, min(10, $all_users->count())));
            // Pastikan leader termasuk dalam anggota
            $members->push($leader);

            $project->members()->sync($members->pluck('id')->unique());
        }
    }
}