<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $adminId;
    public $withdrawalData;

    public function __construct($adminId, $withdrawalData)
    {
        $this->adminId = $adminId;
        $this->withdrawalData = $withdrawalData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.' . $this->adminId),
        ];
    }

    public function broadcastAs()
    {
        return 'WithdrawalRequested';
    }

    public function broadcastWith()
    {
        return [
            'request' => $this->withdrawalData
        ];
    }
}
