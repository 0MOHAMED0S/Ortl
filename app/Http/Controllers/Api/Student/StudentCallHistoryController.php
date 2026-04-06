<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CallSession;

class StudentCallHistoryController extends Controller
{
    public function index()
    {
        $studentId = auth()->id();

        $calls = CallSession::where('student_id', $studentId)
            ->with([
                'teacher.user:id,name',
                // Eager load the rating specifically for each call session
                'ratings' => function ($query) use ($studentId) {
                    $query->where('user_id', $studentId);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $calls->getCollection()->transform(function ($call) {
            // Get the first rating (since one call has one rating)
            $rating = $call->ratings->first();

            return [
                'id' => $call->id,
                'teacher_name' => $call->teacher->user->name ?? 'Unknown Teacher',
                'teacher_id' => $call->teacher_id,
                'status' => $call->status,
                'duration_minutes' => $call->duration_minutes,
                'started_at' => $call->started_at ? $call->started_at->format('Y-m-d H:i:s') : null,
                'created_at' => $call->created_at->format('Y-m-d H:i:s'),
                // 🎥 إضافة رابط التسجيل السحابي هنا
                'recording_url' => $call->recording_url,
                // Display rating summary in the list
                'is_rated' => (bool)$rating,
                'rating_stars' => $rating ? $rating->rating : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $calls
        ]);
    }

    public function show($id)
    {
        $studentId = auth()->id();

        $call = CallSession::where('student_id', $studentId)
            ->with([
                'teacher.user:id,name',
                'teacher.application:id,full_name,profile_photo_path,qualification',
                // Explicitly get the rating for this specific call session
                'ratings' => function ($query) use ($studentId) {
                    $query->where('user_id', $studentId);
                }
            ])
            ->findOrFail($id);

        $rating = $call->ratings->first();

        return response()->json([
            'status' => true,
            'data'   => [
                'call_id' => $call->id,
                'status'  => $call->status,

                'teacher' => [
                    'id'            => $call->teacher_id,
                    'name'          => $call->teacher->user->name ?? $call->teacher->application->full_name ?? 'N/A',
                    'photo'         => $call->teacher->application->profile_photo_path ? asset('storage/' . $call->teacher->application->profile_photo_path) : null,
                    'qualification' => $call->teacher->application->qualification ?? null,
                ],

                'session_details' => [
                    'channel_name'     => $call->channel_name,
                    'duration_minutes' => $call->duration_minutes,
                    // 🎥 إضافة رابط التسجيل السحابي هنا للتفاصيل أيضاً
                    'recording_url'    => $call->recording_url,
                    'timeline' => [
                        'requested_at' => $call->created_at->format('Y-m-d H:i:s'),
                        'started_at'   => $call->started_at ? $call->started_at->format('H:i:s') : null,
                        'ended_at'     => $call->ended_at ? $call->ended_at->format('H:i:s') : null,
                        'date'         => $call->created_at->format('Y-m-d'),
                    ]
                ],

                // 3. Detailed Rating info
                'rating' => $rating ? [
                    'id'         => $rating->id,
                    'stars'      => $rating->rating,
                    'comment'    => $rating->comment,
                    'created_at' => $rating->created_at->format('Y-m-d H:i:s'),
                ] : null,
            ]
        ]);
    }
}
