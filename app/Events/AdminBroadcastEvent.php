<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminBroadcastEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $target;
    public $notificationData;

    public function __construct($target, $notificationData)
    {
        $this->target = $target;
        $this->notificationData = $notificationData;
    }

    /**
     * تحديد القناة التي سيتم البث عليها بناءً على الهدف
     */
    public function broadcastOn()
    {
        if ($this->target === 'all') {
            return [
                new PrivateChannel('group.students'),
                new PrivateChannel('group.teachers')
            ];
        }

        return new PrivateChannel('group.' . $this->target);
    }

    /**
     * اسم الحدث الذي سيستمع له الموبايل
     */
    public function broadcastAs()
    {
        return 'AdminBroadcast';
    }

    /**
     * البيانات التي ستصل في الـ Payload
     */
    public function broadcastWith()
    {
        return [
            'title'   => $this->notificationData['title'],
            'message' => $this->notificationData['message'],
            'type'    => $this->notificationData['type'],
        ];
    }
}
