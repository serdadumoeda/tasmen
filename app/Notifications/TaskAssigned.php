<?php
namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database']; // Kita simpan notifikasi ke database
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project->id,
            'project_name' => $this->task->project->name,
            'message' => "Anda ditugaskan tugas baru: '{$this->task->title}'",
            'url' => route('projects.show', $this->task->project_id),
        ];
    }
}