<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use Illuminate\Http\Request;

class AdminCallSessionController extends Controller
{
    public function index()
    {
        // جلب الجلسات مع بيانات الطالب والمعلم
        $sessions = CallSession::with(['student', 'teacher.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.call', compact('sessions'));
    }
    public function destroy($id)
    {
        try {
            $session = CallSession::findOrFail($id);
            $session->delete();

            return back()->with('success', 'تم حذف سجل المكالمة بنجاح.');
        } catch (\Throwable $e) {
            return back()->withErrors(['حدث خطأ أثناء حذف المكالمة.']);
        }
    }
}
