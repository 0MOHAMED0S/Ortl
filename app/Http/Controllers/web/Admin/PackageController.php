<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Package\StorePackageRequest;
use App\Http\Requests\Admin\Package\UpdatePackageRequest;
use App\Models\package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function index()
    {
        try {
            $packages = Package::latest()->get();

            // Dynamic Stats
            $totalPackages   = $packages->count();
            $activePackages  = $packages->where('status', 'active')->count();
            $inactivePackages = $packages->where('status', 'inactive')->count();

            // Temporary Fake Subscribers Count
            $totalSubscribers = 1250;

            return view('dashboard.packages', compact(
                'packages',
                'totalPackages',
                'activePackages',
                'inactivePackages',
                'totalSubscribers'
            ));
        } catch (\Throwable $e) {

            Log::error('Packages Index Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

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
}
