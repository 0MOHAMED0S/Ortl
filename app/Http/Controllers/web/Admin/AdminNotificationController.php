<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // تأكد من استدعاء موديل المستخدم
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

            // 4. إرسال إشعارات الهواتف/المتصفح عبر OneSignal
            $this->sendOneSignalNotification($target, $users, $request->title, $request->message, $data);

            return back()->with('success', 'تم إرسال الإشعار بنجاح إلى الفئة المحددة.');

        } catch (\Throwable $e) {
            Log::error('Broadcast Notification Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إرسال الإشعار.');
        }
    }

    /**
     * دالة مساعدة لإرسال إشعارات OneSignal
     */
    private function sendOneSignalNotification($target, $users, $title, $message, $extraData)
    {
        $appId = env('ONESIGNAL_APP_ID');
        $apiKey = env('ONESIGNAL_REST_API_KEY');

        $payload = [
            'app_id'   => $appId,
            'headings' => ['en' => $title, 'ar' => $title],
            'contents' => ['en' => $message, 'ar' => $message],
            'data'     => $extraData, // إرسال بيانات إضافية (مثل نوع الإشعار) للتعامل معها داخل التطبيق
        ];

        // تحديد الفئة المستهدفة
        if ($target === 'all') {
            // إرسال للجميع
            $payload['included_segments'] = ['Subscribed Users'];
        } else {
            // إرسال لمستخدمين محددين بناءً على الـ ID الخاص بهم في قاعدة البيانات
            $externalUserIds = $users->pluck('id')->map(function($id) {
                return (string) $id; // OneSignal يطلب الـ ID كنص (String)
            })->toArray();

            if (!empty($externalUserIds)) {
                $payload['include_external_user_ids'] = $externalUserIds;
            }
        }

        // إرسال الطلب إلى سيرفرات OneSignal
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', $payload);

        // تسجيل الخطأ في حال فشل OneSignal لكي لا يتعطل النظام
        if (!$response->successful()) {
            Log::error('OneSignal Error: ' . $response->body());
        }
    }
}
