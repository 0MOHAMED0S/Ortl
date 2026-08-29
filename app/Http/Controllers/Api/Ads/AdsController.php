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
            // Validate and cap the 'per_page' parameter to prevent overloading the database (Max 50)
            $perPage = $request->query('per_page', 10);
            $perPage = is_numeric($perPage) ? (int) $perPage : 10;
            $perPage = max(1, min(50, $perPage));

            $ads = Ad::where('status', 'active')
                ->latest()
                ->paginate($perPage);

            // Transform the collection to only return necessary fields securely
            $ads->getCollection()->transform(function ($ad) {
                return [
                    'id'        => $ad->id,
                    'image_url' => $ad->image ? asset('storage/' . $ad->image) : null,
                    'link'      => $ad->link,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب الإعلانات بنجاح.',
                'data'    => $ads
            ], 200);
        } catch (\Throwable $e) {
            
            // Log detailed error for debugging
            Log::error('Ads API Fetch Error', [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في جلب الإعلانات، يرجى المحاولة لاحقاً.',
                // Safely return error details only in debug mode
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
