<?php

namespace App\Notifications;

use App\Models\Logbook;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LogbookVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Logbook $logbook,
        protected User $verifikator,
        protected string $statusDecision
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $statusLabel = match ($this->statusDecision) {
            'disetujui'    => 'Disetujui ✅',
            'perlu_revisi' => 'Perlu Revisi ⚠️',
            'ditolak'      => 'Ditolak ❌',
            default        => ucfirst($this->statusDecision),
        };

        $tgl = Carbon::parse($this->logbook->tanggal)->locale('id')->isoFormat('D MMMM Y');
        $msg = "Aktivitas logbook Anda tanggal {$tgl} telah {$statusLabel} oleh {$this->verifikator->name}.";
        if ($this->logbook->catatan_atasan) {
            $msg .= " Catatan: \"{$this->logbook->catatan_atasan}\"";
        }

        return [
            'title'      => "Status Logbook: {$statusLabel}",
            'message'    => $msg,
            'type'       => 'logbook_verified',
            'icon'       => 'clipboard-document-check',
            'url'        => route('logbook.show', $this->logbook->id),
            'data_count' => 1,
        ];
    }
}
