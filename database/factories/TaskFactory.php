<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Models\Project;
use App\Models\TaskStatus;
use App\Models\PriorityLevel;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        // Fetch IDs from the database once
        $statusIds = TaskStatus::pluck('id')->toArray();
        $priorityIds = PriorityLevel::pluck('id')->toArray();

        $statusId = $this->faker->randomElement($statusIds);
        $status = TaskStatus::find($statusId);

        $progress = match($status->key) {
            'pending' => 0,
            'in_progress' => $this->faker->numberBetween(10, 90),
            'completed' => 100,
            default => 0,
        };

        $deadline = $this->faker->dateTimeBetween('+1 week', '+3 months');
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'task_status_id' => $statusId,
            'priority_level_id' => $this->faker->randomElement($priorityIds),
            'progress' => $progress,
            'start_date' => $this->faker->dateTimeBetween('-1 week', $deadline),
            'deadline' => $deadline,
            'project_id' => Project::factory(),
            'estimated_hours' => $this->faker->numberBetween(4, 40),
        ];
    }
}
