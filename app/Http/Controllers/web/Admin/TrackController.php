<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Track\StoreTrackRequest;
use App\Http\Requests\Admin\Track\UpdateTrackRequest;
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
        $tracks = Track::withCount('teachers')->latest()->get();
        return view('dashboard.tracks', compact('tracks'));
    } catch (\Throwable $e) {
        Log::error('Tracks Index Error', ['error' => $e->getMessage()]);
        return back()->with('error', 'حدث خطأ أثناء تحميل المسارات');
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
            // حذف الأيقونة المرتبطة بالمسار عند الحذف النهائي
            if ($track->icon) {
                Storage::disk('public')->delete($track->icon);
            }

            $track->delete();
            return back()->with('success', 'تم حذف المسار بنجاح');
        } catch (\Throwable $e) {
            Log::error('Track Delete Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء حذف المسار');
        }
    }
}
