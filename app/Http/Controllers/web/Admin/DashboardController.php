<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Teacher_application;
use App\Models\Order;
use App\Models\CallSession;
use App\Models\SlotBooking;
use App\Models\WithdrawalRequest;
use App\Models\Package;
use App\Models\GiftCard;

class DashboardController extends Controller
{
    public function index()
    {
        // تجميع كافة الإحصائيات لمدير النظام
        $stats = [
            // 👥 المستخدمين
            'total_students'      => User::where('role', 'student')->count(),
            'total_teachers'      => Teacher::count(),
            'pending_applications'=> Teacher_application::where('status', 'pending')->count(),

            // 💰 المالية والمبيعات
            'total_revenue'       => Order::where('status', 'paid')->sum('amount'),
            'total_orders'        => Order::where('status', 'paid')->count(),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
            'total_gifts'         => GiftCard::where('payment_status', 'paid')->count(),

            // 📞 الجلسات والمواعيد
            'total_calls'         => CallSession::count(),
            'live_calls'          => CallSession::where('status', 'ongoing')->count(),
            'scheduled_bookings'  => SlotBooking::where('status', 'scheduled')->count(),

            // 📦 محتوى النظام
            'active_packages'     => Package::where('status', 'active')->count(),
        ];

        return view('dashboard.index', compact('stats'));
    }
}
