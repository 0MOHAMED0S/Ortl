<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // لسرعة الإرسال الفورية
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingPrivateCall implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teacherId;
    public $callData;

    public function __construct($teacherId, $callData)
    {
        $this->teacherId = $teacherId;
        $this->callData = $callData;
    }

    public function broadcastOn()
    {
        // القناة الخاصة بالمعلم
        return new Channel('teacher.' . $this->teacherId);
    }

    public function broadcastAs()
    {
        return 'incoming.call';
    }

    public function broadcastWith()
    {
        return $this->callData;
    }
}
