<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderPaid implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $adminId;
    public $orderData;

    /**
     * Create a new event instance.
     */
    public function __construct($adminId, $orderData)
    {
        $this->adminId = $adminId;
        $this->orderData = $orderData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // بث الإشعار على القناة الخاصة بالمدير
        return [
            new PrivateChannel('admin.' . $this->adminId),
        ];
    }

    /**
     * اسم الحدث الذي سيستمع له الفرونت إند
     */
    public function broadcastAs()
    {
        return 'NewOrder';
    }

    /**
     * البيانات المرسلة مع الحدث
     */
    public function broadcastWith()
    {
        return [
            'order' => $this->orderData
        ];
    }
}
