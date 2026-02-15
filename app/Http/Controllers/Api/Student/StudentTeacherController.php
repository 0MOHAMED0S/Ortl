<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Teacher_application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentTeacherController extends Controller
{
    public function index()
    {
        try {
            // 1️⃣ Fetch only approved teachers with relationships
            $teachers = Teacher_application::where('status', 'approved')
                ->with('profile.user') // Eager load relationships
                ->get();

            // 2️⃣ Format data
            $formattedTeachers = $teachers->map(function ($teacher) {

                // Name: Prefer User account name, fallback to Application name
                $name = optional(optional($teacher->profile)->user)->name ?? $teacher->full_name;

                // Photo: Prefer profile photo, fallback to UI Avatar
                $photoPath = optional($teacher->profile)->profile_photo_path ?? null;
                $photoUrl = $photoPath
                    ? asset('storage/' . $photoPath)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=128';

                return [
                    'id' => $teacher->id,
                    'name' => $name,
                    'photo_url' => $photoUrl,
                    'qualification' => $teacher->qualification,
                    'country' => $teacher->origin_country,
                    'languages' => $teacher->languages,       // Cast to array in model
                    'specialties' => $teacher->specialties,   // Cast to array
                    'experience_years' => $teacher->experience_years,
                    'about' => $teacher->ijazas_text,
                ];
            });

            return response()->json([
                'message' => 'Teachers retrieved successfully',
                'data' => $formattedTeachers
            ], 200);
        } catch (\Throwable $e) {

            // Log the error for debugging
            Log::error('Failed to fetch teachers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve teachers. Please try again later.'
            ], 500);
        }
    }

    /**
 * Get a specific teacher profile by ID.
 */
public function show($id)
{
    try {
        // 1️⃣ Find the approved teacher with their account and application details
        $teacher = Teacher::with(['user', 'application.tracks'])->find($id);

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        // 2️⃣ Formatting Data
        $name = $teacher->user->name ?? $teacher->application->full_name;

        $photoUrl = $teacher->profile_photo_path
            ? asset('storage/' . $teacher->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=200';

        $profile = [
            'id'               => $teacher->id,
            'user_id'          => $teacher->user_id,
            'name'             => $name,
            'photo_url'        => $photoUrl,
            'qualification'    => $teacher->application->qualification,
            'country'          => $teacher->application->origin_country,
            'languages'        => $teacher->application->languages,
            'experience_years' => $teacher->application->experience_years,
            'about'            => $teacher->application->ijazas_text,
            'minutes_balance'  => $teacher->minutes,
            'specialties'      => $teacher->application->tracks->map(function($track) {
                return [
                    'id'   => $track->id,
                    'name' => $track->name,
                ];
            }),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Teacher profile retrieved successfully',
            'data'    => $profile
        ], 200);

    } catch (\Throwable $e) {
        Log::error("API Error: Fetching teacher profile ($id): " . $e->getMessage());

        return response()->json([
            'status'  => false,
            'message' => 'An error occurred while fetching the profile'
        ], 500);
    }
}
}
