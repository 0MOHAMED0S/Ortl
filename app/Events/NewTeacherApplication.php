<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTeacherApplication implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $adminId;
    public $applicationData;

    /**
     * Create a new event instance.
     */
    public function __construct($adminId, $applicationData)
    {
        $this->adminId = $adminId;
        $this->applicationData = $applicationData;
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
     * اسم الحدث الذي سيستمع له الفرونت إند في لوحة التحكم
     */
    public function broadcastAs()
    {
        return 'NewTeacherApplication';
    }

    /**
     * البيانات المرسلة مع الحدث
     */
    public function broadcastWith()
    {
        return [
            'application' => $this->applicationData
        ];
    }
}
