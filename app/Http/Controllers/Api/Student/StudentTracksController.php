<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentTracksController extends Controller
{
    /**
     * Display a paginated listing of active tracks.
     */
    public function index(Request $request)
    {
        try {
            // 1. Fetch active tracks with pagination
            // Default to 10 items per page if not specified
            $perPage = $request->query('per_page', 10);

            $tracks = Track::where('status', 'active')
                ->latest()
                ->paginate($perPage);

            // 2. Transform the data to include full icon URLs
            $tracks->getCollection()->transform(function ($track) {
                return [
                    'id'              => $track->id,
                    'name'            => $track->name,
                    'target_group'    => $track->target_group,
                    'marketing_value' => $track->marketing_value,
                    'description'     => $track->description,
                    'icon_url'        => $track->icon ? asset('storage/' . $track->icon) : null,
                    'created_at'      => $track->created_at->format('Y-m-d'),
                ];
            });

            // 3. Return a standardized JSON response
            return response()->json([
                'status'  => true,
                'message' => 'Tracks retrieved successfully',
                'data'    => $tracks
            ], 200);

        } catch (\Throwable $e) {
            Log::error('API Tracks Fetch Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve tracks',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
