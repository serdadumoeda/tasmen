<?php

namespace App\Notifications;

use App\Models\Disposisi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuratDisposisiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $disposisi;

    /**
     * Create a new notification instance.
     */
    public function __construct(Disposisi $disposisi)
    {
        $this->disposisi = $disposisi;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // By default, send to database. Can add 'mail' if needed.
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Disposisi Surat Baru')
                    ->line('Anda menerima disposisi surat baru.')
                    ->line('Perihal: ' . $this->disposisi->surat->perihal)
                    ->action('Lihat Detail Surat', route('surat-masuk.show', $this->disposisi->surat_id))
                    ->line('Terima kasih.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'surat_id' => $this->disposisi->surat_id,
            'perihal' => $this->disposisi->surat->perihal,
            'pengirim_disposisi' => $this->disposisi->pengirim->name,
            'message' => 'Anda menerima disposisi baru dari ' . $this->disposisi->pengirim->name,
        ];
    }
}
