<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\TeacherSlot;
use App\Models\CallSession;
use App\Models\SlotBooking;
use App\Services\AgoraService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherBookingController extends Controller
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }

    public function getSoonestBooking()
    {
        try {
            $user = auth()->user();
            $teacher = $user->teacher;

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'ملف المعلم غير موجود.'], 404);
            }

            $now = Carbon::now();

            $soonestSlot = TeacherSlot::with(['booking.user'])
                ->where('teacher_id', $teacher->id)
                ->where('is_booked', true)
                // المواعيد التي لم تنتهِ بعد (تاريخ اليوم وقته أو تاريخ مستقبلي)
                ->where(function ($query) use ($now) {
                    $query->where('date', '>', $now->toDateString())
                          ->orWhere(function ($q) use ($now) {
                              $q->where('date', $now->toDateString())
                                ->where('end_time', '>', $now->toTimeString());
                          });
                })
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->first();

            if (!$soonestSlot) {
                return response()->json([
                    'status' => true,
                    'message' => 'لا توجد مواعيد محجوزة قريباً.',
                    'data' => null
                ]);
            }

            return response()->json([
                'status' => true,
                'data' => $this->formatSlotData($soonestSlot, $teacher, $now)
            ]);

        } catch (\Throwable $e) {
            Log::error('Soonest Booking Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ في جلب أقرب موعد.'], 500);
        }
    }

    /**
     * 2. عرض جميع الحجوزات مع التقييمات للمنتهي منها
     */
    public function getTeacherBookings(Request $request)
    {
        try {
            $teacher = auth()->user()->teacher;

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'حساب المعلم غير موجود.'], 404);
            }

            $slots = TeacherSlot::with(['booking.user'])
                ->where('teacher_id', $teacher->id)
                ->where('is_booked', true)
                ->orderBy('date', 'desc') // عرض الأحدث أولاً في القائمة الكاملة
                ->paginate($request->get('per_page', 15));

            $now = Carbon::now();

            $slots->getCollection()->transform(function ($slot) use ($teacher, $now) {
                return $this->formatSlotData($slot, $teacher, $now);
            });

            return response()->json([
                'status' => true,
                'message' => 'تم استرجاع الحجوزات بنجاح.',
                'data' => $slots
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Get Teacher Bookings Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب المواعيد.'], 500);
        }
    }

    /**
     * دالة مساعدة لتوحيد تنسيق بيانات الـ Slot واحتساب الحالات والتقييمات
     */
    private function formatSlotData($slot, $teacher, $now)
    {
        $student = optional($slot->booking)->user;
        $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
        $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

        // جلب جلسة المكالمة مع التقييمات المرتبطة بها (Polymorphic Relation)
        $callSession = CallSession::where('teacher_id', $teacher->id)
            ->where('student_id', optional($student)->id)
            ->where('started_at', $slotStartDateTime)
            ->with('ratings')
            ->first();

        $rating = optional($callSession)->ratings ? $callSession->ratings->first() : null;

        // حالة السماح ببدء المكالمة
        $canStart = $now->copy()->addMinutes(5)->greaterThanOrEqualTo($slotStartDateTime)
            && $now->lessThanOrEqualTo($slotEndDateTime)
            && optional($callSession)->status !== 'ended';

        return [
            'slot_id'         => $slot->id,
            'date'            => $slot->date,
            'start_time'      => $slot->start_time,
            'end_time'        => $slot->end_time,
            'booking_status'  => optional($slot->booking)->status, // scheduled, completed, cancelled
            'student' => [
                'id'    => optional($student)->id,
                'name'  => optional($student)->name,
                'photo' => optional($student)->profile_photo_url, // إذا وجد
            ],
            'call_session' => [
                'id'             => optional($callSession)->id,
                'status'         => optional($callSession)->status ?? 'not_started',
                'channel_name'   => optional($callSession)->channel_name,
                'can_start_now'  => $canStart,
                'duration'       => optional($callSession)->duration_minutes,
            ],
            // إرجاع التقييم إذا كانت الجلسة منتهية ويوجد تقييم
            'rating' => $rating ? [
                'stars'   => $rating->rating,
                'comment' => $rating->comment,
                'date'    => $rating->created_at->format('Y-m-d')
            ] : null
        ];
    }

    /**
     * 3. بدء الجلسة وتوليد التوكن
     */
    public function startBookedSession(Request $request)
    {
        $request->validate(['call_session_id' => 'required|exists:call_sessions,id']);
        $user = auth()->user();

        try {
            $call = CallSession::with('teacher')->findOrFail($request->call_session_id);

            if ($call->teacher->user_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك.'], 403);
            }

            if ($call->status === 'ended') {
                return response()->json(['status' => false, 'message' => 'هذه المكالمة انتهت بالفعل.'], 400);
            }

            // التحقق من الوقت (قبل 5 دقائق كحد أقصى)
            if (Carbon::now()->addMinutes(5)->lessThan(Carbon::parse($call->started_at))) {
                return response()->json(['status' => false, 'message' => 'لا يمكنك بدء الجلسة الآن، انتظر حتى يحين الموعد.'], 400);
            }

            $call->update(['status' => 'ongoing']);
            $token = $this->agoraService->generateToken($call->channel_name, $user->id, 'publisher');

            return response()->json([
                'status' => true,
                'data' => [
                    'token' => $token,
                    'channel' => $call->channel_name,
                    'uid' => $user->id
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'خطأ في بدء الجلسة.'], 500);
        }
    }

    /**
     * 4. إنهاء الجلسة وتحويل الدقائق للمحفظة
     */
    public function endBookedSession(Request $request)
    {
        $request->validate(['call_session_id' => 'required|exists:call_sessions,id']);
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $call = CallSession::with('teacher')->lockForUpdate()->findOrFail($request->call_session_id);

            if ($call->teacher->user_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك.'], 403);
            }

            if ($call->status === 'ended') {
                return response()->json(['status' => true, 'message' => 'منتهية مسبقاً.']);
            }

            $now = Carbon::now();
            $durationMinutes = (int) ceil(Carbon::parse($call->started_at)->diffInMinutes($now));

            $call->update([
                'ended_at' => $now,
                'duration_minutes' => $durationMinutes,
                'status' => 'ended'
            ]);

            // البحث عن الـ Slot والـ Booking المرتبطين لإنهاء الدورة المالية
            $slot = TeacherSlot::where('teacher_id', $call->teacher_id)
                ->where('date', Carbon::parse($call->started_at)->toDateString())
                ->where('start_time', Carbon::parse($call->started_at)->toTimeString())
                ->first();

            if ($slot) {
                $booking = SlotBooking::where('teacher_slot_id', $slot->id)->first();
                if ($booking && $booking->status !== 'completed') {
                    $booking->update(['status' => 'completed']);
                    // إضافة الدقائق المحجوزة مسبقاً لرصيد المعلم
                    $call->teacher->increment('minutes', $booking->deducted_minutes);
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'تم إنهاء الجلسة وإضافة الرصيد محفظتك.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('End Session Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'فشل إنهاء الجلسة.'], 500);
        }
    }
}
