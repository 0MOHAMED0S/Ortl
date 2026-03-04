<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
// 🌟 استدعاء كلاسات النجاح والفشل
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Events\NotificationFailed;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

public function boot(): void
    {
        // 1️⃣ تسجيل أي نجاح في الإشعارات
        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Notifications\Events\NotificationSent $event) {
            \Illuminate\Support\Facades\Log::info('✅ الإشعار نجح!', [
                'channel' => $event->channel, // 👈 هنا سنرى اسم القناة التي نجحت
                'user'    => $event->notifiable->id ?? 'Unknown',
            ]);
        });

        // 2️⃣ تسجيل أي فشل
        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Notifications\Events\NotificationFailed $event) {
            \Illuminate\Support\Facades\Log::error('🚨 الإشعار فشل!', [
                'channel' => $event->channel,
                'error'   => $event->data,
            ]);
        });
    }
}
