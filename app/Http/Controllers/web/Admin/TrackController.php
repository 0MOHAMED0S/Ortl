<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Track\StoreTrackRequest;
use App\Http\Requests\Admin\Track\UpdateTrackRequest;
use App\Models\Teacher;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // لاستخدام نظام التخزين

class TrackController extends Controller
{
public function index()
{
    try {
        // 1. جلب المسارات مع أعداد معلميها لعرض الكروت العلوية
        $tracks = Track::withCount('teachers')->latest()->get();

        // 2. جلب جميع المعلمين مع مساراتهم لجدول المعلمين
        $teachers = \App\Models\Teacher::with(['user', 'application', 'tracks'])
            ->latest()
            ->get();

        return view('dashboard.tracks', compact('tracks', 'teachers'));

    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Tracks Index Error', ['error' => $e->getMessage()]);
        return back()->with('error', 'حدث خطأ أثناء تحميل البيانات');
    }
}

    public function store(StoreTrackRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // معالجة رفع الأيقونة
            if ($request->hasFile('icon')) {
                $data['icon'] = $request->file('icon')->store('tracks', 'public');
            }

            Track::create($data);

            DB::commit();
            return back()->with('success', 'تم إضافة المسار بنجاح');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Track Store Error', [
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء إضافة المسار');
        }
    }

    public function update(UpdateTrackRequest $request, Track $track)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // معالجة تحديث الأيقونة
            if ($request->hasFile('icon')) {
                // حذف الأيقونة القديمة من السيرفر إذا وجدت
                if ($track->icon) {
                    Storage::disk('public')->delete($track->icon);
                }
                // تخزين الأيقونة الجديدة
                $data['icon'] = $request->file('icon')->store('tracks', 'public');
            }

            $track->update($data);

            DB::commit();
            return back()->with('success', 'تم تعديل المسار بنجاح');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Track Update Error', [
                'track_id' => $track->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء تعديل المسار');
        }
    }

public function destroy(Track $track)
    {
        try {
            // 1. التحقق الاستباقي: هل يوجد معلمون مرتبطون بهذا المسار؟
            if ($track->teachers()->exists()) {
                return back()->with('error', 'عفواً، لا يمكن حذف هذا المسار لارتباطه بمعلمين حاليين. يمكنك بدلاً من ذلك تغيير حالته إلى "متوقف".');
            }

            // 2. إذا لم يكن هناك معلمون، نقوم بحذف الأيقونة (إن وجدت)
            if ($track->icon) {
                Storage::disk('public')->delete($track->icon);
            }

            // 3. حذف المسار نهائياً
            $track->delete();

            return back()->with('success', 'تم حذف المسار بنجاح');

        } catch (\Throwable $e) {
            Log::error('Track Delete Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء محاولة حذف المسار');
        }
    }

public function updateTeacherTracks(Request $request, Teacher $teacher)
    {
        // 1. التحقق من صحة البيانات (إجباري اختيار مسار واحد على الأقل)
        $request->validate([
            'tracks'   => 'required|array|min:1',
            'tracks.*' => 'exists:tracks,id',
        ], [
            // رسائل خطأ مخصصة تظهر في الـ Toasts
            'tracks.required' => 'يرجى تحديد مسار واحد على الأقل للمعلم.',
            'tracks.min'      => 'يجب اختيار مسار واحد على الأقل للمعلم.',
        ]);

        try {
            // 2. تحديث المسارات
            // سيتم استبدال المسارات القديمة بالمسارات الجديدة المحددة
            $teacher->tracks()->sync($request->tracks);

            return back()->with('success', 'تم تحديث المسارات المسندة للمعلم بنجاح.');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Update Teacher Tracks Error: ', [
                'teacher_id' => $teacher->id,
                'error'      => $e->getMessage()
            ]);

            return back()->with('error', 'حدث خطأ أثناء محاولة تحديث مسارات المعلم.');
        }
    }
}
