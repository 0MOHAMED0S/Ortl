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

            // Validate and apply search term securely
            if ($request->has('search') && is_string($request->search)) {
                // Limit search string length to prevent database slow query attacks
                $searchTerm = substr(trim($request->search), 0, 50);
                
                if (!empty($searchTerm)) {
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('phone_code', 'LIKE', "%{$searchTerm}%");
                    });
                }
            }

            // Validate and cap the 'per_page' parameter to prevent overloading the database (Max 50)
            $perPage = $request->query('per_page', 15);
            $perPage = is_numeric($perPage) ? (int) $perPage : 15;
            $perPage = max(1, min(50, $perPage));

            $countries = $query->paginate($perPage);

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب قائمة الدول المفعلة بنجاح.',
                'data'    => $countries
            ], 200);
        } catch (\Throwable $e) {
            
            // Log detailed error for debugging
            Log::error('Countries API Fetch Error', [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'فشل في جلب قائمة الدول. حاول مرة أخرى.',
                // Safely return error details only in debug mode to avoid leaking sensitive DB info
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
