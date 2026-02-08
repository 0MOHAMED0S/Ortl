<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreTeacherApplicationRequest;
use App\Models\Setting;
use App\Models\Teacher_application;
use App\Models\Track;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherApplicationController extends Controller
{
    public function index()
    {
        $setting = Setting::first();

        if ($setting && $setting->teacher_application_status === 'open') {
            // جلب المسارات النشطة فقط
            $tracks = Track::where('status', 'active')->latest()->get();
            return view('main.teacher', compact('tracks'));
        }

        return view('main.close');
    }

    public function store(StoreTeacherApplicationRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // رفع الملف إذا تم تحميله
            if ($request->hasFile('cv_pdf')) {
                $data['cv_pdf_path'] = $request
                    ->file('cv_pdf')
                    ->store('teacher-applications', 'public');
            }

            // إزالة الحقول غير المرتبطة مباشرة بالجدول
            $tracks = $request->tracks ?? [];
            unset($data['tracks']);

            // إنشاء الطلب
            $teacherApplication = Teacher_application::create($data);

            // ربط المسارات
            if (!empty($tracks)) {
                $teacherApplication->tracks()->attach($tracks);
            }

            DB::commit();

            return view('main.success', [
                'success' => 'تم إرسال طلبك بنجاح، وسيتم التواصل معك بعد المراجعة'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            // تسجيل الخطأ في اللوج
            Log::error('خطأ أثناء إرسال طلب المعلم: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // إرجاع رسالة عامة للمستخدم
            return back()->withErrors([
                'error' => 'حدث خطأ أثناء إرسال الطلب. يرجى المحاولة لاحقاً.'
            ])->withInput();
        }
    }
}
