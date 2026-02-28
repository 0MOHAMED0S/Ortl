<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
public function index(Request $request)
{
    try {
        // الإحصائيات العلوية
        $totalCountries = Country::count();
        $activeCountries = Country::where('status', true)->count();

        // الاستعلام الأساسي
        $query = Country::query();

        // 1. تطبيق البحث (Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('phone_code', 'LIKE', "%{$search}%");
            });
        }

        // 2. تطبيق الفلتر (Filter: active / inactive)
        if ($request->filled('filter') && $request->filter !== 'all') {
            $status = $request->filter === 'active' ? true : false;
            $query->where('status', $status);
        }

        // 3. الترتيب (Order): الدول المفعلة أولاً، ثم حسب الاسم أبجدياً
        $query->orderByDesc('status')->orderBy('name');

        // جلب الدول مع التصفح (20 دولة في الصفحة) مع الحفاظ على البارامترات في الرابط
        $countries = $query->paginate(20)->appends($request->query());

        return view('dashboard.countries', compact('countries', 'totalCountries', 'activeCountries'));

    } catch (\Throwable $e) {
        Log::error('Admin Countries Index Error: ' . $e->getMessage());
        return back()->with('error', 'حدث خطأ أثناء تحميل بيانات الدول.');
    }
}

    public function toggleStatus(Country $country)
    {
        try {
            // عكس الحالة الحالية
            $country->update(['status' => !$country->status]);

            $statusText = $country->status ? 'تفعيل' : 'إيقاف';
            return back()->with('success', "تم {$statusText} دولة {$country->name} بنجاح.");

        } catch (\Throwable $e) {
            Log::error('Toggle Country Status Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء محاولة تحديث حالة الدولة.');
        }
    }
}
