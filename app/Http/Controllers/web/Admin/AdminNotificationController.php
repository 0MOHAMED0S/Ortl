<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // تأكد من استدعاء موديل المستخدم
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('dashboard.notification', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة.');
    }

    // الدالة الجديدة لإرسال الإشعارات الجماعية
    public function broadcast(Request $request)
    {
        $request->validate([
            'target'  => 'required|in:all,students,teachers',
            'title'   => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        try {
            $target = $request->target;
            $data = [
                'type'    => 'admin_broadcast',
                'title'   => $request->title,
                'message' => $request->message,
            ];

            // 1. تحديد المستخدمين المستهدفين
            // ⚠️ ملاحظة: قمت بافتراض أن لديك حقل 'role' في جدول المستخدمين.
            // قم بتعديل هذا الاستعلام حسب طريقة تمييزك للمعلم والطالب في مشروعك
            $query = User::query();

            if ($target === 'students') {
                $query->where('role', 'student');
            } elseif ($target === 'teachers') {
                $query->where('role', 'teacher');
            }

            $users = $query->get();

            if ($users->isEmpty()) {
                return back()->with('error', 'لا يوجد مستخدمين في هذه الفئة لإرسال الإشعار.');
            }

            // 2. حفظ الإشعار في قاعدة البيانات لجميع المستخدمين المستهدفين
            Notification::send($users, new \App\Notifications\SystemBroadcastNotification($data));

            // 3. إرسال الإشعار اللحظي عبر Pusher
            broadcast(new \App\Events\AdminBroadcastEvent($target, $data));

            return back()->with('success', 'تم إرسال الإشعار بنجاح إلى الفئة المحددة.');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Broadcast Notification Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إرسال الإشعار.');
        }
    }
}
