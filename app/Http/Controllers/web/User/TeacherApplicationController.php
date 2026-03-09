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

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            /* 1. معالجة رفع الصورة الشخصية */
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

            unset($data['tracks']);
            unset($data['profile_photo']);
            unset($data['cv_pdf']);

            /* 4. إنشاء الطلب */
            $teacherApplication = \App\Models\Teacher_application::create($data);

            /* 5. ربط المسارات */
            if (!empty($tracks)) {
                $teacherApplication->tracks()->attach($tracks);
            }

            \Illuminate\Support\Facades\DB::commit();

            // ==========================================
            // 🔔 إرسال إشعار لحظي لمديري النظام (Admins) بطلب المعلم الجديد
            // ==========================================
            try {
                $admins = \App\Models\User::where('role', 'admin')->get();

                if ($admins->count() > 0) {
                    $notificationData = [
                        'application_id' => $teacherApplication->id,
                        'full_name'      => $teacherApplication->full_name,
                        'email'          => $teacherApplication->email,
                    ];

                    // 1. الحفظ في الداتابيز لكل المديرين
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DynamicNotification(
                        'طلب انضمام معلم 📝',
                        "قدم/ت {$teacherApplication->full_name} طلب انضمام كمعلم للتو. يرجى مراجعة السيرة الذاتية.",
                        'new_teacher_application',
                        $notificationData
                    ));

                    // 2. إرسال البث اللحظي (Pusher) لكل مدير
                    foreach ($admins as $admin) {
                        broadcast(new \App\Events\NewTeacherApplication($admin->id, $notificationData));
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Admin Teacher App Notification Error: ' . $e->getMessage());
            }
            // ==========================================

            return view('main.success', [
                'success' => 'تم إرسال طلبك بنجاح، وسيتم التواصل معك بعد المراجعة'
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            // تنظيف الملفات إذا رفعت وفشلت العملية
            if (isset($data['profile_photo_path'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($data['profile_photo_path']);
            }
            if (isset($data['cv_pdf_path'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($data['cv_pdf_path']);
            }

            \Illuminate\Support\Facades\Log::error('خطأ أثناء إرسال طلب المعلم: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'حدث خطأ تقني: ' . $e->getMessage()
            ])->withInput();
        }
    }
}
