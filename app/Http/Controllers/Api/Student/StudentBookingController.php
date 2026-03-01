<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SlotBooking;
use App\Models\CallSession;
use App\Services\AgoraService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StudentBookingController extends Controller
{
    protected $agoraService;

    // حقن خدمة Agora لتوليد التوكن
    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }

    /**
     * ==========================================
     * 📅 1. جلب جميع المواعيد المحجوزة للطالب
     * ==========================================
     */
    public function getStudentBookings(Request $request)
    {
        try {
            $user = auth()->user();

            // جلب الحجوزات الخاصة بالطالب (أزلنا علاقة profile لأنها كانت تسبب خطأ)
            $bookings = SlotBooking::with(['slot.teacher.user'])
                ->where('slot_bookings.user_id', $user->id)
                ->where('slot_bookings.status', '!=', 'cancelled')
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->orderBy('teacher_slots.date', 'asc')
                ->orderBy('teacher_slots.start_time', 'asc')
                ->select('slot_bookings.*')
                ->paginate($request->get('per_page', 15));

            $now = Carbon::now();

            // استخدام map بدلاً من transform لكي نتمكن من الفلترة بشكل صحيح
            $transformedData = $bookings->getCollection()->map(function ($booking) use ($user, $now) {
                $slot = $booking->slot;

                // حماية من الأخطاء إذا كان الموعد أو المعلم محذوفاً
                if (!$slot || !$slot->teacher) {
                    return null;
                }

                $teacher = $slot->teacher;

                $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
                $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

                // جلب جلسة المكالمة المرتبطة
                $callSession = CallSession::where('student_id', $user->id)
                    ->where('teacher_id', $teacher->id)
                    ->where('started_at', $slotStartDateTime)
                    ->whereIn('status', ['initiated', 'scheduled', 'ongoing'])
                    ->first();

                // هل يمكن للطالب دخول المكالمة الآن؟
                $canStart = $now->copy()->addMinutes(5)->greaterThanOrEqualTo($slotStartDateTime)
                    && $now->lessThanOrEqualTo($slotEndDateTime)
                    && optional($callSession)->status !== 'ended';

                return [
                    'booking_id'       => $booking->id,
                    'slot_id'          => $slot->id,
                    'date'             => $slot->date,
                    'start_time'       => $slot->start_time,
                    'end_time'         => $slot->end_time,
                    'status'           => $booking->status,
                    'deducted_minutes' => $booking->deducted_minutes,
                    'teacher' => [
                        'id'    => $teacher->id,
                        'name'  => optional($teacher->user)->name ?? 'معلم غير محدد',
                        // الاعتماد على الحقل المباشر للصورة
                        'photo' => $teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null,
                    ],
                    'call_session_id'  => optional($callSession)->id,
                    'channel_name'     => optional($callSession)->channel_name,
                    'session_status'   => optional($callSession)->status,
                    'can_join_now'     => $canStart,
                ];
            })->filter()->values(); // فلترة الـ null وإعادة ترتيب الـ keys

            // إرجاع البيانات المنسقة إلى الـ Paginator
            $bookings->setCollection($transformedData);

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع الحجوزات بنجاح.',
                'data'    => $bookings
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Get Student Bookings Error: ' . $e->getMessage());

            // 🚀 التعديل الأهم: إرجاع رسالة الخطأ الحقيقية بدلاً من الرسالة العامة
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }

    /**
     * ==========================================
     * 🚀 2. انضمام الطالب لجلسة الموعد المحجوز
     * ==========================================
     */
    public function joinBookedSession(Request $request)
    {
        $request->validate([
            'call_session_id' => 'required|exists:call_sessions,id'
        ]);

        $user = auth()->user();

        try {
            $call = CallSession::with('teacher')->findOrFail($request->call_session_id);

            // 1️⃣ التأكد أن الطالب الحالي هو صاحب هذا الحجز
            if ($call->student_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك بالانضمام لهذه المكالمة.'], 403);
            }

            // 2️⃣ التأكد الصارم أن هذه الجلسة تخص "موعد مجدول" فقط
            if (!str_contains($call->channel_name, 'scheduled_call')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'عذراً، هذا المسار مخصص للمواعيد المحجوزة مسبقاً فقط.'
                ], 400);
            }

            // 3️⃣ التأكد أن المكالمة لم تنتهِ
            if ($call->status === 'ended') {
                return response()->json(['status' => false, 'message' => 'هذه المكالمة انتهت مسبقاً.'], 400);
            }

            // 4️⃣ التأكد من الوقت (منع الدخول المبكر جداً)
            $now = Carbon::now();
            $startTime = Carbon::parse($call->started_at);

            if ($now->copy()->addMinutes(5)->lessThan($startTime)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكنك الانضمام للجلسة الآن. يُسمح بالدخول قبل الموعد بـ 5 دقائق كحد أقصى.'
                ], 400);
            }

            // 5️⃣ تحديث حالة المكالمة إلى (جارية) إذا لم يتم تحديثها مسبقاً
            if (in_array($call->status, ['initiated', 'scheduled'])) {
                $call->update(['status' => 'ongoing']);
            }

            // 6️⃣ توليد توكن Agora للطالب (كمرسل/Publisher)
            $token = $this->agoraService->generateToken($call->channel_name, $user->id, 'publisher');

            return response()->json([
                'status'  => true,
                'message' => 'تم الانضمام للغرفة بنجاح.',
                'data'    => [
                    'call_session_id' => $call->id,
                    'channel_name'    => $call->channel_name,
                    'agora_token'     => $token,
                    'uid'             => $user->id,
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Join Booked Session Error (Student): ' . $e->getMessage());

            // 🚀 التعديل الأهم: إرجاع رسالة الخطأ الحقيقية
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
}
