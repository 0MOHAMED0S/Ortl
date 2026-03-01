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
public function create() // يفضل تسميتها index لأنها تعرض الجدول
    {
        // 1. جلب جميع المعلمين مع بياناتهم لتعبئة الـ Select2 في المودال
        $teachers = Teacher::with(['user', 'tracks'])->get();

        // 2. جلب كل الجلسات مع بيانات المعلم والطلاب الحاضرين
        $sessions = RecitationSession::with(['teacher.user', 'students.student'])
            ->latest()
            ->get();

        // 3. إرجاع الواجهة
        return view('dashboard.sessions', compact('teachers', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'teacher_id'       => 'required|exists:teachers,id',
            'start_at'         => 'required|date',
            'end_at'           => 'required|date|after:start_at',
            'max_participants' => 'required|integer|min:1',
        ]);

        try {
            $start = Carbon::parse($validated['start_at']);
            $end = Carbon::parse($validated['end_at']);

            $validated['duration_minutes'] = $start->diffInMinutes($end);
            $validated['created_by']       = auth()->id();
            $validated['channel_name']     = 'recitation_' . bin2hex(random_bytes(6));

            RecitationSession::create($validated);

            return back()->with('success', 'تم جدولة الجلسة بنجاح.');
        } catch (\Exception $e) {
            Log::error("Failed to store recitation session: " . $e->getMessage());
            return back()->withInput()->with('error', 'حدث خطأ أثناء الحفظ، يرجى المحاولة مرة أخرى.');
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'teacher_id'       => 'required|exists:teachers,id',
            'start_at'         => 'required|date',
            'end_at'           => 'required|date|after:start_at',
            'max_participants' => 'required|integer|min:1',
        ]);

        try {
            $session = RecitationSession::findOrFail($id);

            $start = Carbon::parse($validated['start_at']);
            $end = Carbon::parse($validated['end_at']);

            $validated['duration_minutes'] = $start->diffInMinutes($end);

            $session->update($validated);

            return back()->with('success', 'تم تحديث بيانات الجلسة بنجاح.');
        } catch (\Exception $e) {
            Log::error("Failed to update recitation session: " . $e->getMessage());
            return back()->withInput()->with('error', 'حدث خطأ أثناء التحديث.');
        }
    }

    public function destroy($id)
    {
        try {
            $session = RecitationSession::findOrFail($id);
            $session->delete();

            return back()->with('success', 'تم حذف الجلسة نهائياً.');
        } catch (\Exception $e) {
            Log::error("Failed to delete recitation session: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء محاولة الحذف.');
        }
    }
}
