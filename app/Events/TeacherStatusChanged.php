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

    /**
     * تمرير بيانات المعلم للحدث
     */
    public function __construct($teacherId, $isOnline)
    {
        $this->teacherId = $teacherId;
        $this->isOnline = $isOnline;
    }

    /**
     * تحديد القناة التي سيتم البث عليها (قناة عامة لأن كل الطلاب يحتاجون رؤيتها)
     */
    public function broadcastOn()
    {
        return new Channel('teachers-status');
    }

    /**
     * اسم الحدث الذي سيستمع له تطبيق الفلاتر
     */
    public function broadcastAs()
    {
        return 'status.changed';
    }
}
