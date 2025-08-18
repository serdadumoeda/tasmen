<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Comment;
use App\Models\User;

class UserMentioned extends Notification
{
    use Queueable;

    protected $comment;
    protected $mentioner;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comment $comment, User $mentioner)
    {
        $this->comment = $comment;
        $this->mentioner = $mentioner;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // We only need database notifications for the in-app center
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $task = $this->comment->task;
        $project = $task->project;

        $title = "Anda disebut dalam sebuah komentar";
        $message = "{$this->mentioner->name} menyebut Anda di tugas '{$task->title}'.";
        $link = route('projects.show', $project) . '#task-' . $task->id;

        return [
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'comment_id' => $this->comment->id,
        ];
    }
}
