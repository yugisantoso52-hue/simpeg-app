<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SatyalancanaWorkflowNotification extends Notification
{
    use Queueable;

    protected $usulan;
    protected $action;
    protected $title;
    protected $message;

    public function __construct($usulan, string $action, string $title, string $message)
    {
        $this->usulan = $usulan;
        $this->action = $action;
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title'      => $this->title,
            'message'    => $this->message,
            'type'       => 'satyalancana_workflow',
            'action'     => $this->action,
            'usulan_id'  => $this->usulan->id,
            'pegawai_id' => $this->usulan->pegawai_id,
            'icon'       => 'award',
            'url'        => route('satyalancana.index'),
        ];
    }
}
