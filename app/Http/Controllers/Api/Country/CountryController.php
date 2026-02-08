<?php

namespace App\Http\Controllers\Api\Country;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        try {
            $countries = Country::select('id', 'name', 'phone_code', 'code')->get();

            return response()->json([
                'message' => 'Countries retrieved successfully',
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
