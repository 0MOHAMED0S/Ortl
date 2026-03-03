<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\CallSession;
use App\Models\SlotBooking;
use App\Models\Session_student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherRatingController extends Controller
{
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['status' => false, 'message' => 'Teacher profile not found.'], 404);
        }

        // 1. Map frontend types to Model Classes
        $typeMap = [
            'call'    => CallSession::class,
            'slot'    => SlotBooking::class,
            'session' => Session_student::class,
        ];

        // 2. Base Query
        $query = Rating::where('teacher_id', $teacher->id);

        // 3. 🎯 Apply Filter if requested (e.g., ?type=call)
        if ($request->has('type') && array_key_exists($request->type, $typeMap)) {
            $query->where('rateable_type', $typeMap[$request->type]);
        }

        // 4. Calculate Stats (Stats usually stay global to show total context)
        $stats = Rating::where('teacher_id', $teacher->id)
            ->select(
                DB::raw('AVG(rating) as avg_rating'),
                DB::raw('COUNT(*) as total_reviews'),
                DB::raw("SUM(CASE WHEN rateable_type LIKE '%CallSession' THEN 1 ELSE 0 END) as calls_count"),
                DB::raw("SUM(CASE WHEN rateable_type LIKE '%SlotBooking' THEN 1 ELSE 0 END) as slots_count"),
                DB::raw("SUM(CASE WHEN rateable_type LIKE '%Session_student' THEN 1 ELSE 0 END) as sessions_count")
            )->first();

        // 5. Paginated Results
        $ratings = $query->with(['user:id,name', 'rateable'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        // 6. Transform Data
        $ratings->getCollection()->transform(function ($rating) {
            return [
                'id'          => $rating->id,
                'rating'      => (int) $rating->rating,
                'comment'     => $rating->comment,
                'date'        => $rating->created_at->format('Y-m-d H:i'),
                'student'     => [
                    'id'   => $rating->user->id ?? null,
                    'name' => $rating->user->name ?? 'Student',
                ],
                'source_type' => strtolower(class_basename($rating->rateable_type)),
                'source_id'   => $rating->rateable_id,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => [
                'summary' => [
                    'average_rating' => round($stats->avg_rating, 2) ?? 0,
                    'total_reviews'  => (int) $stats->total_reviews,
                    'breakdown' => [
                        'calls'    => (int) $stats->calls_count,
                        'slots'    => (int) $stats->slots_count,
                        'sessions' => (int) $stats->sessions_count,
                    ]
                ],
                'filter_applied' => $request->get('type', 'all'),
                'reviews'        => $ratings
            ]
        ]);
    }
}
