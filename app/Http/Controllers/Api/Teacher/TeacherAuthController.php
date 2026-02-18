<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Teacher\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TeacherAuthController extends Controller
{
public function login(LoginRequest $request)
{
    try {
        // ✅ التحقق من البيانات باستخدام الـ FormRequest
        $validated = $request->validated();

        // 🔹 البحث عن المستخدم
        $user = User::where('email', $validated['email'])->first();

        // 🔹 التحقق من كلمة المرور
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'بيانات الاعتماد غير صحيحة'
            ], 401);
        }

        // 🔹 التحقق من الدور
        if ($user->role !== 'teacher') {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بالدخول من هنا. هذا التطبيق للمعلمين فقط.'
            ], 403);
        }

        // 🔹 إنشاء توكن جديد
        $token = $user->createToken('teacher_auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'profile' => $user->teacherProfile,
            'token' => $token
        ], 200);

    } catch (\Throwable $e) {
        Log::error('Teacher Login Error', [
            'email' => $request->email,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'فشل تسجيل الدخول. حاول مرة أخرى.'
        ], 500);
    }
}


public function logout(Request $request)
{
    try {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'المستخدم غير مسجل الدخول'
        ], 401);

    } catch (\Throwable $e) {
        Log::error('Teacher Logout Error', [
            'user_id' => optional($request->user())->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'فشل تسجيل الخروج. حاول مرة أخرى.'
        ], 500);
    }
}

}
