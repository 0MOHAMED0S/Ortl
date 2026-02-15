<?php

namespace App\Http\Controllers\Api\Country;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
public function index(Request $request)
{
    try {
        // 1️⃣ Initialize the query
        $query = Country::query()->select('id', 'name', 'phone_code', 'code');

        // 2️⃣ Optional: Add search functionality (good for long lists)
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone_code', 'LIKE', "%{$searchTerm}%");
        }

        // 3️⃣ Paginate
        $perPage = $request->query('per_page', 15);
        $countries = $query->paginate($perPage);

        return response()->json([
            'message' => 'Countries retrieved successfully',
            // Return the paginated object directly
            'data' => $countries
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Failed to retrieve countries',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
