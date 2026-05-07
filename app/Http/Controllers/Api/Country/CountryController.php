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
            $query = Country::query()
                ->select('id', 'name', 'phone_code', 'code')
                ->where('status', true);
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('phone_code', 'LIKE', "%{$searchTerm}%");
                });
            }
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
