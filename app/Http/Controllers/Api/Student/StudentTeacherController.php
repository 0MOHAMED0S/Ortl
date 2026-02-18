<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Teacher_application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentTeacherController extends Controller
{
    public function index()
    {
        try {
            // 1️⃣ جلب المعلمين الموافق عليهم فقط مع العلاقات
            $teachers = Teacher_application::where('status', 'approved')
                ->with('profile.user')
                ->get();

            // 2️⃣ تنسيق البيانات
            $formattedTeachers = $teachers->map(function ($teacher) {

                // الاسم: استخدم اسم حساب المستخدم إذا موجود، وإلا اسم التطبيق
                $name = optional(optional($teacher->profile)->user)->name ?? $teacher->full_name;

                // الصورة: استخدم صورة الملف الشخصي إذا موجودة، وإلا استخدم Avatar
                $photoPath = optional($teacher->profile)->profile_photo_path ?? null;
                $photoUrl = $photoPath
                    ? asset('storage/' . $photoPath)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=128';

                return [
                    'id' => $teacher->id,
                    'name' => $name,
                    'photo_url' => $photoUrl,
                    'qualification' => $teacher->qualification,
                    'country' => $teacher->origin_country,
                    'languages' => $teacher->languages,
                    'specialties' => $teacher->specialties,
                    'experience_years' => $teacher->experience_years,
                    'about' => $teacher->ijazas_text,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع المعلمين بنجاح.',
                'data'    => $formattedTeachers
            ], 200);
        } catch (\Throwable $e) {

            Log::error('فشل في جلب المعلمين', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
}
