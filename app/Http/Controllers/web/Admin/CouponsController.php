<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Coupon\StoreCouponRequest;
use App\Http\Requests\Admin\Coupon\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Support\Facades\Log;

class CouponsController extends Controller
{
    public function index()
    {
        try {
            $coupons = Coupon::latest()->get();
            return view('dashboard.coupons', compact('coupons'));
        } catch (\Throwable $e) {
            Log::error('Coupons index error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل الكوبونات');
        }
    }

    public function store(StoreCouponRequest $request)
    {
        try {
            Coupon::create([
                'code' => strtoupper($request->code),
                'percent' => $request->percent,
                'limit' => $request->limit,
                'expiry_date' => $request->expiry_date,
                'status' => $request->has('is_active') ? 'active' : 'inactive',
                'used' => 0,
            ]);

            return redirect()->back()->with('success', 'تم إنشاء الكوبون بنجاح');
        } catch (\Throwable $e) {
            Log::error('Coupon store error', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الكوبون');
        }
    }

    public function update(UpdateCouponRequest $request, $id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            // Status toggle only
            if ($request->has('status_toggle')) {
                $coupon->update([
                    'status' => $coupon->status === 'active' ? 'inactive' : 'active',
                ]);

                return redirect()->back()->with('success', 'تم تحديث حالة الكوبون');
            }

            // Normal update
            $coupon->update([
                'code' => strtoupper($request->code),
                'percent' => $request->percent,
                'limit' => $request->limit,
                'expiry_date' => $request->expiry_date,
            ]);

            return redirect()->back()->with('success', 'تم تعديل بيانات الكوبون بنجاح');
        } catch (\Throwable $e) {
            Log::error('Coupon update error', [
                'coupon_id' => $id,
                'data' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تعديل الكوبون');
        }
    }

    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();

            return redirect()->back()->with('success', 'تم حذف الكوبون');
        } catch (\Throwable $e) {
            Log::error('Coupon delete error', [
                'coupon_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الكوبون');
        }
    }
}
