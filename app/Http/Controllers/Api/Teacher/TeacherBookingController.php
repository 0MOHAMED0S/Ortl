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

    /**
     * ==========================================
     * 1. عرض جميع المواعيد المحجوزة للمعلم
     * ==========================================
     */
    public function getTeacherBookings(Request $request)
    {
        try {
            $user = auth()->user();

            // جلب ملف المعلم المرتبط بالمستخدم
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'حساب المعلم غير موجود.'], 404);
            }

            // جلب المواعيد المحجوزة فقط وترتيبها من الأقرب للأبعد
            $slots = TeacherSlot::with(['booking.user']) // استدعاء الحجز وبيانات الطالب
                ->where('teacher_id', $teacher->id)
                ->where('is_booked', true)
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->paginate($request->get('per_page', 15));

            $now = Carbon::now();

            // تنسيق البيانات لتكون سهلة لتطبيق الـ Flutter
            $slots->getCollection()->transform(function ($slot) use ($teacher, $now) {
                $student = optional($slot->booking)->user;
                $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
                $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

                // جلب جلسة المكالمة المرتبطة بهذا الموعد
                $callSession = CallSession::where('teacher_id', $teacher->id)
                    ->where('student_id', optional($student)->id)
                    ->where('started_at', $slotStartDateTime)
                    ->first();

                // هل يمكن للمعلم بدء المكالمة الآن؟ (نسمح بالدخول قبل الموعد بـ 5 دقائق)
                $canStart = $now->copy()->addMinutes(5)->greaterThanOrEqualTo($slotStartDateTime)
                    && $now->lessThanOrEqualTo($slotEndDateTime)
                    && optional($callSession)->status !== 'ended';

                return [
                    'slot_id'         => $slot->id,
                    'date'            => $slot->date,
                    'start_time'      => $slot->start_time,
                    'end_time'        => $slot->end_time,
                    'booking_status'  => optional($slot->booking)->status,
                    'student' => [
                        'id'    => optional($student)->id,
                        'name'  => optional($student)->name,
                        'email' => optional($student)->email,
                    ],
                    'call_session_id' => optional($callSession)->id,
                    'channel_name'    => optional($callSession)->channel_name,
                    'session_status'  => optional($callSession)->status,
                    'can_start_now'   => $canStart, // True إذا كان الوقت قد حان
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع الحجوزات بنجاح.',
                'data'    => $slots
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Teacher Bookings Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء جلب المواعيد.'], 500);
        }
    }

    /**
     * ==========================================
     * 2. المعلم يبدأ جلسة الموعد المحجوز
     * ==========================================
     */
/**
     * ==========================================
     * 2. المعلم يبدأ جلسة الموعد المحجوز (فقط)
     * ==========================================
     */
    public function startBookedSession(Request $request)
    {
        $request->validate([
            'call_session_id' => 'required|exists:call_sessions,id'
        ]);

        $user = auth()->user();

        try {
            $call = CallSession::with('teacher')->findOrFail($request->call_session_id);

            // 1️⃣ التأكد أن المعلم الحالي هو صاحب الجلسة
            if ($call->teacher->user_id !== $user->id) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك ببدء هذه المكالمة.'], 403);
            }

            // 2️⃣ الحماية: التأكد الصارم أن هذه الجلسة تخص "موعد محجوز" فقط
            // (بناءً على اسم القناة الذي حددناه وقت الحجز)
            if (!str_contains($call->channel_name, 'scheduled_call')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'عذراً، هذه الدالة مخصصة للمواعيد المحجوزة مسبقاً فقط.'
                ], 400);
            }

            // 3️⃣ التأكد أن المكالمة لم تنتهِ
            if ($call->status === 'ended') {
                return response()->json(['status' => false, 'message' => 'هذه المكالمة انتهت مسبقاً.'], 400);
            }

            // 4️⃣ التأكد من الوقت (لا يمكنه بدء مكالمة مبكراً جداً!)
            $now = Carbon::now();
            $startTime = Carbon::parse($call->started_at);

            if ($now->copy()->addMinutes(5)->lessThan($startTime)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكنك بدء الجلسة الآن. يُسمح بالدخول قبل الموعد بـ 5 دقائق كحد أقصى.'
                ], 400);
            }

            // 5️⃣ تحديث حالة المكالمة إلى (جارية)
            $call->update(['status' => 'ongoing']);

            // 6️⃣ توليد توكن Agora للمعلم
            $token = $this->agoraService->generateToken($call->channel_name, $user->id, 'publisher');

            // ==========================================
            // 🚀 (اختياري) إرسال إشعار Pusher للطالب بأن المعلم في الغرفة
            // ==========================================
            /*
            broadcast(new \App\Events\TeacherStartedSession($call->student_id, [
                'call_session_id' => $call->id,
                'channel_name'    => $call->channel_name,
                'teacher_name'    => $user->name,
                'message'         => 'المعلم متواجد الآن في الغرفة، انضم للمكالمة.'
            ]));
            */

            return response()->json([
                'status'  => true,
                'message' => 'تم فتح الغرفة بنجاح. في انتظار الطالب.',
                'data'    => [
                    'call_session_id' => $call->id,
                    'channel_name'    => $call->channel_name,
                    'agora_token'     => $token,
                    'uid'             => $user->id,
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Start Booked Session Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء محاولة بدء المكالمة.'
            ], 500);
        }
    }

    /**
     * ==========================================
     * 🏁 3. إنهاء الجلسة المحجوزة (من قِبل المعلم)
     * ==========================================
     */
    public function endBookedSession(Request $request)
    {
        $request->validate([
            'call_session_id' => 'required|exists:call_sessions,id'
        ]);

        $user = auth()->user();

        DB::beginTransaction();
        try {
            // جلب المكالمة مع قفل الصف لمنع التعديل المزدوج
            $call = CallSession::with('teacher')->lockForUpdate()->findOrFail($request->call_session_id);

            // 1️⃣ التأكد أن المعلم الحالي هو صاحب الجلسة
            if ($call->teacher->user_id !== $user->id) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'غير مصرح لك بإنهاء هذه المكالمة.'], 403);
            }

            // 2️⃣ التأكد الصارم أن هذه الجلسة تخص "موعد مجدول" فقط
            if (!str_contains($call->channel_name, 'scheduled_call')) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => 'هذه الدالة مخصصة لإنهاء المواعيد المحجوزة مسبقاً فقط.'
                ], 400);
            }

            // 3️⃣ إذا كانت منتهية مسبقاً، لا داعي لتكرار العملية
            if ($call->status === 'ended') {
                DB::rollBack();
                return response()->json(['status' => true, 'message' => 'المكالمة منتهية مسبقاً.']);
            }

            // حساب الوقت الفعلي الذي استغرقته الجلسة (لأغراض الإحصائيات فقط)
            $now = Carbon::now();
            $durationSeconds = Carbon::parse($call->started_at)->diffInSeconds($now);
            $durationMinutes = (int) ceil($durationSeconds / 60);

            // 4️⃣ تحديث حالة الجلسة
            $call->update([
                'ended_at'         => $now,
                'duration_minutes' => $durationMinutes,
                'status'           => 'ended'
            ]);

            // 5️⃣ البحث عن الموعد الأصلي (Slot) باستخدام وقت بداية الجلسة
            $callStart = Carbon::parse($call->started_at);

            $slot = TeacherSlot::where('teacher_id', $call->teacher_id)
                ->where('date', $callStart->toDateString())
                ->where('start_time', $callStart->toTimeString())
                ->first();

            if ($slot) {
                // 6️⃣ البحث عن سجل الحجز (Pivot) لتحويل الدقائق
                $booking = SlotBooking::where('teacher_slot_id', $slot->id)
                    ->where('status', 'scheduled')
                    ->first();

                if ($booking) {
                    // تحويل الحجز إلى مكتمل
                    $booking->update(['status' => 'completed']);

                    // 💰 تحويل الرصيد (الدقائق المخصومة مسبقاً) إلى محفظة المعلم
                    $call->teacher->increment('minutes', $booking->deducted_minutes);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم إنهاء الجلسة بنجاح، وتمت إضافة الدقائق لمحفظتك.',
                'data'    => [
                    'actual_duration_minutes' => $durationMinutes
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('End Booked Session Error (Teacher): ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
}
