<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ads\AdRequest;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdsController extends Controller
{
    public function index()
    {
        try {
            $ads = Ad::latest()->get();
            return view('dashboard.ads', compact('ads'));
        } catch (\Throwable $e) {
            Log::error('Failed to fetch ads', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'فشل تحميل الإعلانات. حاول مرة أخرى.');
        }
    }

    public function store(AdRequest $request)
    {
        try {
            $data = $request->except('image');

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('ads', 'public');
            }

            $data['status'] = 'active';

            Ad::create($data);

            return back()->with('success', 'تم إضافة الإعلان بنجاح');
        } catch (\Throwable $e) {
            Log::error('Ad Store Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'فشل إضافة الإعلان. حاول مرة أخرى.');
        }
    }

    public function update(AdRequest $request, Ad $ad)
    {
        try {
            $data = $request->except('image');

            if ($request->hasFile('image')) {
                if ($ad->image) {
                    Storage::disk('public')->delete($ad->image);
                }
                $data['image'] = $request->file('image')->store('ads', 'public');
            }

            $ad->update($data);

            return back()->with('success', 'تم تحديث الإعلان');
        } catch (\Throwable $e) {
            Log::error('Ad Update Error', [
                'ad_id' => $ad->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'فشل تحديث الإعلان. حاول مرة أخرى.');
        }
    }


    public function destroy(Ad $ad)
    {
        try {
            // حذف الصورة إذا موجودة
            if ($ad->image) {
                Storage::disk('public')->delete($ad->image);
            }

            $ad->delete();

            return back()->with('success', 'تم حذف الإعلان بنجاح');
        } catch (\Throwable $e) {
            Log::error('Failed to delete ad', [
                'ad_id' => $ad->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'فشل حذف الإعلان. حاول مرة أخرى.');
        }
    }
}
