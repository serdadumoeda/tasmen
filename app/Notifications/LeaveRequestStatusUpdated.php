<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusUpdated extends Notification
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
        $status = $this->leaveRequest->status === 'approved' ? 'Disetujui' : 'Ditolak';
        $subject = "Status Pengajuan Cuti Anda: {$status}";
        $line = "Pengajuan cuti Anda untuk tanggal {$this->leaveRequest->start_date->format('d M Y')} telah {$status}.";

        $mail = (new MailMessage)
                    ->subject($subject)
                    ->line($line);

        if ($this->leaveRequest->status === 'rejected') {
            $mail->line('Alasan Penolakan: ' . $this->leaveRequest->rejection_reason);
        }

        $mail->action('Lihat Detail', route('leaves.index'));

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->leaveRequest->status === 'approved' ? 'disetujui' : 'ditolak';

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => 'Pengajuan cuti Anda telah ' . $status . '.',
            'url' => route('leaves.index'),
        ];
    }
}
