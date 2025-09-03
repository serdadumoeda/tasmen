<?php

namespace App\Notifications;

use App\Models\Disposisi;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuratDisposisiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $surat;
    protected $pengirim;
    protected $isTembusan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Surat $surat, User $pengirim, bool $isTembusan = false)
    {
        $this->surat = $surat;
        $this->pengirim = $pengirim;
        $this->isTembusan = $isTembusan;
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
        $subject = $this->isTembusan ? 'Tembusan Surat' : 'Disposisi Surat Baru';
        $line = $this->isTembusan ? 'Anda menerima tembusan surat.' : 'Anda menerima disposisi surat baru.';

        return (new MailMessage)
                    ->subject($subject)
                    ->line($line)
                    ->line('Perihal: ' . $this->surat->perihal)
                    ->action('Lihat Detail Surat', route('surat-masuk.show', $this->surat->id))
                    ->line('Terima kasih.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = $this->isTembusan
            ? 'Anda menerima tembusan surat dari ' . $this->pengirim->name
            : 'Anda menerima disposisi baru dari ' . $this->pengirim->name;

        return [
            'surat_id' => $this->surat->id,
            'perihal' => $this->surat->perihal,
            'pengirim_disposisi' => $this->pengirim->name,
            'message' => $message,
            'is_tembusan' => $this->isTembusan,
        ];
    }
}
