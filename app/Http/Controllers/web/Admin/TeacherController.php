<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveTeacherRequest;
use App\Mail\TeacherApprovedMail;
use App\Models\Teacher;
use App\Models\Teacher_application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher_application::with(['tracks', 'profile.user'])->latest()->get();
        return view('dashboard.teachers', compact('teachers'));
    }

    public function approve(ApproveTeacherRequest $request, $id)
    {
        $application = Teacher_application::findOrFail($id);

        // التحقق من أن الطلب لم يتم قبوله مسبقاً لتجنب تكرار إنشاء الحسابات
        if ($application->status === 'approved') {
            return back()->with('error', 'هذا الطلب تم قبوله مسبقاً.');
        }

        DB::beginTransaction(); // استخدام الترانزاكشن لضمان سلامة البيانات

        try {
            // 1. معالجة الصورة الشخصية (التحقق من وجودها)
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')
                    ->store('teachers/photos', 'public');
            } else {
                // خيار احتياطي: استخدام صورة الطلب الأصلية إذا لم يرفع الأدمن صورة جديدة
                $photoPath = $application->profile_photo_path;
            }

            // 2. إنشاء المستخدم
            $user = User::create([
                'name'     => $application->full_name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'teacher',
            ]);

            // 3. إنشاء بروفايل المعلم
            Teacher::create([
                'user_id'                => $user->id,
                'teacher_application_id' => $application->id,
                'salary'                 => $request->salary,
                'profile_photo_path'     => $photoPath,
                'minutes'                => 0, // تعيين القيمة الافتراضية للدقائق
            ]);

            // 4. تحديث حالة الطلب
            $application->update([
                'status' => 'approved'
            ]);

            DB::commit(); // اعتماد التغييرات في قاعدة البيانات

            // 5. إرسال الإيميل (خارج الترانزاكشن لتجنب تأخير الاستجابة في حال بطء السيرفر)
            try {
                Mail::to($user->email)->send(new TeacherApprovedMail($user, $request->password, $request->salary));
            } catch (\Exception $e) {
                Log::error('فشل إرسال إيميل قبول المعلم: ' . $e->getMessage());
                // لا نوقف العملية هنا لأن الحساب تم إنشاؤه بالفعل
            }

            return back()->with('success', 'تم قبول المعلم بنجاح، إنشاء الحساب، وإرسال تفاصيل الدخول للبريد الإلكتروني');
        } catch (\Throwable $e) {
            DB::rollBack(); // تراجع عن كل العمليات في حال حدوث أي خطأ

            // حذف الصورة المرفوعة إذا فشلت العملية
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('خطأ في اعتماد المعلم: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ تقني: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $application = Teacher_application::findOrFail($id);

        // تحديث الحالة إلى "مرفوض"
        $application->update([
            'status' => 'rejected'
        ]);

        return redirect()->back()->with('success', 'تم رفض طلب المعلم.');
    }


    public function updateDetails(Request $request, $id)
    {
        $application = Teacher_application::findOrFail($id);

        // التأكد من وجود البروفايل والمستخدم (لأننا نحدث بياناتهم)
        if (!$application->profile || !$application->profile->user) {
            return redirect()->back()->with('error', 'لا يوجد حساب مستخدم مرتبط لهذا الطلب');
        }

        $profile = $application->profile;
        $user = $profile->user;

        // التحقق من البيانات
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'salary'        => 'required|numeric|min:0',
            'password'      => 'nullable|string|min:8',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'        => 'required|in:approved,not_active',
        ]);

        // 1. تحديث جدول المستخدمين (Login Info)
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // 2. تحديث جدول المعلمين (Profile Info)
        $profile->salary = $request->salary;
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('teachers/photos', 'public');
            $profile->profile_photo_path = $path;
        }
        $profile->save();

        // 3. تحديث حالة الطلب فقط (للسماح بالدخول أو منعه)
        // لا نقوم بتحديث الاسم أو الإيميل في جدول الطلبات للحفاظ على البيانات الأصلية
        $application->status = $request->status;
        $application->save();

        return redirect()->back()->with('success', 'تم تحديث بيانات المعلم بنجاح');
    }
    
}
