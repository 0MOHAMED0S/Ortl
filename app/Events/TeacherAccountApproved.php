<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherAccountApproved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teacherUserId;
    public $approvalData;

    /**
     * Create a new event instance.
     */
    public function __construct($teacherUserId, $approvalData)
    {
        $this->teacherUserId = $teacherUserId;
        $this->approvalData = $approvalData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // نبث على قناة اليوزر (المعلم)
        return [
            new PrivateChannel('teacher.' . $this->teacherUserId),
        ];
    }

    /**
     * اسم الحدث
     */
    public function broadcastAs()
    {
        return 'AccountApproved';
    }

    /**
     * البيانات المرسلة
     */
    public function broadcastWith()
    {
        return [
            'approval_details' => $this->approvalData
        ];
    }
}
