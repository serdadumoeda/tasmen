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
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // MODIFIKASI: Logika cerdas untuk menangani tugas proyek & ad-hoc

        // Jika tugas ini memiliki project_id (tugas proyek)
        if ($this->task->project_id) {
            return [
                'task_id' => $this->task->id,
                'task_title' => $this->task->title,
                'project_id' => $this->task->project->id,
                'project_name' => $this->task->project->name,
                'message' => "Anda ditugaskan tugas baru: '{$this->task->title}' dalam proyek '{$this->task->project->name}'",
                'url' => route('projects.show', $this->task->project_id),
            ];
        }

        // Jika tugas ini TIDAK memiliki project_id (tugas ad-hoc)
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => null, // Tidak ada proyek
            'project_name' => null, // Tidak ada proyek
            'message' => "Anda mendapat tugas harian baru: '{$this->task->title}'",
            // Arahkan ke halaman detail tugas, yang akan dialihkan ke halaman daftar tugas ad-hoc
            'url' => route('tasks.edit', $this->task->id), 
        ];
    }
}