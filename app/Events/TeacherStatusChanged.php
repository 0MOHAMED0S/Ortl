<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teacherId;
    public $isOnline;

    public function __construct($teacherId, $isOnline)
    {
        $this->teacherId = $teacherId;
        $this->isOnline = $isOnline;
    }

    public function broadcastOn()
    {
        return new Channel('teachers-status');
    }

    public function broadcastAs()
    {
        return 'status.changed';
    }
}
