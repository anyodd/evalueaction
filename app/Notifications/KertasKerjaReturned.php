<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KertasKerjaReturned extends Notification
{
    protected $kertasKerja;
    protected $reviewer;
    protected $reason;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($kertasKerja, $reviewer, $reason = '')
    {
        $this->kertasKerja = $kertasKerja;
        $this->reviewer = $reviewer;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'kk_id' => $this->kertasKerja->id,
            'kk_title' => $this->kertasKerja->judul_kk,
            'reviewer_name' => $this->reviewer->name,
            'message' => $this->reviewer->name . " mengembalikan Kertas Kerja: " . $this->kertasKerja->judul_kk . ". Alasan: " . ($this->reason ?: 'Perlu perbaikan.'),
            'url' => route('kertas-kerja.edit', $this->kertasKerja->id),
        ];
    }
}
