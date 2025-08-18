<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Auth;

class AdHocTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $users = User::where('role', '!=', User::ROLE_SUPERADMIN)->get();

        if ($users->isEmpty()) {
            $this->command->info('No users found to assign ad-hoc tasks.');
            return;
        }

        for ($i = 0; $i < 15; $i++) { // Membuat 15 record
            // Temporarily authenticate as a random user to be the task creator
            $creator = $users->random();
            Auth::login($creator);

            $task = Task::create([
                'title' => $faker->sentence(4),
                'description' => $faker->realText(150),
                'deadline' => $faker->dateTimeBetween('+1 week', '+3 months'),
                'progress' => $faker->numberBetween(0, 100),
                'status' => $faker->randomElement(['pending', 'in_progress', 'completed']),
                'project_id' => null, // Kunci untuk ad-hoc task
                'estimated_hours' => $faker->randomFloat(1, 1, 8),
            ]);

            // Tugaskan ke 1 atau 2 user acak
            $task->assignees()->attach(
                $users->random(rand(1, min(2, $users->count())))->pluck('id')->toArray()
            );

            Auth::logout();
        }
    }
}
