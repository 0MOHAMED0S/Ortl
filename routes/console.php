<?php

use App\Models\RecitationSession;
use App\Models\Session_student;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::command('bookings:check-missed')->everyTenMinutes()->withoutOverlapping();
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('packages:check-expired')->dailyAt('00:00');

Schedule::call(function () {
    try {
        DB::transaction(function () {
            $now = now();
            $bufferTime = $now->subMinutes(30);

            // 1. جلب معرفات الحصص التي يجب إغلاقها (Live أو Scheduled وانتهى وقتها)
            $expiredIds = RecitationSession::whereIn('status', ['live', 'scheduled'])
                ->where('end_at', '<', $bufferTime)
                ->pluck('id');

            if ($expiredIds->isNotEmpty()) {
                // 2. تحديث الحصص دفعة واحدة (أسرع بكثير)
                RecitationSession::whereIn('id', $expiredIds)
                    ->update(['status' => 'ended']);

                // 3. إخراج الطلاب العالقين في تلك الحصص
                Session_student::whereIn('recitation_session_id', $expiredIds)
                    ->whereNull('left_at')
                    ->update(['left_at' => DB::raw('updated_at')]); // أو استخدم $now

                Log::info("Cron Job: Closed " . $expiredIds->count() . " expired sessions.");
            }
        });
    } catch (\Exception $e) {
        Log::error("Cron Job Error (Expired Sessions): " . $e->getMessage());
    }
})->everyFifteenMinutes();

