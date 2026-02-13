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
            // 1️⃣ Authenticated user
            $user = $request->user();

            // 2️⃣ Student profile
            $studentProfile = $user->studentProfile;

            if (!$studentProfile) {
                return response()->json([
                    'message' => 'Student profile not found.'
                ], 404);
            }

            // 3️⃣ Country (with exchange data)
            $country = $studentProfile->country;

            if (!$country) {
                return response()->json([
                    'message' => 'Country data missing in profile.'
                ], 404);
            }

            /**
             * Assumption:
             * rate_to_usd = how much 1 USD equals in local currency
             * Example:
             * EGP → 30
             * EUR → 0.92
             */
            $rate = $country->rate_to_usd ?? 1;

            // 4️⃣ Active packages only
            $packages = Package::where('status', 'active')->get();

            // 5️⃣ Transform packages
            $formattedPackages = $packages->map(function ($package) use ($country, $rate) {

                $localPrice = $package->price * $rate;

                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'base_minutes' => $package->base_minutes,
                    'bonus_minutes' => $package->bonus_minutes,
                    'validity_days' => $package->validity_days,

                    // Prices
                    'price_usd' => (int) $package->price,
                    'local_price' => (int) round($localPrice),

                    // Currency info
                    'currency_code' => $country->currency_code,
                    'currency_symbol' => $country->currency_symbol,

                    // UI-ready
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
                'data' => $formattedPackages
            ], 200);
        } catch (\Throwable $e) {

            Log::error('Get Packages Error', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to load packages. Please try again.'
            ], 500);
        }
    }

    public function userPackages(Request $request): JsonResponse
    {
        try {

            $user = $request->user();
            $user->load('country');

            $country = $user->country;

            $userPackages = $user->packages()
                ->with('package')
                ->latest()
                ->get();

            $totalRemainingMinutes = $userPackages->sum('remaining_minutes');

            $totalOriginalMinutes = $userPackages->sum(function ($userPackage) {
                return $userPackage->package->base_minutes +
                    $userPackage->package->bonus_minutes;
            });

            $totalActiveRemainingMinutes = $userPackages
                ->where('status', 'active')
                ->sum('remaining_minutes');

            $packages = $userPackages->map(function ($userPackage) use ($country) {

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
                'data' => $packages
            ]);
        } catch (\Throwable $e) {

            // Log full error for debugging
            Log::error('User Packages Error', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching packages.'
            ], 500);
        }
    }
}
