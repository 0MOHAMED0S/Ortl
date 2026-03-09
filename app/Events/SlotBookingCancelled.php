<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlotBookingCancelled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teacherId;
    public $cancelData;

    public function __construct($teacherId, $cancelData)
    {
        $this->teacherId = $teacherId;
        $this->cancelData = $cancelData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('teacher.' . $this->teacherId),
        ];
    }

    public function broadcastAs()
    {
        return 'SlotBookingCancelled';
    }

    public function broadcastWith()
    {
        return [
            'cancellation' => $this->cancelData
        ];
    }
}
