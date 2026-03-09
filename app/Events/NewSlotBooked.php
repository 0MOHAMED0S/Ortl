<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSlotBooked implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teacherId;
    public $bookingData;

    public function __construct($teacherId, $bookingData)
    {
        $this->teacherId = $teacherId;
        $this->bookingData = $bookingData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('teacher.' . $this->teacherId),
        ];
    }

    public function broadcastAs()
    {
        return 'NewSlotBooked';
    }

    public function broadcastWith()
    {
        return [
            'booking' => $this->bookingData
        ];
    }
}
