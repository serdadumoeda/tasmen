<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Models\Project;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $deadline = $this->faker->dateTimeBetween('+1 week', '+3 months');
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'task_status_id' => \App\Models\TaskStatus::inRandomOrder()->first()->id,
            'priority_level_id' => \App\Models\PriorityLevel::inRandomOrder()->first()->id,
            'progress' => $this->faker->numberBetween(0, 100),
            'start_date' => $this->faker->dateTimeBetween('-1 week', $deadline),
            'deadline' => $deadline,
            'project_id' => Project::factory(),
            'estimated_hours' => $this->faker->numberBetween(4, 40),
        ];
    }
}
