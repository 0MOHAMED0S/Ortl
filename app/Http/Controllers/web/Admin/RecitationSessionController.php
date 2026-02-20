<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecitationSession;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RecitationSessionController extends Controller
{
    public function create()
    {
        $teachers = Teacher::with('user')->get();
        $sessions = RecitationSession::with('teacher.user')
            ->latest()
            ->get();

        return view('dashboard.sessions', compact('teachers', 'sessions'));
    }


public function store(Request $request)
{
    // 1. التحقق من الصلاحية (يفضل استخدام Middleware بدلاً من التحقق اليدوي)
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Unauthorized action.');
    }

    // 2. التحقق من البيانات
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'teacher_id' => 'required|exists:teachers,id',
        'start_at' => 'required|date',
        'end_at' => 'required|date|after:start_at',
        'max_participants' => 'required|integer|min:1',
    ]);

    try {
        // 3. معالجة البيانات الإضافية
        $start = \Carbon\Carbon::parse($validated['start_at']);
        $end = \Carbon\Carbon::parse($validated['end_at']);

        $validated['duration_minutes'] = $start->diffInMinutes($end);
        $validated['created_by'] = auth()->id();
        $validated['channel_name'] = 'recitation_' . bin2hex(random_bytes(6)); // أكثر أماناً من uniqid

        // 4. التخزين الفعلي
        $session = RecitationSession::create($validated);

        return redirect()
            ->route('admin.recitations.create') // يفضل التوجيه للقائمة بعد النجاح
            ->with('success', 'تم إنشاء الحصة بنجاح.');

    } catch (\Exception $e) {
        // تسجيل الخطأ إذا فشل التخزين لأي سبب تقني
        Log::error("Failed to store recitation session: " . $e->getMessage());

        return back()
            ->withInput()
            ->with('error', 'حدث خطأ أثناء الحفظ، يرجى المحاولة مرة أخرى.');
    }
}
}
