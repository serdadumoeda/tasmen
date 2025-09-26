<?php
namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentOnTask extends Notification
{
    use Queueable;

    public function __construct(public Comment $comment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'comment_body' => $this->comment->body,
            'commenter_name' => $this->comment->user->name,
            'task_id' => $this->comment->task->id,
            'task_title' => $this->comment->task->title,
            'project_id' => $this->comment->task->project->id,
            'message' => "{$this->comment->user->name} berkomentar pada tugas: '{$this->comment->task->title}'",
            'url' => route('projects.show', $this->comment->task->project_id) . '#task-' . $this->comment->task->id,
        ];
    }
}
