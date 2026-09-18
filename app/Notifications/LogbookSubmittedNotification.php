<?php

namespace App\Notifications;

use App\Models\Pegawai;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LogbookSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Pegawai $pegawai,
        protected int $count = 1
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $text = $this->count > 1
            ? "{$this->pegawai->nama} telah mengajukan {$this->count} aktivitas logbook kinerja baru untuk diverifikasi."
            : "{$this->pegawai->nama} telah mengajukan 1 aktivitas logbook kinerja baru untuk diverifikasi.";

        return [
            'title'      => 'Pengajuan Logbook Kinerja Baru',
            'message'    => $text,
            'type'       => 'logbook_submitted',
            'icon'       => 'clipboard-document-check',
            'url'        => route('admin.logbook.index', ['status' => 'diajukan']),
            'data_count' => $this->count,
        ];
    }
}
