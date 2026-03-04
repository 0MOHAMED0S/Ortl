<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class DynamicNotification extends Notification
{
    use Queueable;

    public $title;
    public $body;
    public $type;
    public $extraData;

    /**
     * نستقبل البيانات ديناميكياً لتمريرها في كل مكان
     */
    public function __construct(string $title, string $body, string $type, array $extraData = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->extraData = $extraData;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', OneSignalChannel::class];
    }

    /**
     * 1️⃣ الحفظ في الداتابيز
     */
    public function toDatabase($notifiable)
    {
        // ندمج البيانات الأساسية مع البيانات الإضافية في مصفوفة واحدة
        return array_merge([
            'title' => $this->title,
            'body'  => $this->body,
            'type'  => $this->type,
        ], $this->extraData);
    }

    /**
     * 2️⃣ الإرسال الفوري عبر Pusher
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage(array_merge([
            'title' => $this->title,
            'body'  => $this->body,
            'type'  => $this->type,
        ], $this->extraData));
    }

    /**
     * 3️⃣ الإرسال للموبايل عبر OneSignal
     */
    public function toOneSignal($notifiable)
    {
        $message = OneSignalMessage::create()
            ->setSubject($this->title)
            ->setBody($this->body)
            ->setData('type', $this->type);

        // حقن كل البيانات الإضافية داخل OneSignal لكي يقرأها الموبايل عند الضغط
        foreach ($this->extraData as $key => $value) {
            $message->setData($key, $value);
        }

        return $message;
    }
}
