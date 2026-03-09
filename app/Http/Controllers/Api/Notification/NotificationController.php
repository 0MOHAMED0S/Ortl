<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $notifications = $user->notifications()->paginate($request->get('per_page', 15));

            $notifications->getCollection()->transform(function ($notification) {
                $data = $notification->data;

                return [
                    'id'         => $notification->id,
                    'title'      => $data['title'] ?? 'إشعار جديد',
                    'message'    => $data['message'] ?? '',
                    'type'       => $data['type'] ?? 'general', // لمعرفة نوع الإشعار في الفلاتر (مثل: incoming_call, gift_claimed)
                    'payload'    => $data['payload'] ?? null,   // البيانات الإضافية (مثل call_id لفتح شاشة المكالمة فوراً)
                    'is_read'    => $notification->read_at !== null,
                    'created_at' => $notification->created_at->diffForHumans(), // مثال: "منذ 5 دقائق"
                    'date'       => $notification->created_at->format('Y-m-d'),
                    'time'       => $notification->created_at->format('h:i A'),
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع الإشعارات بنجاح.',
                'data'    => [
                    'unread_count'  => $user->unreadNotifications()->count(),
                    'notifications' => $notifications
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Notifications Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء جلب الإشعارات.'
            ], 500);
        }
    }

    /**
     * تحديد الإشعارات كمقروءة (إما إشعار واحد أو الكل)
     */
    public function markAsRead(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($request->has('notification_id')) {
                // تحديد إشعار معين كمقروء
                $notification = $user->notifications()->where('id', $request->notification_id)->first();
                if ($notification) {
                    $notification->markAsRead();
                }
            } else {
                // تحديد جميع الإشعارات كمقروءة
                $user->unreadNotifications->markAsRead();
            }

            return response()->json([
                'status'  => true,
                'message' => 'تم تحديث حالة الإشعارات بنجاح.',
                'data'    => [
                    'unread_count' => $user->unreadNotifications()->count() // نرسل العداد الجديد ليتحدث في أيقونة الجرس 🔔
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Mark Notifications Read Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الإشعارات.'
            ], 500);
        }
    }
}
