<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherCancelledSlot implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $studentId;
    public $cancelData;
    public function __construct($studentId, $cancelData)
    {
        $this->studentId = $studentId;
        $this->cancelData = $cancelData;
    }
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('student.' . $this->studentId),
        ];
    }
    public function broadcastAs()
    {
        return 'TeacherCancelledSlot';
    }
    public function broadcastWith()
    {
        return [
            'cancellation' => $this->cancelData
        ];
    }
}
