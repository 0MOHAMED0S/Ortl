<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Package\StorePackageRequest;
use App\Http\Requests\Admin\Package\UpdatePackageRequest;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function index()
    {
        try {
            // جلب الباقات مع عدد المشتركين الحقيقيين في كل باقة
            $packages = Package::withCount(['userPackages as subscribers_count'])
                ->latest()
                ->get();

            $totalPackages    = $packages->count();
            $activePackages   = $packages->where('status', 'active')->count();
            $inactivePackages = $packages->where('status', 'inactive')->count();

            // إجمالي المشتركين الحقيقيين (Unique Users) في كل الباقات النشطة
            $totalSubscribers = \App\Models\UserPackage::distinct('user_id')->count();

            return view('dashboard.packages', compact(
                'packages',
                'totalPackages',
                'activePackages',
                'inactivePackages',
                'totalSubscribers'
            ));
        } catch (\Throwable $e) {
            Log::error('Packages Index Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل الباقات');
        }
    }

    public function store(StorePackageRequest $request)
    {
        try {
            Package::create(
                $request->validated() + ['status' => 'active']
            );

            return redirect()
                ->route('packages.index')
                ->with('success', 'تم إنشاء الباقة بنجاح');
        } catch (\Throwable $e) {
            Log::error('Package Store Error', [
                'data'    => $request->validated(),
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الباقة');
        }
    }

    public function update(UpdatePackageRequest $request, Package $package)
    {
        try {
            $package->update($request->validated());

            return redirect()
                ->route('packages.index')
                ->with('success', 'تم تحديث الباقة بنجاح');
        } catch (\Throwable $e) {
            Log::error('Package Update Error', [
                'package_id' => $package->id,
                'data'       => $request->validated(),
                'message'    => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الباقة');
        }
    }

    /**
     * حذف الباقة فقط إذا لم تكن مرتبطة بأي مستخدمين
     */
    public function destroy(Package $package)
    {
        try {
            // التحقق مما إذا كانت الباقة مستخدمة (مرتبطة بجدول user_packages)
            if ($package->userPackages()->exists()) {
                return back()->with('error', 'عفواً، لا يمكن حذف هذه الباقة لوجود مشتركين (حاليين أو سابقين) مرتبطين بها. بدلاً من ذلك، يمكنك تغيير حالتها إلى "معطلة".');
            }

            // إذا كان لديك جدول آخر مثل الطلبات (orders) يمكنك إضافة الشرط أيضاً:
            // if ($package->orders()->exists()) { ... }

            // إذا لم تكن مرتبطة بأي شيء، قم بحذفها
            $package->delete();

            return redirect()
                ->route('packages.index')
                ->with('success', 'تم حذف الباقة بنجاح من النظام');

        } catch (\Throwable $e) {
            Log::error('Package Delete Error', [
                'package_id' => $package->id,
                'message'    => $e->getMessage(),
            ]);

            return back()->with('error', 'حدث خطأ غير متوقع أثناء محاولة حذف الباقة');
        }
    }
}
