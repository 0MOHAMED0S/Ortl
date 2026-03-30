<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SlotBooking;
use App\Models\UserPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckMissedSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:check-missed';

    /**
     * The console command description.
     */
    protected $description = 'التحقق من الحصص الفائتة أو المعلقة وإعادة الدقائق للطلاب';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        // فترة السماح: 15 دقيقة بعد موعد انتهاء الحصة
        $gracePeriodMinutes = 15;

        // 1. جلب الحجوزات المعلقة (مجدولة أو مستمرة) ولم يتم إنهاؤها
        $missedBookings = SlotBooking::with('slot')
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->get();

        $processedCount = 0;

        foreach ($missedBookings as $booking) {
            $slot = $booking->slot;

            // تخطي إذا كان الموعد محذوفاً لسبب ما
            if (!$slot) continue;

            // حساب وقت انتهاء الحصة وإضافة فترة السماح
            $slotEndTime = Carbon::parse($slot->date . ' ' . $slot->end_time)->addMinutes($gracePeriodMinutes);

            // إذا كان الوقت الحالي قد تجاوز وقت انتهاء الحصة + 15 دقيقة
            if ($now->greaterThan($slotEndTime)) {

                DB::beginTransaction();
                try {
                    // أ. إعادة الدقائق المخصومة لرصيد الطالب
                    $refundMinutes = $booking->deducted_minutes;

                    if ($refundMinutes > 0) {
                        // جلب باقة نشطة أو أحدث باقة لاسترجاع الرصيد مع قفل السطر للحماية
                        $userPackage = UserPackage::where('user_id', $booking->user_id)
                            ->where('status', 'active')
                            ->orderBy('expires_at', 'desc')
                            ->lockForUpdate()
                            ->first()
                            ??
                            UserPackage::where('user_id', $booking->user_id)
                            ->latest()
                            ->lockForUpdate()
                            ->first();

                        if ($userPackage) {
                            $userPackage->increment('remaining_minutes', $refundMinutes);
                            $userPackage->update(['status' => 'active']);
                        }
                    }

                    // ب. تغيير حالة الحجز إلى (فائتة / missed)
                    $booking->update([
                        'status' => 'missed',
                        'ended_at' => $now // توثيق وقت إغلاق الحصة من قبل النظام
                    ]);

                    // ج. تحرير الموعد لكي لا يبقى معلقاً في جدول المعلم
                    $slot->update(['is_booked' => false]);

                    DB::commit();
                    $processedCount++;

                    Log::info("System Cron: Missed session processed. Booking ID {$booking->id}. Refunded {$refundMinutes} mins to Student {$booking->user_id}.");

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("System Cron Error (Booking ID {$booking->id}): " . $e->getMessage());
                }
            }
        }

        $this->info("تم الانتهاء من فحص الحجوزات. عدد الحصص الفائتة التي عولجت: {$processedCount}");
    }
}
