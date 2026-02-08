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
            $validated = $request->validated();
            $user = User::where('email', $validated['email'])->first();
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'بيانات الاعتماد غير صحيحة'
                ], 401);
            }
            if ($user->role !== 'teacher') {
                return response()->json([
                    'message' => 'غير مصرح لك بالدخول من هنا. هذا التطبيق للمعلمين فقط.'
                ], 403);
            }
            $token = $user->createToken('teacher_auth_token')->plainTextToken;
            return response()->json([
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
            }

            return response()->json([
                'message' => 'تم تسجيل الخروج بنجاح'
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Teacher Logout Error', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'فشل تسجيل الخروج. حاول مرة أخرى.'
            ], 500);
        }
    }
}
