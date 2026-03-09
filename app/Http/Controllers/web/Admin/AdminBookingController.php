<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlotBooking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index()
    {
        // جلب الحجوزات مع بيانات الطالب، الموعد، والمعلم
        $bookings = SlotBooking::with(['user', 'slot.teacher.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.booking', compact('bookings'));
    }
}
