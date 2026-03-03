<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    private function getTeacherStats($teacherId)
    {
        $stats = [
            'students_count' => 0,
            'calls_count'    => 0,
            'slots_count'    => 0,
            'sessions_count' => 0,
        ];

        if (!$teacherId) return $stats;

        try {
            // 1. حساب المكالمات
            $callStudents = DB::table('call_sessions')
                ->where('teacher_id', $teacherId)
                ->where('status', 'ended')
                ->pluck('student_id')
                ->toArray();

            $stats['calls_count'] = count($callStudents);

            // 2. حساب المواعيد
            $slotStudents = DB::table('slot_bookings')
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->where('teacher_slots.teacher_id', $teacherId)
                ->where('slot_bookings.status', '!=', 'cancelled')
                ->pluck('slot_bookings.user_id')
                ->toArray();

            $stats['slots_count'] = count($slotStudents);

            // 3. حساب الجلسات
            $sessionStudents = [];
            try {
                $sessionStudents = DB::table('sessions')
                    ->where('teacher_id', $teacherId)
                    ->pluck('student_id')
                    ->toArray();
                $stats['sessions_count'] = count($sessionStudents);
            } catch (\Exception $e) {
                $stats['sessions_count'] = 0;
            }

            // 4. حساب عدد الطلاب الفعليين
            $allUniqueStudents = array_unique(array_merge($callStudents, $slotStudents, $sessionStudents));
            $stats['students_count'] = count($allUniqueStudents);
        } catch (\Exception $e) {
            Log::error("Stats Error for Teacher {$teacherId} in Favorites: " . $e->getMessage());
        }

        return $stats;
    }
    public function toggle(Request $request)
    {
        try {
            // 1️⃣ Validation
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teacher_applications,id',
            ], [
                'teacher_id.required' => 'رقم المعلم مطلوب.',
                'teacher_id.exists'   => 'المعلم غير موجود.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'بيانات غير صحيحة.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // 2️⃣ جلب ملف الطالب
            $student = $request->user()->studentProfile;

            if (!$student) {
                return response()->json([
                    'status'  => false,
                    'message' => 'ملف الطالب غير موجود.',
                ], 404);
            }

            // 3️⃣ Toggle favorite
            $result = $student->favorites()->toggle($request->teacher_id);

            $status  = count($result['attached']) > 0 ? 'added' : 'removed';
            $message = $status === 'added'
                ? 'تمت الإضافة إلى المفضلة.'
                : 'تمت الإزالة من المفضلة.';

            return response()->json([
                'status'  => true,
                'message' => $message,
                'favorite_status' => $status
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Toggle Favorite Error', [
                'user_id'    => optional($request->user())->id,
                'teacher_id' => $request->teacher_id ?? null,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تنفيذ العملية.'
            ], 500);
        }
    }
    public function index(Request $request)
    {
        try {
            $student = $request->user()->studentProfile;

            if (!$student) {
                return response()->json([
                    'status'  => false,
                    'message' => 'ملف الطالب غير موجود.',
                ], 404);
            }

            $perPage = $request->query('per_page', 10);

            // جلب المفضلة مع العلاقات والتقييمات المحسوبة
            $favorites = $student->favorites()
                ->where('status', 'approved')
                ->with([
                    'profile' => function ($query) {
                        $query->withAvg('ratings', 'rating')
                            ->withCount('ratings');
                    },
                    'profile.user',
                    'tracks' // إضافة المسارات (التخصصات) لتوحيد شكل الكارد
                ])
                ->paginate($perPage);

            $favorites->getCollection()->transform(function ($application) {
                $profile = $application->profile;
                $user = optional($profile)->user;
                $teacherId = optional($profile)->id;

                $name = optional($user)->name ?? $application->full_name;
                $photoPath = optional($profile)->profile_photo_path ?? $application->profile_photo_path;

                $photoUrl = $photoPath
                    ? asset('storage/' . $photoPath)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=128';

                // 🚀 استدعاء الإحصائيات للمعلم
                $stats = $this->getTeacherStats($teacherId);

                return [
                    'id'               => $teacherId, // مهم جداً أن يكون هذا ID المعلم وليس الـ Application
                    'application_id'   => $application->id,
                    'name'             => $name,
                    'photo_url'        => $photoUrl,
                    'is_online'        => (bool) optional($profile)->is_online,

                    // التقييمات المحسوبة
                    'rating'           => (float) number_format(optional($profile)->ratings_avg_rating ?? 5.0, 1, '.', ''),
                    'reviews_count'    => (int) (optional($profile)->ratings_count ?? 0),

                    // إحصائيات المعلم
                    'students_count'   => $stats['students_count'],
                    'calls_count'      => $stats['calls_count'],
                    'slots_count'      => $stats['slots_count'],
                    'sessions_count'   => $stats['sessions_count'],

                    'qualification'    => $application->qualification,
                    'country'          => $application->origin_country,
                    'experience_years' => $application->experience_years,
                    'specialties'      => $application->tracks->map(function ($track) {
                        return [
                            'id'   => $track->id,
                            'name' => $track->name,
                        ];
                    }),
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب المفضلة بنجاح.',
                'data'    => $favorites
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Favorites Error', [
                'user_id' => optional($request->user())->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في جلب المفضلة.'
            ], 500);
        }
    }
}
