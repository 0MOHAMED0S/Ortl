<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ChangePasswordRequest;
use App\Http\Requests\Student\CheckOtpRequest;
use App\Http\Requests\Student\CompleteRegistrationRequest;
use App\Http\Requests\Student\ForgotPasswordSendOtpRequest;
use App\Http\Requests\Student\LoginRequest;
use App\Http\Requests\Student\ResetPasswordRequest;
use App\Http\Requests\Student\SendOtpRequest;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Mail\OtpMail;
use App\Mail\ResetOtpMail;
use App\Models\OtpCode;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class StudentAuthController extends Controller
{
    public function sendOtp(SendOtpRequest $request)
    {
        try {
            $otp = random_int(1000, 9999);

            OtpCode::updateOrCreate(
                ['email' => $request->email],
                [
                    'otp' => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'is_verified' => false,
                ]
            );

            Mail::to($request->email)->send(new OtpMail($otp));

            return response()->json([
                'status'  => true,
                'message' => 'تم إرسال رمز التحقق بنجاح.',
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Send OTP Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في إرسال رمز التحقق. حاول مرة أخرى لاحقاً.',
            ], 500);
        }
    }


    public function checkOtp(CheckOtpRequest $request)
    {
        try {
            $otpRecord = OtpCode::where('email', $request->email)
                ->where('otp', $request->otp)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status'  => false,
                    'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
                ], 400);
            }

            $otpRecord->update([
                'is_verified' => true,
                'otp' => null, // لأمان أعلى
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم التحقق من رمز التأكيد بنجاح.',
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Check OTP Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء التحقق من الرمز. حاول مرة أخرى لاحقاً.',
            ], 500);
        }
    }


    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        DB::beginTransaction();

        try {
            // 🔐 التأكد من أن الـ OTP تم التحقق منه
            $otpRecord = OtpCode::where('email', $request->email)
                ->where('is_verified', true)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لم يتم تأكيد البريد الإلكتروني. يرجى إدخال رمز التحقق أولاً.',
                ], 403);
            }

            // 📸 معالجة رفع الصورة الشخصية
            $photoPath = null;
            if ($request->hasFile('profile_photo_path')) {
                // تخزين الصورة في مجلد 'profiles' داخل الـ public disk
                $photoPath = $request->file('profile_photo_path')->store('profiles', 'public');
            }

            // 1️⃣ إنشاء المستخدم (بدون تمرير email_verified_at هنا)
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'student',
            ]);

            // ✅ توثيق البريد الإلكتروني فوراً (هذه الدالة تضع now() في قاعدة البيانات مباشرة)
            $user->markEmailAsVerified();

            // 2️⃣ إنشاء بروفايل الطالب
            $student = Student::create([
                'user_id'             => $user->id,
                'country_id'          => $request->country_id,
                'phone'               => $request->phone,
                'address'             => $request->address,
                'qualification'       => $request->qualification,
                'professional_status' => $request->professional_status,
                'gender'              => $request->gender,
                'profile_photo_path'  => $photoPath,
            ]);

            // 🌟 إضافة الرابط الكامل للصورة للاستجابة
            $student->profile_photo_url = $photoPath ? asset('storage/' . $photoPath) : null;

            // 3️⃣ حذف OTP لمنع إعادة الاستخدام
            $otpRecord->delete();

            // 4️⃣ إنشاء التوكن
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم إنشاء الحساب بنجاح.',
                'data' => [
                    'user'    => $user,
                    'profile' => $student,
                    'token'   => $token,
                ]
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            // تنظيف الصورة إذا فشلت العملية
            if (isset($photoPath) && $photoPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($photoPath);
            }

            \Illuminate\Support\Facades\Log::error('Complete Registration Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء إنشاء الحساب. حاول مرة أخرى.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'بيانات تسجيل الدخول غير صحيحة.',
                ], 401);
            }

            if ($user->role !== 'student') {
                return response()->json([
                    'status'  => false,
                    'message' => 'غير مصرح لك بتسجيل الدخول من هنا.',
                ], 403);
            }

            // لو حابب تخلي تسجيل دخول بجلسة واحدة فقط
            // $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            // 🌟 Prepare the profile and add the full photo URL
            $profile = $user->studentProfile;
            if ($profile) {
                $profile->profile_photo_url = $profile->profile_photo_path
                    ? asset('storage/' . $profile->profile_photo_path)
                    : null;
            }

            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل الدخول بنجاح.',
                'data' => [
                    'user'    => $user,
                    'profile' => $profile,
                    'token'   => $token,
                ]
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Login Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول. حاول مرة أخرى.',
            ], 500);
        }
    }


    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        // تأكد أن العلاقة في موديل User اسمها student
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'status'  => false,
                'message' => 'لم يتم العثور على الملف الشخصي.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            // 🔹 تحديث اسم المستخدم إذا تم إرساله
            if ($request->filled('name')) {
                $user->update(['name' => $request->name]);
            }

            // 🔹 تجهيز بيانات الطالب للتحديث (بما في ذلك التفضيلات الجديدة)
            $studentData = $request->only([
                // البيانات الأساسية
                'phone',
                'address',
                'qualification',
                'professional_status',
                'country_id',
                'gender',

                // 📖 تفضيلاتي التعليمية
                'age_group',
                'reading_level',
                'preferred_teacher_language',
                'reading_track',
                'memorized_amount',

                // ⏱️ تفضيلات الجلسة
                'plan_name',
                'reading_type',
                'teacher_response_speed'
            ]);

            // 🔹 معالجة الصورة الشخصية (باستخدام الاسم profile_photo_path)
            if ($request->hasFile('profile_photo_path')) {
                // حذف الصورة القديمة من السيرفر لتوفير المساحة
                if ($student->profile_photo_path) {
                    Storage::disk('public')->delete($student->profile_photo_path);
                }

                // تخزين الصورة الجديدة وإضافة المسار لمصفوفة التحديث
                $studentData['profile_photo_path'] = $request->file('profile_photo_path')
                    ->store('students/photos', 'public');
            }

            // 🔹 تحديث سجل الطالب في قاعدة البيانات
            $student->update($studentData);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم تحديث الملف الشخصي بنجاح.',
                'data' => [
                    'user'      => $user->fresh(),
                    'profile'   => $student->fresh(),
                    'photo_url' => $student->profile_photo_path
                        ? asset('storage/' . $student->profile_photo_path)
                        : null,
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Update Profile Error', [
                'user_id' => $user->id,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في تحديث الملف الشخصي. حاول مرة أخرى.',
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {
            $user = $request->user()->load(['student.country']);
            $userId = $user->id;
            if ($user->student) {
                $user->student->profile_photo_url = $user->student->profile_photo_path
                    ? asset('storage/' . $user->student->profile_photo_path)
                    : null;
            }
            $callsData = DB::table('call_sessions')
                ->where('student_id', $userId)
                ->where('status', 'ended')
                ->selectRaw('COUNT(id) as total_calls, SUM(duration_minutes) as total_minutes')
                ->first();

            $callsCount = $callsData->total_calls ?? 0;
            $totalMinutes = (int) ($callsData->total_minutes ?? 0);

            // حساب الساعات والدقائق
            $learningHours = floor($totalMinutes / 60);
            $remainingMinutes = $totalMinutes % 60;

            // ب. جلب عدد الحجوزات (المواعيد)
            $slotsCount = DB::table('slot_bookings')
                ->where('user_id', $userId)
                ->where('status', '!=', 'cancelled')
                ->count();

            // ج. جلب عدد الجلسات (محمية بـ try-catch في حال لم تنشئ الجدول بعد)
            $sessionsCount = 0;
            try {
                $sessionsCount = DB::table('sessions')
                    ->where('student_id', $userId)
                    ->count();
            } catch (\Exception $e) {
                $sessionsCount = 0;
            }

            // ==========================================
            // إرفاق الإحصائيات مع بيانات المستخدم
            // ==========================================
            $user->statistics = [
                'calls_count'    => $callsCount,
                'slots_count'    => $slotsCount,
                'sessions_count' => $sessionsCount,
                'learning_stats' => [
                    'total_minutes' => $totalMinutes,
                    'hours'         => $learningHours,
                    'minutes'       => $remainingMinutes,
                    'formatted'     => "{$learningHours} ساعة و {$remainingMinutes} دقيقة"
                ]
            ];

            return response()->json([
                'status' => true,
                'data'   => $user
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Student Profile Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء جلب البيانات.',
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || !$user->currentAccessToken()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'المستخدم غير مسجل الدخول.',
                ], 401);
            }

            $user->currentAccessToken()->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل الخروج بنجاح.',
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Logout Error', [
                'user_id' => optional($request->user())->id,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تسجيل الخروج. حاول مرة أخرى.',
            ], 500);
        }
    }


    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();

            // التحقق من كلمة المرور الحالية
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'كلمة المرور الحالية غير صحيحة.'
                ], 401);
            }

            // تحديث كلمة المرور الجديدة
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم تغيير كلمة المرور بنجاح.'
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Change Password Error', [
                'user_id' => optional($request->user())->id,
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تغيير كلمة المرور. حاول مرة أخرى.'
            ], 500);
        }
    }


    public function forgotPasswordSendOtp(ForgotPasswordSendOtpRequest $request)
    {
        try {
            $otp = random_int(1000, 9999);

            OtpCode::updateOrCreate(
                ['email' => $request->email],
                [
                    'otp'         => $otp,
                    'expires_at'  => now()->addMinutes(10),
                    'is_verified' => false,
                ]
            );

            // إرسال ميل إعادة تعيين كلمة المرور
            Mail::to($request->email)->send(new ResetOtpMail($otp));

            return response()->json([
                'status'  => true,
                'message' => 'تم إرسال رمز التحقق لإعادة تعيين كلمة المرور.',
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Forgot Password OTP Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في إرسال رمز التحقق. حاول مرة أخرى.',
            ], 500);
        }
    }


    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            // التأكد من وجود OTP موثق
            $otpRecord = OtpCode::where('email', $request->email)
                ->where('is_verified', true)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لم يتم التحقق من البريد الإلكتروني. يرجى تأكيد رمز التحقق أولاً.',
                ], 403);
            }

            // تحديث كلمة المرور
            $user = User::where('email', $request->email)->first();
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // حذف سجل OTP لمنع إعادة الاستخدام
            $otpRecord->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم إعادة تعيين كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.',
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Reset Password Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء إعادة تعيين كلمة المرور. حاول مرة أخرى.',
            ], 500);
        }
    }
}
