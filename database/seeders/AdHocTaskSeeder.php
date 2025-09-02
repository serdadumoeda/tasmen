<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskStatus;
use App\Models\PriorityLevel;
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
        $users = User::whereHas('role', function ($query) {
            $query->where('name', '!=', 'superadmin');
        })->get();

        if ($users->isEmpty()) {
            $this->command->info('No users found to assign ad-hoc tasks.');
            return;
        }

        $statusIds = TaskStatus::pluck('id')->toArray();
        $priorityIds = PriorityLevel::pluck('id')->toArray();

        for ($i = 0; $i < 15; $i++) {
            $creator = $users->random();
            Auth::login($creator);

            $task = Task::create([
                'title' => $faker->sentence(4),
                'description' => $faker->realText(150),
                'deadline' => $faker->dateTimeBetween('+1 week', '+3 months'),
                'progress' => $faker->numberBetween(0, 100),
                'task_status_id' => $faker->randomElement($statusIds),
                'priority_level_id' => $faker->randomElement($priorityIds),
                'project_id' => null,
                'estimated_hours' => $faker->randomFloat(1, 1, 8),
            ]);

            $task->assignees()->attach(
                $users->random(rand(1, min(2, $users->count())))->pluck('id')->toArray()
            );

            Auth::logout();
        }
    }
}
