<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewStudentRegistered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $adminId;
    public $studentData;

    /**
     * Create a new event instance.
     */
    public function __construct($adminId, $studentData)
    {
        $this->adminId = $adminId;
        $this->studentData = $studentData;
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
     * اسم الحدث الذي سيستمع له الفرونت إند (لوحة التحكم)
     */
    public function broadcastAs()
    {
        return 'NewStudent';
    }

    /**
     * البيانات المرسلة مع الحدث
     */
    public function broadcastWith()
    {
        return [
            'student' => $this->studentData
        ];
    }
}
