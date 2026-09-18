<?php

namespace App\Notifications;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CutiSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected PengajuanCuti $cuti,
        protected Pegawai $pegawai
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title'      => 'Permohonan Cuti Baru',
            'message'    => "{$this->pegawai->nama} telah mengajukan permohonan {$this->cuti->jenis_cuti} selama {$this->cuti->jumlah_hari} hari kerja.",
            'type'       => 'cuti_submitted',
            'icon'       => 'calendar-days',
            'url'        => route('pengajuan-cuti.show', $this->cuti->id),
            'data_count' => 1,
        ];
    }
}
