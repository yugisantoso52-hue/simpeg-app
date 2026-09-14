<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KgbDueDateNotification extends Notification
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
            'title'      => 'Pengingat KGB Pegawai',
            'message'    => "Terdapat {$count} pegawai yang mendekati/jatuh tempo Kenaikan Gaji Berkala (KGB).",
            'type'       => 'kgb_due',
            'icon'       => 'banknotes',
            'url'        => route('pegawai.index', ['filter' => 'kgb_due']),
            'data_count' => $count,
        ];
    }
}