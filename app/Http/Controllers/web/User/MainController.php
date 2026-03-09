<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\Package;
use App\Models\Teacher;
use App\Models\Ad;
use App\Models\Track;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index()
    {
        // 1. جلب الباقات النشطة فقط
        $packages = Package::where('status', 'active')->get();

        // 2. جلب إعدادات التواصل
        $contact = ContactSetting::first();

        // 3. جلب المعلمين المعتمدين (Approved) فقط مع بيانات المستخدم
        // قمت بإضافة شرط 'is_approved' للتأكد من ظهور المعلمين المقبولين فقط
        $teachers = Teacher::with('user')
    ->whereHas('application', function($query) {
        $query->where('status', 'approved');
    })
    ->take(8)
    ->get();

        // 4. جلب الإعلانات النشطة فقط
        $ads = Ad::where('status', 'active')->get();

        // 5. جلب المسارات النشطة فقط
        $tracks = Track::where('status', 'active')->get();

        // تمرير جميع المتغيرات إلى الواجهة
        return view('welcome', compact('packages', 'contact', 'teachers', 'ads', 'tracks'));
    }
}
