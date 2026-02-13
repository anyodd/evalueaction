<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KertasKerjaSubmitted extends Notification
{
    protected $kertasKerja;
    protected $submitter;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($kertasKerja, $submitter)
    {
        $this->kertasKerja = $kertasKerja;
        $this->submitter = $submitter;
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
            'submitter_name' => $this->submitter->name,
            'message' => $this->submitter->name . " telah melaporkan Kertas Kerja: " . $this->kertasKerja->judul_kk,
            'url' => route('kertas-kerja.edit', $this->kertasKerja->id),
        ];
    }
}
