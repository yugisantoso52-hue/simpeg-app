<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StrSipExpiringNotification extends Notification
{
    use Queueable;

    protected $expiringDocs;

    public function __construct($expiringDocs)
    {
        $this->expiringDocs = $expiringDocs;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $count = is_countable($this->expiringDocs) ? count($this->expiringDocs) : 0;

        return [
            'title'      => '⚠️ Peringatan Masa Berlaku STR & SIP Ners',
            'message'    => "Terdapat {$count} dokumen STR / SIP Ners yang kedaluwarsa atau segera berakhir dalam tempo 6 bulan ke depan.",
            'type'       => 'str_sip_expiring',
            'icon'       => 'academic-cap',
            'url'        => route('riwayat-str-sip.index', ['status' => 'segera_berakhir']),
            'data_count' => $count,
        ];
    }
}
