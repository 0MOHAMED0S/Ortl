<?php

namespace App\Http\Controllers\Api\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    public function index()
    {
        // 1. Get only active ads
        $ads = Ad::where('status', 'active')
            ->latest()
            ->get();

        // 2. Format data (Add full image URL)
        $formattedAds = $ads->map(function ($ad) {
            return [
                'id' => $ad->id,
                'title' => $ad->title,
                'image_url' => asset('storage/' . $ad->image), // Generate full URL
            ];
        });

        return response()->json([
            'message' => 'Ads retrieved successfully',
            'data' => $formattedAds
        ], 200);
    }
}
