<?php

namespace App\Http\Controllers\Api\Student;

use App\Events\NewRatingReceived;
use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\Rating;
use App\Models\Session_student;
use App\Models\SlotBooking;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $user = auth()->user();
        $userId = $user->id;

        $map = [
            'call'    => CallSession::class,
            'slot'    => SlotBooking::class,
            'session' => Session_student::class,
        ];

        $modelClass = $map[$request->target_type];
        $target = $modelClass::findOrFail($request->target_id);

        $isAuthorized = false;
        if ($request->target_type === 'call' && $target->student_id == $userId) $isAuthorized = true;
        if ($request->target_type === 'slot' && $target->user_id == $userId) $isAuthorized = true;
        if ($request->target_type === 'session' && $target->user_id == $userId) $isAuthorized = true;

        if (!$isAuthorized) {
            return response()->json(['message' => 'غير مسموح لك بتقييم هذا النشاط.'], 403);
        }

        $exists = Rating::where([
            'user_id'       => $userId,
            'rateable_id'   => $request->target_id,
            'rateable_type' => $modelClass,
        ])->exists();

        if ($exists) {
            return response()->json(['message' => 'لقد قمت بتقييم هذه الجلسة مسبقاً.'], 400);
        }

        $rating = Rating::create([
            'user_id'       => $userId,
            'teacher_id'    => $request->teacher_id,
            'rating'        => $request->rating,
            'comment'       => $request->comment,
            'rateable_id'   => $request->target_id,
            'rateable_type' => $modelClass,
        ]);
        $teacher = Teacher::with('user')->find($request->teacher_id);
        if ($teacher && $teacher->user) {
            $notificationData = [
                'rating_id'    => $rating->id,
                'stars'        => $rating->rating,
                'student_name' => $user->name,
                'target_type'  => $request->target_type,
                'comment'      => $rating->comment
            ];

            try {
                broadcast(new NewRatingReceived($teacher->id, $notificationData));
                $teacher->user->notify(new \App\Notifications\DynamicNotification(
                    'تقييم جديد ⭐',
                    "قام الطالب {$user->name} بتقييم جلستك بـ {$rating->rating} نجوم.",
                    'new_rating',
                    $notificationData
                ));
            } catch (\Exception $e) {
                Log::error('Rating Notification/Broadcast Error: ' . $e->getMessage());
            }
        }
        return response()->json([
            'status'  => true,
            'message' => 'شكراً لك! تم إرسال تقييمك بنجاح.',
            'data'    => $rating
        ]);
    }
}
