<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherStartedSession implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $studentId;
    public $callData;

    public function __construct($studentId, $callData)
    {
        $this->studentId = $studentId;
        $this->callData = $callData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('student.' . $this->studentId),
        ];
    }

    public function broadcastAs()
    {
        return 'TeacherStartedSession';
    }

    public function broadcastWith()
    {
        return [
            'call_details' => $this->callData
        ];
    }
}
