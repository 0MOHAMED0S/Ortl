<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ads\AdRequest;
use App\Models\Ad;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdsController extends Controller
{
    public function index()
    {
        try {
            $ads = Ad::latest()->get(); //
            return view('dashboard.ads', compact('ads')); //
        } catch (\Throwable $e) {
            Log::error('Failed to fetch ads: ' . $e->getMessage()); //
            return back()->with('error', 'فشل تحميل الإعلانات.'); //
        }
    }

    public function store(AdRequest $request)
    {
        try {
            $data = $request->validated(); //

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('ads', 'public'); //
            }

            $data['status'] = 'active'; //
            Ad::create($data); //

            return back()->with('success', 'تم إضافة الإعلان بنجاح'); //
        } catch (\Throwable $e) {
            Log::error('Ad Store Error: ' . $e->getMessage()); //
            return back()->with('error', 'فشل إضافة الإعلان.'); //
        }
    }

    public function update(AdRequest $request, Ad $ad)
    {
        try {
            $data = $request->validated(); //

            if ($request->hasFile('image')) {
                if ($ad->image) {
                    Storage::disk('public')->delete($ad->image); //
                }
                $data['image'] = $request->file('image')->store('ads', 'public'); //
            }

            $ad->update($data); //
            return back()->with('success', 'تم تحديث الإعلان بنجاح'); //
        } catch (\Throwable $e) {
            Log::error('Ad Update Error: ' . $e->getMessage()); //
            return back()->with('error', 'فشل تحديث الإعلان.'); //
        }
    }

public function destroy(Ad $ad)
{
    try {
        // 1. Delete image file from storage if it exists
        if ($ad->image) {
            Storage::disk('public')->delete($ad->image);
        }

        // 2. Delete record from database
        $ad->delete();

        return back()->with('success', 'تم حذف الإعلان بنجاح');
    } catch (\Throwable $e) {
        Log::error('Failed to delete ad: ' . $e->getMessage());
        return back()->with('error', 'حدث خطأ أثناء محاولة الحذف');
    }
}
}
