<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    // عرض صفحة الإشعارات
    public function index()
    {
        // جلب إشعارات المدير الحالي (مقروءة وغير مقروءة)
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('dashboard.notification', compact('notifications'));
    }

    // تحديد إشعار معين كمقروء
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    }

    // تحديد كل الإشعارات كمقروءة
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة.');
    }
}
