<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request; // التصحيح هنا
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
            Log::error('Failed to fetch ads: ' . $e->getMessage());
            return back()->with('error', 'فشل تحميل الإعلانات.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link'  => 'nullable|url',
        ]);

        try {
            $data = $request->only(['link']);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('ads', 'public');
            }

            $data['status'] = 'active';
            Ad::create($data);

            return back()->with('success', 'تم إضافة البنر الإعلاني بنجاح');
        } catch (\Throwable $e) {
            Log::error('Ad Store Error: ' . $e->getMessage());
            return back()->with('error', 'فشل إضافة الإعلان.');
        }
    }

    public function update(Request $request, Ad $ad)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link'  => 'nullable|url',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $data = $request->only(['link', 'status']);

            if ($request->hasFile('image')) {
                if ($ad->image) {
                    Storage::disk('public')->delete($ad->image);
                }
                $data['image'] = $request->file('image')->store('ads', 'public');
            }

            $ad->update($data);
            return back()->with('success', 'تم تحديث الإعلان بنجاح');
        } catch (\Throwable $e) {
            Log::error('Ad Update Error: ' . $e->getMessage());
            return back()->with('error', 'فشل تحديث الإعلان.');
        }
    }

    public function destroy(Ad $ad)
    {
        try {
            if ($ad->image) {
                Storage::disk('public')->delete($ad->image);
            }

            $ad->delete();

            return back()->with('success', 'تم حذف الإعلان بنجاح');
        } catch (\Throwable $e) {
            Log::error('Failed to delete ad: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء محاولة الحذف');
        }
    }
}
