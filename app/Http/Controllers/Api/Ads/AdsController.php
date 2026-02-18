<?php

namespace App\Http\Controllers\Api\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdsController extends Controller
{
    /**
     * عرض الإعلانات النشطة مع Pagination
     */
    public function index(Request $request)
    {
        try {
            // 1️⃣ عدد العناصر لكل صفحة
            $perPage = $request->query('per_page', 10);

            // 2️⃣ جلب الإعلانات النشطة
            $ads = Ad::where('status', 'active')
                ->latest()
                ->paginate($perPage);

            // 3️⃣ تعديل البيانات قبل الإرجاع
            $ads->getCollection()->transform(function ($ad) {
                return [
                    'id'        => $ad->id,
                    'title'     => $ad->title,
                    'subtitle'  => $ad->subtitle,
                    'bg_color'  => $ad->bg_color,
                    'image_url' => $ad->image ? asset('storage/' . $ad->image) : null,
                ];
            });

            // 4️⃣ إعادة الرد الموحد
            return response()->json([
                'status'  => true,
                'message' => 'تم جلب الإعلانات بنجاح.',
                'data'    => $ads
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Ads Fetch Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في جلب الإعلانات. حاول مرة أخرى.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
