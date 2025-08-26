<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestForwarded extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public LeaveRequest $leaveRequest)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $applicantName = $this->leaveRequest->user->name;

        return (new MailMessage)
                    ->subject("Persetujuan Cuti Lanjutan: {$applicantName}")
                    ->line("Sebuah pengajuan cuti untuk {$applicantName} telah disetujui oleh atasan langsung dan membutuhkan persetujuan Anda.")
                    ->action('Lihat Detail Pengajuan', route('leaves.show', $this->leaveRequest->id))
                    ->line('Mohon untuk segera ditindaklanjuti.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'applicant_name' => $this->leaveRequest->user->name,
            'message' => 'Pengajuan cuti dari ' . $this->leaveRequest->user->name . ' menunggu persetujuan lanjutan dari Anda.',
            'url' => route('leaves.show', $this->leaveRequest->id),
        ];
    }
}
