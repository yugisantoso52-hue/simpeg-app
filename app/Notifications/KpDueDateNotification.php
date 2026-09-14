<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KpDueDateNotification extends Notification
{
    use Queueable;

    protected $pegawaiList;

    public function __construct($pegawaiList)
    {
        $this->pegawaiList = $pegawaiList;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $count = count($this->pegawaiList);

        return [
            'title'      => 'Pengingat Kenaikan Pangkat (KP)',
            'message'    => "Terdapat {$count} pegawai yang masuk periode Kenaikan Pangkat (KP).",
            'type'       => 'kp_due',
            'icon'       => 'arrow-trending-up',
            'url'        => route('riwayat-pangkat.index', ['filter' => 'kp_due']),
            'data_count' => $count,
        ];
    }
}