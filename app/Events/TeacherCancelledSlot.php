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

    /**
     * Create a new event instance.
     */
    public function __construct($studentId, $cancelData)
    {
        $this->studentId = $studentId;
        $this->cancelData = $cancelData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // بث الإشعار على القناة الخاصة بالطالب
        return [
            new PrivateChannel('student.' . $this->studentId),
        ];
    }

    /**
     * اسم الحدث الذي سيستمع له الموبايل
     */
    public function broadcastAs()
    {
        return 'TeacherCancelledSlot';
    }

    /**
     * البيانات المرسلة مع الحدث
     */
    public function broadcastWith()
    {
        return [
            'cancellation' => $this->cancelData
        ];
    }
}
