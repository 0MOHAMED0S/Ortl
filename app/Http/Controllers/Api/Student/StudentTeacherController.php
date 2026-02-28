<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Teacher_application;
use App\Models\TeacherSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                    // ✅ إضافة حالة الاتصال هنا
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
            $name = $teacher->user->name ?? $teacher->application->full_name;

            $photoUrl = $teacher->profile_photo_path
                ? asset('storage/' . $teacher->profile_photo_path)
                : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=200';

            $profile = [
                'id'               => $teacher->id,
                'user_id'          => $teacher->user_id,
                'name'             => $name,
                'photo_url'        => $photoUrl,
                // ✅ إضافة حالة الاتصال هنا
                'is_online'        => (bool) $teacher->is_online,
                'qualification'    => $teacher->application->qualification,
                'country'          => $teacher->application->origin_country,
                'languages'        => $teacher->application->languages,
                'experience_years' => $teacher->application->experience_years,
                'about'            => $teacher->application->ijazas_text,
                'minutes_balance'  => $teacher->minutes,
                'specialties'      => $teacher->application->tracks->map(function ($track) {
                    return [
                        'id'   => $track->id,
                        'name' => $track->name,
                    ];
                }),
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
            $teacherExists = \App\Models\Teacher::where('id', $teacherId)->exists();

            if (!$teacherExists) {
                return response()->json([
                    'status' => false,
                    'message' => 'عذراً، المعلم المطلوب غير موجود.'
                ], 404);
            }

            // 2️⃣ بناء الاستعلام للمواعيد المتاحة
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

            // 3️⃣ تطبيق التصفح (مثلاً 20 موعد في الصفحة)
            $perPage = $request->get('per_page', 20);
            $paginator = $query->paginate($perPage);

            // 4️⃣ تحويل السجلات الحالية لمجموعة (Collection) وتجميعها حسب التاريخ
            $groupedSlots = $paginator->getCollection()->groupBy('date');

            if ($paginator->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'لا توجد مواعيد متاحة حالياً.',
                    'data' => [],
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
}
