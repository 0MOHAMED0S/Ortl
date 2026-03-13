<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemBroadcastNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        // نحن نحفظ في الداتابيز فقط هنا، الإرسال اللحظي يتولاه الـ Event
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type'    => $this->data['type'],
            'title'   => $this->data['title'],
            'message' => $this->data['message'],
        ];
    }
}
