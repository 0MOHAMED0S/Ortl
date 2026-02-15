<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class StudentPackageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $studentProfile = $user->studentProfile;

            if (!$studentProfile || !$studentProfile->country) {
                return response()->json([
                    'message' => 'Student profile or country data not found.'
                ], 404);
            }

            $country = $studentProfile->country;
            $rate = $country->rate_to_usd ?? 1;

            $perPage = $request->query('per_page', 10);
            $packages = package::where('status', 'active')->paginate($perPage);

            $packages->getCollection()->transform(function ($package) use ($country, $rate) {
                $localPrice = $package->price * $rate;

                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'base_minutes' => $package->base_minutes,
                    'bonus_minutes' => $package->bonus_minutes,
                    'validity_days' => $package->validity_days,
                    'price_usd' => (int) $package->price,
                    'local_price' => (int) round($localPrice),
                    'currency_code' => $country->currency_code,
                    'currency_symbol' => $country->currency_symbol,
                    'display_price' => sprintf(
                        '%s %s',
                        $country->currency_symbol,
                        number_format($localPrice, 0)
                    ),
                ];
            });

            return response()->json([
                'message' => 'Packages retrieved successfully.',
                'user_country' => [
                    'name' => $country->name,
                    'currency' => $country->currency_code,
                    'rate_to_usd' => $rate,
                ],
                'packages' => $packages
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Get Packages Pagination Error', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to load packages.'
            ], 500);
        }
    }

    public function userPackages(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load('country');
            $country = $user->country;

            // 1️⃣ Get the base query
            $query = $user->packages()
                ->with('package')
                ->latest();

            // 2️⃣ Calculate Summary Totals (on ALL packages before pagination)
            $allUserPackages = $query->get(); // Fetch full list for totals

            $totalRemainingMinutes = $allUserPackages->sum('remaining_minutes');
            $totalOriginalMinutes = $allUserPackages->sum(function ($up) {
                return $up->package->base_minutes + $up->package->bonus_minutes;
            });
            $totalActiveRemainingMinutes = $allUserPackages
                ->where('status', 'active')
                ->sum('remaining_minutes');

            // 3️⃣ Paginate the results
            $perPage = $request->query('per_page', 10);
            $paginatedPackages = $query->paginate($perPage);

            // 4️⃣ Transform only the items on the current page
            $paginatedPackages->getCollection()->transform(function ($userPackage) use ($country) {
                $package = $userPackage->package;
                $localPrice = $country
                    ? $package->price * $country->rate_to_usd
                    : $package->price;

                return [
                    'id' => $userPackage->id,
                    'remaining_minutes' => $userPackage->remaining_minutes,
                    'expires_at' => $userPackage->expires_at,
                    'status' => $userPackage->status,
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price_usd' => $package->price,
                        'price_local' => round($localPrice, 2),
                        'currency' => $country?->currency_code ?? 'USD',
                        'currency_symbol' => $country?->currency_symbol ?? '$',
                        'base_minutes' => $package->base_minutes,
                        'bonus_minutes' => $package->bonus_minutes,
                        'validity_days' => $package->validity_days,
                        'description' => $package->description,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'country' => $country?->name ?? 'Default (USD)',
                'summary' => [
                    'total_original_minutes' => $totalOriginalMinutes,
                    'total_remaining_minutes' => $totalRemainingMinutes,
                    'total_active_remaining_minutes' => $totalActiveRemainingMinutes,
                ],
                'packages' => $paginatedPackages // Includes data + meta
            ]);
        } catch (\Throwable $e) {
            Log::error('User Packages Pagination Error', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching packages.'
            ], 500);
        }
    }
}
