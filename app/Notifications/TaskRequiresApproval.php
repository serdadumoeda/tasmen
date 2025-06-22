<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskRequiresApproval extends Notification
{
    use Queueable;

    public function __construct(public Task $task, public User $submitter)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = "Tugas '{$this->task->title}' telah diselesaikan oleh {$this->submitter->name} dan membutuhkan persetujuan Anda.";
        
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'message' => $message,
            'url' => route('tasks.edit', $this->task->id),
        ];
    }
}