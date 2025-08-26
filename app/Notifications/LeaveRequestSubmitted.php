<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification
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
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $applicantName = $this->leaveRequest->user->name;
        $leaveType = $this->leaveRequest->leaveType->name;
        $startDate = $this->leaveRequest->start_date->format('d M Y');
        $endDate = $this->leaveRequest->end_date->format('d M Y');

        return (new MailMessage)
                    ->subject("Pengajuan Cuti Baru: {$applicantName}")
                    ->line("Pengajuan cuti baru telah diajukan oleh {$applicantName} ({$leaveType}).")
                    ->line("Tanggal: {$startDate} - {$endDate}.")
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
            'message' => 'Pengajuan cuti baru dari ' . $this->leaveRequest->user->name . ' menunggu persetujuan Anda.',
            'url' => route('leaves.show', $this->leaveRequest->id),
        ];
    }
}
