<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PensiunDueDateNotification extends Notification
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
        $count = is_countable($this->pegawaiList) ? count($this->pegawaiList) : 0;

        return [
            'title'      => 'Pengingat Masa Pensiun (BUP 58)',
            'message'    => "Terdapat {$count} pegawai yang akan memasuki Batas Usia Pensiun dalam 1 tahun ke depan.",
            'type'       => 'pensiun_due',
            'icon'       => 'building-office-2',
            'url'        => route('dashboard'),
            'data_count' => $count,
        ];
    }
}
