<?php

namespace App\Http\Controllers\Api\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdsController extends Controller
{

    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $ads = Ad::where('status', 'active')
                ->latest()
                ->paginate($perPage);
            $ads->getCollection()->transform(function ($ad) {
                return [
                    'id'        => $ad->id,
                    'image_url' => $ad->image ? asset('storage/' . $ad->image) : null,
                    'link'      => $ad->link, // الرابط الذي يفتح عند الضغط على البنر
                ];
            });
            return response()->json([
                'status'  => true,
                'message' => 'تم جلب الإعلانات بنجاح.',
                'data'    => $ads
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Ads API Fetch Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'فشل في جلب الإعلانات، يرجى المحاولة لاحقاً.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
