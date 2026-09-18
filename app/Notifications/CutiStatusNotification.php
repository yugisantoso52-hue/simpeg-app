<?php

namespace App\Notifications;

use App\Models\PengajuanCuti;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CutiStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected PengajuanCuti $cuti,
        protected User $verifikator,
        protected string $status
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $statusLabel = $this->status === 'Disetujui' ? 'Disetujui ✅' : 'Ditolak ❌';
        $msg = "Permohonan {$this->cuti->jenis_cuti} Anda telah {$statusLabel} oleh {$this->verifikator->name}.";
        if ($this->cuti->catatan_pimpinan) {
            $msg .= " Catatan: \"{$this->cuti->catatan_pimpinan}\"";
        }

        return [
            'title'      => "Status Cuti: {$statusLabel}",
            'message'    => $msg,
            'type'       => 'cuti_status',
            'icon'       => 'calendar-days',
            'url'        => route('pengajuan-cuti.show', $this->cuti->id),
            'data_count' => 1,
        ];
    }
}
