<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreTeacherApplicationRequest;
use App\Models\Setting;
use App\Models\Teacher_application;
use App\Models\Track;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TeacherApplicationController extends Controller
{
    public function index()
    {
        $setting = Setting::first();

        // التأكد من أن التقديم مفتوح في الإعدادات
        if ($setting && $setting->teacher_application_status === 'open') {
            // جلب المسارات النشطة ليعرضها المعلم في النموذج
            $tracks = Track::where('status', 'active')->latest()->get();
            return view('main.teacher', compact('tracks'));
        }

        return view('main.close');
    }

public function store(StoreTeacherApplicationRequest $request)
{
    // جلب البيانات التي تمت المصادقة عليها
    $data = $request->validated();

    DB::beginTransaction();

    try {
        /* 1. معالجة رفع الصورة الشخصية */
        // لاحظ هنا: نستخدم 'profile_photo' وهو اسم الحقل القادم من الفورم (Request)
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo_path'] = $request
                ->file('profile_photo')
                ->store('teacher-applications/photos', 'public');
        }

        /* 2. معالجة رفع ملف السيرة الذاتية */
        if ($request->hasFile('cv_pdf')) {
            $data['cv_pdf_path'] = $request
                ->file('cv_pdf')
                ->store('teacher-applications/cvs', 'public');
        }

        /* 3. تنظيف البيانات قبل الإرسال للموديل */
        $tracks = $request->tracks ?? [];

        // الحقول التي يجب حذفها لأنها ليست أعمدة في جدول teacher_applications
        unset($data['tracks']);
        unset($data['profile_photo']); // نحذف الملف الأصلي ونبقي على الـ path الذي أنشأناه فوق
        unset($data['cv_pdf']);        // نحذف الملف الأصلي ونبقي على الـ path الذي أنشأناه فوق

        // الآن $data تحتوي على profile_photo_path و cv_pdf_path بشكل صحيح

        /* 4. إنشاء الطلب */
        $teacherApplication = Teacher_application::create($data);

        /* 5. ربط المسارات */
        if (!empty($tracks)) {
            $teacherApplication->tracks()->attach($tracks);
        }

        DB::commit();

        return view('main.success', [
            'success' => 'تم إرسال طلبك بنجاح، وسيتم التواصل معك بعد المراجعة'
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        // تنظيف الملفات إذا رفعت وفشلت العملية
        if (isset($data['profile_photo_path'])) {
            Storage::disk('public')->delete($data['profile_photo_path']);
        }
        if (isset($data['cv_pdf_path'])) {
            Storage::disk('public')->delete($data['cv_pdf_path']);
        }

        Log::error('خطأ أثناء إرسال طلب المعلم: ' . $e->getMessage());

        return back()->withErrors([
            'error' => 'حدث خطأ تقني: ' . $e->getMessage()
        ])->withInput();
    }
}
}
