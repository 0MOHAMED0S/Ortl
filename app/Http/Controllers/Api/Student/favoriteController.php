<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
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
            $favorites = $student->favorites()
                ->where('status', 'approved')
                ->with('profile.user')
                ->paginate($perPage);

            $favorites->getCollection()->transform(function ($teacher) {
                $name = optional(optional($teacher->profile)->user)->name ?? $teacher->full_name;
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
                    'experience_years' => $teacher->experience_years,
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
