<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
public function toggleTeacherRegistration(Request $request)
{
    // 1. استدعاء أو إنشاء الإعدادات
    $setting = Setting::firstOrCreate([]);

    // 2. التحقق من الـ Checkbox وتحويله للنص المناسب
    // إذا تم تحديد المربع (true) -> القيمة تصبح 'open'
    // إذا لم يتم تحديده (false) -> القيمة تصبح 'close'
    $status = $request->has('teacher_application_status') ? 'open' : 'close';

    // 3. الحفظ
    $setting->teacher_application_status = $status;
    $setting->save();

    // 4. رسالة النجاح
    $message = ($status === 'open')
        ? 'تم فتح باب التسجيل بنجاح'
        : 'تم إغلاق باب التسجيل بنجاح';

    return redirect()->back()->with('success', $message);
}
}
