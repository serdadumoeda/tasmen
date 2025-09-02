<?php
namespace App\Notifications;

use App\Models\Task;
use App\Models\NotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $template;
    protected $placeholders;

    public function __construct(public Task $task)
    {
        $this->template = NotificationTemplate::where('key', 'task_assigned')->first();
    }

    public function via(object $notifiable): array
    {
        // Return database channel if template exists, otherwise none.
        return $this->template ? ['database'] : [];
    }

    private function getPlaceholders(object $notifiable): array
    {
        if (!isset($this->placeholders)) {
            $this->placeholders = [
                '{{user_name}}' => $notifiable->name,
                '{{task_title}}' => $this->task->title,
                '{{project_title}}' => $this->task->project->name ?? 'Tugas Harian',
            ];
        }
        return $this->placeholders;
    }

    private function replacePlaceholders(string $content, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }

    public function toArray(object $notifiable): array
    {
        if (!$this->template) {
            return []; // Don't send notification if template is missing
        }

        $placeholders = $this->getPlaceholders($notifiable);
        $message = $this->replacePlaceholders($this->template->body, $placeholders);

        $url = $this->task->project_id
            ? route('projects.show', $this->task->project_id)
            : route('tasks.edit', $this->task->id);

        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'message' => $message,
            'url' => $url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if (!$this->template) {
            // Return a dummy message or handle error
            return (new MailMessage)->line('Error: Notification template not found.');
        }

        $placeholders = $this->getPlaceholders($notifiable);
        $subject = $this->replacePlaceholders($this->template->subject, $placeholders);
        $body = $this->replacePlaceholders($this->template->body, $placeholders);

        $url = $this->task->project_id
            ? route('projects.show', $this->task->project_id)
            : route('tasks.edit', $this->task->id);

        return (new MailMessage)
                    ->subject($subject)
                    ->line($body)
                    ->action('Lihat Tugas', $url);
    }
}