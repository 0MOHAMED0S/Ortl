<?php

namespace App\Http\Controllers\Api\Ads;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdsController extends Controller
{
public function index(Request $request)
{
    // 1. Get active ads using pagination
    // You can also allow the 'per_page' to be dynamic from the request
    $perPage = $request->query('per_page', 10);
    $ads = Ad::where('status', 'active')
        ->latest()
        ->paginate($perPage);

    // 2. Transform the collection while preserving pagination metadata
    $ads->getCollection()->transform(function ($ad) {
        return [
            'id' => $ad->id,
            'title' => $ad->title,
            'subtitle' => $ad->subtitle, // Added to match your new schema
            'bg_color' => $ad->bg_color, // Added to match your new schema
            'image_url' => $ad->image ? asset('storage/' . $ad->image) : null,
        ];
    });

    // 3. Return the paginated object
    return response()->json([
        'message' => 'Ads retrieved successfully',
        'data' => $ads
    ], 200);
}
}
