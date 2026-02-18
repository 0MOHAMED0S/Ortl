<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentTracksController extends Controller
{
    /**
     * عرض قائمة المسارات (Tracks) النشطة مع Pagination
     */
    public function index(Request $request)
    {
        try {
            // 1️⃣ عدد العناصر لكل صفحة
            $perPage = $request->query('per_page', 10);

            // 2️⃣ جلب المسارات النشطة مع ترتيبها حسب الأحدث
            $tracks = Track::where('status', 'active')
                ->latest()
                ->paginate($perPage);

            // 3️⃣ تنسيق البيانات وإضافة رابط الأيقونة الكامل
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

            // 4️⃣ الرد النهائي
            return response()->json([
                'status'  => true,
                'message' => 'تم جلب المسارات بنجاح.',
                'data'    => $tracks
            ], 200);

        } catch (\Throwable $e) {
            Log::error('خطأ في جلب المسارات (Tracks): ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في جلب المسارات. حاول مرة أخرى.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
