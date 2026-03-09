<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentJoinedSession implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teacherId;
    public $callData;

    /**
     * Create a new event instance.
     */
    public function __construct($teacherId, $callData)
    {
        $this->teacherId = $teacherId;
        $this->callData = $callData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // بث الإشعار على القناة الخاصة بالمعلم
        return [
            new PrivateChannel('teacher.' . $this->teacherId),
        ];
    }

    /**
     * اسم الحدث الذي سيستمع له الموبايل
     */
    public function broadcastAs()
    {
        return 'StudentJoined';
    }

    /**
     * البيانات المرسلة مع الحدث
     */
    public function broadcastWith()
    {
        return [
            'call_details' => $this->callData
        ];
    }
}
