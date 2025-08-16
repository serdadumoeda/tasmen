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
        $status = $this->faker->randomElement(['pending', 'in_progress', 'completed']);
        $progress = match($status) {
            'pending' => 0,
            'in_progress' => $this->faker->numberBetween(10, 90),
            'completed' => 100,
            default => 0,
        };

        $deadline = $this->faker->dateTimeBetween('+1 week', '+3 months');
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'status' => $status,
            'progress' => $progress,
            'start_date' => $this->faker->dateTimeBetween('-1 week', $deadline),
            'deadline' => $deadline,
            'project_id' => Project::factory(),
            'estimated_hours' => $this->faker->numberBetween(4, 40),
            'priority' => $this->faker->randomElement(Task::PRIORITIES),
        ];
    }
}
