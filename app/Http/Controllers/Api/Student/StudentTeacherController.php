<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Teacher_application;
use App\Models\TeacherSlot;
use App\Models\UserPackage;
use App\Models\CallSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\SlotBooking;
class StudentTeacherController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1️⃣ تحديد عدد المعلمين في كل صفحة (افتراضياً 10)
            $perPage = $request->get('per_page', 10);

            // 2️⃣ جلب المعلمين الموافق عليهم مع التصفح
            $teachersPaginator = Teacher_application::where('status', 'approved')
                ->with(['profile.user'])
                ->latest() // ترتيب الأحدث أولاً
                ->paginate($perPage);

            // 3️⃣ تنسيق البيانات داخل الـ Collection الخاص بالتصفح
            $teachersPaginator->getCollection()->transform(function ($teacher) {

                // الاسم: اسم المستخدم أو اسم التطبيق
                $name = optional(optional($teacher->profile)->user)->name ?? $teacher->full_name;

                // الصورة الشخصية
                $photoPath = optional($teacher->profile)->profile_photo_path ?? null;
                $photoUrl = $photoPath
                    ? asset('storage/' . $photoPath)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=128';

                return [
                    'id'               => $teacher->id,
                    'name'             => $name,
                    'photo_url'        => $photoUrl,
                    'is_online'        => (bool) optional($teacher->profile)->is_online,
                    'qualification'    => $teacher->qualification,
                    'country'          => $teacher->origin_country,
                    'languages'        => $teacher->languages,
                    'specialties'      => $teacher->specialties,
                    'experience_years' => $teacher->experience_years,
                    'about'            => $teacher->ijazas_text,
                ];
            });

            // 4️⃣ إرجاع الاستجابة مع بيانات التصفح كاملة
            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع المعلمين بنجاح.',
                'data'    => [
                    'teachers' => $teachersPaginator->items(), // المصفوفة المنسقة
                    'pagination' => [
                        'total'         => $teachersPaginator->total(),
                        'count'         => $teachersPaginator->count(),
                        'per_page'      => (int) $teachersPaginator->perPage(),
                        'current_page'  => $teachersPaginator->currentPage(),
                        'total_pages'   => $teachersPaginator->lastPage(),
                        'next_page_url' => $teachersPaginator->nextPageUrl(),
                        'prev_page_url' => $teachersPaginator->previousPageUrl(),
                    ]
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('فشل في جلب المعلمين مع التصفح', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء استرجاع المعلمين. حاول مرة أخرى لاحقًا.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // 1️⃣ جلب المعلم الموافق عليه مع حسابه وتطبيقه ومساراته
            $teacher = Teacher::with(['user', 'application.tracks'])->find($id);

            if (!$teacher) {
                return response()->json([
                    'status'  => false,
                    'message' => 'المعلم غير موجود.'
                ], 404);
            }

            // 2️⃣ تنسيق البيانات
            $name = $teacher->user->name ?? optional($teacher->application)->full_name;

            $photoUrl = $teacher->profile_photo_path
                ? asset('storage/' . $teacher->profile_photo_path)
                : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=200';

            $profile = [
                'id'               => $teacher->id,
                'user_id'          => $teacher->user_id,
                'name'             => $name,
                'photo_url'        => $photoUrl,
                'is_online'        => (bool) $teacher->is_online,
                'qualification'    => optional($teacher->application)->qualification,
                'country'          => optional($teacher->application)->origin_country,
                'languages'        => optional($teacher->application)->languages,
                'experience_years' => optional($teacher->application)->experience_years,
                'about'            => optional($teacher->application)->ijazas_text,
                'minutes_balance'  => $teacher->minutes,
                'specialties'      => optional(optional($teacher->application)->tracks)->map(function ($track) {
                    return [
                        'id'   => $track->id,
                        'name' => $track->name,
                    ];
                }) ?? [],
            ];

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع ملف المعلم بنجاح.',
                'data'    => $profile
            ], 200);
        } catch (\Throwable $e) {
            Log::error("حدث خطأ أثناء جلب ملف المعلم ($id): " . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء جلب الملف الشخصي للمعلم.'
            ], 500);
        }
    }

    public function getTeacherAvailableSlots(Request $request, $teacherId)
    {
        try {
            // 1️⃣ التأكد من وجود المعلم
            $teacherExists = Teacher::where('id', $teacherId)->exists();

            if (!$teacherExists) {
                return response()->json([
                    'status' => false,
                    'message' => 'عذراً، المعلم المطلوب غير موجود.'
                ], 404);
            }

            // 2️⃣ بناء الاستعلام للمواعيد المتاحة (في المستقبل فقط)
            $query = TeacherSlot::where('teacher_id', $teacherId)
                ->where('is_booked', false)
                ->where(function ($query) {
                    $query->where('date', '>', now()->toDateString())
                        ->orWhere(function ($q) {
                            $q->where('date', now()->toDateString())
                                ->where('start_time', '>', now()->format('H:i:s'));
                        });
                })
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc');

            // 3️⃣ تطبيق التصفح
            $perPage = $request->get('per_page', 20);
            $paginator = $query->paginate($perPage);

            // 4️⃣ تحويل السجلات الحالية لمجموعة وتجميعها حسب التاريخ
            $groupedSlots = $paginator->getCollection()->groupBy('date');

            if ($paginator->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'لا توجد مواعيد متاحة حالياً.',
                    'data' => [
                        'calendar' => [],
                    ],
                    'pagination' => null
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'تم جلب المواعيد بنجاح.',
                'data' => [
                    'calendar' => $groupedSlots,
                    'pagination' => [
                        'total'        => $paginator->total(),
                        'count'        => $paginator->count(),
                        'per_page'     => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'total_pages'  => $paginator->lastPage(),
                        'next_page'    => $paginator->nextPageUrl(),
                        'prev_page'    => $paginator->previousPageUrl(),
                    ]
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Available Slots Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب المواعيد.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * ==========================================
     * 🚀 دالة حجز موعد مع المعلم
     * ==========================================
     */


    public function bookSlot(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|exists:teacher_slots,id'
        ]);

        $user = auth()->user();

        DB::beginTransaction();
        try {
            // 1️⃣ جلب الموعد مع قفل الصف لمنع الحجز المزدوج
            $slot = TeacherSlot::where('id', $request->slot_id)
                ->lockForUpdate()
                ->first();

            // التحقق مما إذا كان محجوزاً
            if ($slot->is_booked) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'عذراً، هذا الموعد تم حجزه للتو بواسطة طالب آخر.'], 400);
            }

            // التحقق من وقت الموعد
            $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
            $slotEndDateTime = Carbon::parse($slot->date . ' ' . $slot->end_time);

            if ($slotStartDateTime->isPast()) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'لا يمكنك حجز موعد في الماضي.'], 400);
            }

            // 2️⃣ حساب مدة الموعد بالدقائق
            $slotDurationMinutes = $slotStartDateTime->diffInMinutes($slotEndDateTime);

            // 3️⃣ التحقق من رصيد الطالب وجلب الباقات مع قفلها مالياً
            $activePackages = UserPackage::where('user_id', $user->id)
                ->whereIn('status', ['active', 'Active'])
                ->where('remaining_minutes', '>', 0)
                ->where(function ($q) {
                    $q->where('expires_at', '>', now())
                      ->orWhereNull('expires_at');
                })
                ->orderByRaw('expires_at IS NULL, expires_at ASC') // الباقات الأقرب انتهاءً أولاً
                ->lockForUpdate() // قفل مالي
                ->get();

            $totalAvailableMinutes = $activePackages->sum('remaining_minutes');

            // هل يمتلك دقائق تكفي لتغطية مدة الموعد بالكامل؟
            if ($totalAvailableMinutes < $slotDurationMinutes) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => "رصيدك غير كافٍ. الموعد يتطلب {$slotDurationMinutes} دقيقة، ورصيدك الحالي {$totalAvailableMinutes} دقيقة."
                ], 400);
            }

            // 4️⃣ خصم الدقائق من الباقات تدريجياً (الدفع المسبق)
            $minutesLeftToDeduct = $slotDurationMinutes;

            foreach ($activePackages as $package) {
                if ($minutesLeftToDeduct <= 0) break;

                $deductFromThisPackage = min($package->remaining_minutes, $minutesLeftToDeduct);

                $package->remaining_minutes -= $deductFromThisPackage;
                $minutesLeftToDeduct -= $deductFromThisPackage;

                if ($package->remaining_minutes <= 0) {
                    $package->remaining_minutes = 0;
                    $package->status = 'expired';
                }

                $package->save();
            }

            // 5️⃣ تحديث الموعد الأساسي
            $slot->update(['is_booked' => true]);

            // 6️⃣ تسجيل الحجز في جدول الـ Pivot
            $booking = SlotBooking::create([
                'user_id'          => $user->id,
                'teacher_slot_id'  => $slot->id,
                'deducted_minutes' => $slotDurationMinutes, // حفظ ما تم خصمه
                'status'           => 'scheduled'
            ]);

            // 7️⃣ إنشاء جلسة المكالمة المجدولة لكي تظهر في التطبيق
            $channelName = 'scheduled_call_' . $user->id . '_' . $slot->teacher_id . '_' . time();
           $callSession = CallSession::create([
                'student_id'   => $user->id,
                'teacher_id'   => $slot->teacher_id,
                'channel_name' => $channelName,
                // 👇 تم تغيير الكلمة هنا لكي تقبلها قاعدة البيانات
                'status'       => 'initiated',
                'started_at'   => $slotStartDateTime,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "تم حجز الموعد بنجاح وخصم {$slotDurationMinutes} دقيقة من رصيدك.",
                'data'    => [
                    'slot'         => $slot,
                    'booking'      => $booking,
                    'call_session' => $callSession
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Book Slot Error: ' . $e->getMessage());

            // 🚀 التعديل هنا: كشف الخطأ الحقيقي للمطور
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ], 500);
        }
    }


}
