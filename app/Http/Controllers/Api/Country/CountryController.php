<?php

namespace App\Http\Controllers\Api\Country;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1️⃣ تهيئة الاستعلام وجلب الدول المفعلة فقط
            $query = Country::query()
                ->select('id', 'name', 'phone_code', 'code')
                ->where('status', true); // ✅ شرط أن تكون الدولة مفعلة

            // 2️⃣ البحث في الاسم، الكود، أو كود الهاتف
            if ($request->has('search')) {
                $searchTerm = $request->search;

                // ✅ استخدام دالة (Closure) لتجميع شروط البحث بالأقواس في الـ SQL
                // حتى لا يتداخل orWhere مع شرط الـ status
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone_code', 'LIKE', "%{$searchTerm}%");
                });
            }

            // 3️⃣ Pagination
            $perPage = $request->query('per_page', 15);
            $countries = $query->paginate($perPage);

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب قائمة الدول المفعلة بنجاح.',
                'data'    => $countries
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Failed to retrieve countries: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في جلب قائمة الدول. حاول مرة أخرى.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
