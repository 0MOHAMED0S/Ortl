<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalCancelled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $adminId;
    public $cancelData;

    public function __construct($adminId, $cancelData)
    {
        $this->adminId = $adminId;
        $this->cancelData = $cancelData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.' . $this->adminId),
        ];
    }

    public function broadcastAs()
    {
        return 'WithdrawalCancelled';
    }

    public function broadcastWith()
    {
        return [
            'cancellation' => $this->cancelData
        ];
    }
}
