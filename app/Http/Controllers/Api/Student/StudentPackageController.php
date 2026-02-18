<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\PackagePriceRequest;
use App\Models\Coupon;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class StudentPackageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $studentProfile = $user->studentProfile;

            if (!$studentProfile || !$studentProfile->country) {
                return response()->json([
                    'status' => false,
                    'message' => 'ملف الطالب أو بيانات الدولة غير موجودة.'
                ], 404);
            }

            $country = $studentProfile->country;
            $rate = $country->rate_to_usd ?? 1;

            $perPage = $request->query('per_page', 10);
            $packages = Package::where('status', 'active')->paginate($perPage);

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
                'status' => true,
                'message' => 'تم استرجاع الباقات بنجاح.',
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
                'status' => false,
                'message' => 'فشل في تحميل الباقات.'
            ], 500);
        }
    }

    public function userPackages(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load('country');
            $country = $user->country;

            $query = $user->packages()->with('package')->latest();
            $allUserPackages = $query->get();

            $totalRemainingMinutes = $allUserPackages->sum('remaining_minutes');
            $totalOriginalMinutes = $allUserPackages->sum(function ($up) {
                return $up->package->base_minutes + $up->package->bonus_minutes;
            });
            $totalActiveRemainingMinutes = $allUserPackages
                ->where('status', 'active')
                ->sum('remaining_minutes');

            $perPage = $request->query('per_page', 10);
            $paginatedPackages = $query->paginate($perPage);

            $paginatedPackages->getCollection()->transform(function ($userPackage) use ($country) {
                $package = $userPackage->package;
                $localPrice = $country ? $package->price * $country->rate_to_usd : $package->price;

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
                'status' => true,
                'message' => 'تم استرجاع باقات المستخدم بنجاح.',
                'country' => $country?->name ?? 'Default (USD)',
                'summary' => [
                    'total_original_minutes' => $totalOriginalMinutes,
                    'total_remaining_minutes' => $totalRemainingMinutes,
                    'total_active_remaining_minutes' => $totalActiveRemainingMinutes,
                ],
                'packages' => $paginatedPackages
            ], 200);
        } catch (\Throwable $e) {
            Log::error('User Packages Pagination Error', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب باقات المستخدم.'
            ], 500);
        }
    }



public function getPrice(PackagePriceRequest $request, $id): JsonResponse
{
    try {
        $user = $request->user();

        // 1️⃣ التحقق من وجود الباقة
        $package = Package::find($id);
        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'الباقة غير موجودة.'
            ], 404);
        }

        // 2️⃣ تحويل العملة حسب دولة المستخدم
        $basePrice = $package->price;
        $rate = $user->country?->rate_to_usd ?? 1;
        $convertedPrice = $basePrice * $rate;

        $discountAmount = 0;
        $discountPercentage = 0; // القيمة الافتراضية لنسبة الخصم
        $couponCode = $request->input('coupon');

        // 3️⃣ منطق كود الخصم
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();

            if (!$coupon || $coupon->status !== 'active') {
                return response()->json([
                    'status' => false,
                    'message' => 'كود الخصم غير صحيح أو غير مفعل.'
                ], 422);
            }

            if ($coupon->used >= $coupon->limit) {
                return response()->json([
                    'status' => false,
                    'message' => 'تم تجاوز الحد الأقصى لاستخدام هذا الكود.'
                ], 422);
            }

            if ($coupon->expiry_date && $coupon->expiry_date->isPast()) {
                return response()->json([
                    'status' => false,
                    'message' => 'كود الخصم منتهي الصلاحية.'
                ], 422);
            }

            // جلب نسبة الخصم وحساب القيمة
            $discountPercentage = $coupon->percent;
            $discountAmount = ($convertedPrice * $discountPercentage) / 100;
        }

        $finalPrice = max($convertedPrice - $discountAmount, 0);

        return response()->json([
            'status'   => true,
            'message'  => 'تم احتساب السعر بنجاح.',
            'data'     => [
                'package_id'       => $package->id,
                'package_name'     => $package->name,
                'original_price'   => round($basePrice, 2),
                'country_currency' => $user->country?->currency_code ?? 'USD',
                'converted_price'  => round($convertedPrice, 2),
                'discount_percent' => $discountPercentage, // النسبة المئوية (مثلاً: 20)
                'discount_amount'  => round($discountAmount, 2),
                'final_price'      => round($finalPrice, 2),
                'coupon_used'      => $couponCode,
            ]
        ], 200);

    } catch (\Throwable $e) {
        Log::error('Package Price Error', [
            'user_id' => optional($request->user())->id,
            'package_id' => $id,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'حدث خطأ ما، يرجى المحاولة لاحقاً.',
            'error'   => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

}
