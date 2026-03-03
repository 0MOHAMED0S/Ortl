<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\Rating;
use App\Models\Session_student;
use App\Models\SlotBooking;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'teacher_id'   => 'required|exists:teachers,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:500',
            'target_id'    => 'required|integer',
            'target_type'  => 'required|in:call,slot,session',
        ]);

        $userId = auth()->id();

        // 1. Map type to Model
        $map = [
            'call'    => CallSession::class,
            'slot'    => SlotBooking::class,
            'session' => Session_student::class,
        ];

        $modelClass = $map[$request->target_type];

        // 2. Fetch target and verify ownership/participation
        $target = $modelClass::findOrFail($request->target_id);

        // Security Check: Ensure the student actually belongs to this record
        $isAuthorized = false;
        if ($request->target_type === 'call' && $target->student_id == $userId) $isAuthorized = true;
        if ($request->target_type === 'slot' && $target->user_id == $userId) $isAuthorized = true;
        if ($request->target_type === 'session' && $target->user_id == $userId) $isAuthorized = true;

        if (!$isAuthorized) {
            return response()->json(['message' => 'غير مسموح لك بتقييم هذا النشاط.'], 403);
        }

        // 3. Prevent Duplicate Ratings
        $exists = Rating::where([
            'user_id'       => $userId,
            'rateable_id'   => $request->target_id,
            'rateable_type' => $modelClass,
        ])->exists();

        if ($exists) {
            return response()->json(['message' => 'لقد قمت بتقييم هذه الجلسة مسبقاً.'], 400);
        }

        // 4. Create Rating
        $rating = Rating::create([
            'user_id'       => $userId,
            'teacher_id'    => $request->teacher_id,
            'rating'        => $request->rating,
            'comment'       => $request->comment,
            'rateable_id'   => $request->target_id,
            'rateable_type' => $modelClass,
        ]);

        // 5. Update Teacher Average (Optional but recommended)
        // $this->updateTeacherRating($request->teacher_id);

        return response()->json([
            'status'  => true,
            'message' => 'شكراً لك! تم إرسال تقييمك بنجاح.',
            'data'    => $rating
        ]);
    }
}
