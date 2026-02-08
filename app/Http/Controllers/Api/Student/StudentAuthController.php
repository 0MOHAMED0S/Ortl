<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\CheckOtpRequest;
use App\Http\Requests\Student\CompleteRegistrationRequest;
use App\Http\Requests\Student\LoginRequest;
use App\Http\Requests\Student\SendOtpRequest;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Mail\OtpMail;
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
                'message' => 'OTP sent successfully.'
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Send OTP Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to send OTP. Please try again later.'
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
                    'message' => 'Invalid or expired OTP.'
                ], 400);
            }

            $otpRecord->update([
                'is_verified' => true,
                'otp' => null, // optional but recommended (security)
            ]);

            return response()->json([
                'message' => 'OTP verified successfully.'
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Check OTP Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        DB::beginTransaction();

        try {
            // 🔐 Ensure OTP verified
            $otpRecord = OtpCode::where('email', $request->email)
                ->where('is_verified', true)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'message' => 'Email not verified. Please verify OTP first.'
                ], 403);
            }

            // 1️⃣ Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
            ]);

            // 2️⃣ Create Student Profile
            $student = Student::create([
                'user_id' => $user->id,
                'country_id' => $request->country_id,
                'phone' => $request->phone,
                'address' => $request->address,
                'qualification' => $request->qualification,
                'professional_status' => $request->professional_status,
                'gender' => $request->gender,
            ]);

            // 3️⃣ Delete OTP (prevent reuse)
            $otpRecord->delete();

            // 4️⃣ Generate Token
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Registration successful.',
                'user' => $user,
                'profile' => $student,
                'token' => $token
            ], 201);
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Complete Registration Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'بيانات الاعتماد غير صحيحة'
                ], 401);
            }

            if ($user->role !== 'student') {
                return response()->json([
                    'message' => 'غير مصرح لك بالدخول من هنا'
                ], 403);
            }

            //  Optional: single-session login
            // $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'تم تسجيل الدخول بنجاح',
                'user' => $user,
                'profile' => $user->studentProfile,
                'token' => $token
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Login Error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء تسجيل الدخول، حاول مرة أخرى'
            ], 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $student = $user->studentProfile;

        if (!$student) {
            return response()->json([
                'message' => 'Profile not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // 🔹 Update user name
            if ($request->filled('name')) {
                $user->update([
                    'name' => $request->name
                ]);
            }

            // 🔹 Handle profile photo
            if ($request->hasFile('profile_photo')) {

                // delete old photo
                if ($student->profile_photo_path) {
                    Storage::disk('public')->delete($student->profile_photo_path);
                }

                $path = $request->file('profile_photo')
                    ->store('students/photos', 'public');

                $student->profile_photo_path = $path;
            }

            // 🔹 Update student fields
            $student->update($request->only([
                'phone',
                'address',
                'qualification',
                'professional_status',
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(),
                'profile' => $student->fresh(),
                'photo_url' => $student->profile_photo_path
                    ? asset('storage/' . $student->profile_photo_path)
                    : null
            ], 200);
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Update Profile Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to update profile. Please try again.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || !$user->currentAccessToken()) {
                return response()->json([
                    'message' => 'المستخدم غير مسجل الدخول'
                ], 401);
            }

            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'تم تسجيل الخروج بنجاح'
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Logout Error', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء تسجيل الخروج'
            ], 500);
        }
    }
}
