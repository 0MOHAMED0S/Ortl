<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentLeftSession implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $teacherId;
    public $data;

    public function __construct($teacherId, $data)
    {
        $this->teacherId = $teacherId;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('teacher.' . $this->teacherId);
    }

    public function broadcastAs()
    {
        return 'StudentLeft';
    }
}
