<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\DeleteDaySlotsRequest;
use App\Http\Requests\Teacher\SetAvailabilityRequest;
use App\Models\TeacherSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherSlotController extends Controller
{
    public function setAvailability(SetAvailabilityRequest $request)
    {
        // التحقق يتم تلقائياً قبل دخول الدالة
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json([
                'status'  => false,
                'message' => 'عذراً، يجب إكمال ملف المعلم أولاً.'
            ], 403);
        }

        try {
            $date = $request->date;
            $startTime = Carbon::parse($date . ' ' . $request->start_time);
            $endTime   = Carbon::parse($date . ' ' . $request->end_time);
            $duration = 30;

            return DB::transaction(function () use ($startTime, $endTime, $teacher, $date, $duration) {

                $existingSlots = TeacherSlot::where('teacher_id', $teacher->id)
                    ->where('date', $date)
                    ->pluck('start_time')
                    ->toArray();

                $slotsToInsert = [];
                $tempStart = $startTime->copy();

                while ($tempStart->copy()->addMinutes($duration) <= $endTime) {
                    $slotStart = $tempStart->format('H:i:s');
                    $slotEnd   = $tempStart->copy()->addMinutes($duration)->format('H:i:s');

                    if (!in_array($slotStart, $existingSlots)) {
                        $slotsToInsert[] = [
                            'teacher_id' => $teacher->id,
                            'date'       => $date,
                            'start_time' => $slotStart,
                            'end_time'   => $slotEnd,
                            'is_booked'  => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $existingSlots[] = $slotStart;
                    }
                    $tempStart->addMinutes($duration);
                }

                if (count($slotsToInsert) > 0) {
                    TeacherSlot::insert($slotsToInsert);
                }

                return response()->json([
                    'status'  => true,
                    'message' => count($slotsToInsert) > 0
                        ? "تم إضافة " . count($slotsToInsert) . " حصة بنجاح."
                        : "المواعيد المختارة موجودة بالفعل مسبقاً.",
                    'data'    => [
                        'added_count' => count($slotsToInsert),
                        'date'        => $date
                    ]
                ], 201);
            });
        } catch (\Throwable $e) {
            Log::error('Teacher Availability Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء معالجة الطلب، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }
    public function getMySlots(Request $request)
    {
        try {
            $user = $request->user();
            $teacher = $user->teacherProfile;

            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'عذراً، لم يتم العثور على ملف تعريف المعلم.'
                ], 404);
            }

            // 1️⃣ تحديد عدد العناصر في الصفحة (مثلاً 15 سجل)
            $perPage = $request->get('per_page', 20);

            // 2️⃣ جلب البيانات مع التصفح (Pagination)
            // نستخدم simplePaginate إذا كنا لا نحتاج لعدد الصفحات الكلي (أسرع) أو paginate العادي
            $slotsQuery = TeacherSlot::where('teacher_id', $teacher->id)
                ->where('date', '>=', now()->toDateString())
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc');

            $slotsPaginator = $slotsQuery->paginate($perPage);

            // 3️⃣ تجميع النتائج حسب التاريخ بعد الجلب (للحفاظ على شكل البيانات المريح للـ Mobile)
            $groupedSlots = $slotsPaginator->getCollection()->groupBy('date');

            // 4️⃣ بناء الاستجابة الاحترافية
            return response()->json([
                'status' => true,
                'message' => 'تم جلب جدول المواعيد بنجاح.',
                'data' => [
                    'calendar' => $groupedSlots, // المواعيد مجمعة حسب اليوم
                    'pagination' => [
                        'total'        => $slotsPaginator->total(),
                        'count'        => $slotsPaginator->count(),
                        'per_page'     => $slotsPaginator->perPage(),
                        'current_page' => $slotsPaginator->currentPage(),
                        'total_pages'  => $slotsPaginator->lastPage(),
                        'next_page_url' => $slotsPaginator->nextPageUrl(),
                        'prev_page_url' => $slotsPaginator->previousPageUrl(),
                    ]
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Teacher GetMySlots Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب المواعيد، يرجى المحاولة لاحقاً.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function deleteSlot(Request $request, $id)
    {
        try {
            $teacher = $request->user()->teacherProfile;

            // البحث عن الموعد والتأكد أنه يخص المعلم المسجل دخوله
            $slot = TeacherSlot::where('id', $id)
                ->where('teacher_id', $teacher->id)
                ->first();

            if (!$slot) {
                return response()->json([
                    'status' => false,
                    'message' => 'الموعد غير موجود أو لا تملك صلاحية حذفه.'
                ], 404);
            }

            // منع حذف الموعد إذا كان محجوزاً من قبل طالب
            if ($slot->is_booked) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يمكن حذف موعد محجوز بالفعل.'
                ], 422);
            }

            $slot->delete();

            return response()->json([
                'status' => true,
                'message' => 'تم حذف الموعد بنجاح.'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء الحذف.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function deleteDaySlots(DeleteDaySlotsRequest $request)
    {
        // 1️⃣ جلب بروفايل المعلم
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json([
                'status' => false,
                'message' => 'عذراً، لم يتم العثور على ملف المعلم.'
            ], 404);
        }

        try {
            // 2️⃣ التأكد من وجود مواعيد قابلة للحذف قبل البدء
            $query = TeacherSlot::where('teacher_id', $teacher->id)
                ->where('date', $request->date)
                ->where('is_booked', false);

            $slotsCount = $query->count();

            if ($slotsCount === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا توجد مواعيد متاحة (غير محجوزة) للحذف في هذا التاريخ.'
                ], 404);
            }

            // 3️⃣ تنفيذ عملية الحذف
            $query->delete();

            return response()->json([
                'status' => true,
                'message' => "تم حذف $slotsCount موعد بنجاح.",
                'data' => [
                    'deleted_count' => $slotsCount,
                    'date' => $request->date
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Bulk Delete Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء محاولة الحذف الجماعي، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }
}
