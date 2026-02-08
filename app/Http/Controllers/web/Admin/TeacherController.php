<?php

namespace App\Http\Controllers\web\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveTeacherRequest;
use App\Mail\TeacherApprovedMail;
use App\Models\Teacher;
use App\Models\Teacher_application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

        $photoPath = $request->file('profile_photo')
            ->store('teachers/photos', 'public');

        // إنشاء المستخدم
        $user = User::create([
            'name'     => $application->full_name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // كلمة المرور المشفرة للداتابيز
            'role'     => 'teacher',
        ]);

        // إنشاء بروفايل المعلم
        Teacher::create([
            'user_id'                => $user->id,
            'teacher_application_id' => $application->id,
            'salary'                 => $request->salary,
            'profile_photo_path'     => $photoPath,
        ]);

        // تحديث حالة الطلب
        $application->update([
            'status' => 'approved'
        ]);

        // --- إرسال الإيميل ---
        try {
            // نمرر كلمة المرور "غير المشفرة" للإيميل ليراها المستخدم
            Mail::to($user->email)->send(new TeacherApprovedMail($user, $request->password, $request->salary));
        } catch (\Exception $e) {
            // في حال فشل الإيميل، نكمل العملية ولكن نعطي تنبيه (اختياري)
            // return back()->with('warning', 'تم القبول ولكن فشل إرسال الإيميل');
        }
        // ---------------------

        return back()->with('success', 'تم قبول المعلم بنجاح، إنشاء الحساب، وإرسال تفاصيل الدخول للبريد الإلكتروني');
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
