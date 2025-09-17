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
        // MODIFIKASI: Logika cerdas untuk menangani tugas kegiatan & ad-hoc

        // Jika tugas ini memiliki project_id (tugas kegiatan)
        if ($this->task->project_id) {
            return [
                'task_id' => $this->task->id,
                'task_title' => $this->task->title,
                'project_id' => $this->task->project->id,
                'project_name' => $this->task->project->name,
                'message' => "Anda ditugaskan tugas baru: '{$this->task->title}' dalam kegiatan '{$this->task->project->name}'",
                'url' => route('projects.show', $this->task->project_id),
            ];
        }

        // Jika tugas ini TIDAK memiliki project_id (tugas ad-hoc)
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => null, // Tidak ada kegiatan
            'project_name' => null, // Tidak ada kegiatan
            'message' => "Anda mendapat tugas harian baru: '{$this->task->title}'",
            // Arahkan ke halaman daftar tugas ad-hoc, bukan halaman edit.
            'url' => route('adhoc-tasks.index'),
        ];
    }
}