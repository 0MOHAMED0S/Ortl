<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRatingReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teacherId;
    public $ratingData;

    /**
     * Create a new event instance.
     */
    public function __construct($teacherId, $ratingData)
    {
        $this->teacherId = $teacherId;
        $this->ratingData = $ratingData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // نرسل الإشعار على القناة الخاصة بالمعلم
        return [
            new PrivateChannel('teacher.' . $this->teacherId),
        ];
    }

    /**
     * اسم الحدث الذي سيستمع له الموبايل (الفلاتر)
     */
    public function broadcastAs()
    {
        return 'NewRating';
    }

    /**
     * البيانات المرسلة مع الحدث
     */
    public function broadcastWith()
    {
        return [
            'rating' => $this->ratingData
        ];
    }
}
