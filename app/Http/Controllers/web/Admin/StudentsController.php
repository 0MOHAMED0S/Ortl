<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StudentsController extends Controller
{
    /**
     * عرض قائمة الطلاب مع معالجة الصور والباقات (Package +1)
     */
/**
     * عرض قائمة الطلاب مع الإحصائيات المتقدمة ومنطق الأسعار
     */
public function index(Request $request)
{
    try {
        // 1. جلب البيانات العامة
        $allPackages = Package::where('status', 'active')->get();
        $countries = \App\Models\Country::whereHas('students')->get();

        // 2. الاستعلام الأساسي للطلاب
        $query = User::where('role', 'student')
            ->with(['student.country', 'packages.package'])
            ->latest();

        // 3. الإحصائيات الشاملة
        $totalStudents = User::where('role', 'student')->count();
        $totalMinutesRegistered = UserPackage::where('status', 'active')->sum('remaining_minutes');
        $totalGifts = UserPackage::where('is_gift', true)->count();
        $activePackagesCount = UserPackage::where('status', 'active')->count();

        // --- 4. جلب تواريخ تسجيل الطلاب (أساسي لعمل الفلتر) ---
        // نقوم بجلب التواريخ فقط لجميع الطلاب لتسريع الأداء في الفلترة بالجافاسكريبت
        $allStudentDates = User::where('role', 'student')
            ->select('created_at')
            ->pluck('created_at')
            ->map(function($date) {
                return $date->format('Y-m-d'); // توحيد الصيغة
            })
            ->toArray();

        // 5. الترقيم والمعالجة للعرض في الجدول
        $studentsPaginated = $query->paginate(15);
        $studentsPaginated->getCollection()->transform(function ($user) {
            $currentActive = $user->packages->where('status', 'active')->first() ?? $user->packages->first();

            return (object)[
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'phone'           => $user->student->phone ?? $user->phone ?? 'غير مسجل',
                'avatar_initials' => mb_substr($user->name, 0, 1, 'utf-8'),
                'created_at'      => $user->created_at->toDateTimeString(),
                'created_at_human'=> $user->created_at->format('Y/m/d'),
                'profile_image'   => ($user->student && $user->student->profile_photo_path) ? asset('storage/' . $user->student->profile_photo_path) : null,
                'package_name'    => ($currentActive && $currentActive->package) ? $currentActive->package->name : 'لا يوجد باقة',
                'country_name'    => $user->student->country->name ?? 'غير محدد',
                'total_minutes'   => $user->packages->where('status', 'active')->sum('remaining_minutes'),
                'gender'          => ($user->student->gender ?? '') == 'male' ? 'ذكر' : 'أنثى',
                'qualification'   => $user->student->qualification ?? 'غير محدد',
                'job'             => $user->student->professional_status ?? 'غير محدد',
                'all_packages'    => $user->packages->map(function ($up) {
                    return [
                        'name'    => $up->package->name ?? 'باقة غير معروفة',
                        'remain'  => $up->remaining_minutes,
                        'is_gift' => (bool)$up->is_gift,
                        'date'    => \Carbon\Carbon::parse($up->created_at)->format('Y/m/d'),
                    ];
                })
            ];
        });

        // 6. العرض
        return view('dashboard.students', [
            'students'               => $studentsPaginated,
            'allPackages'            => $allPackages,
            'countries'              => $countries,
            'totalStudents'          => $totalStudents,
            'totalMinutesRegistered' => $totalMinutesRegistered,
            'totalGifts'             => $totalGifts,
            'activePackagesCount'    => $activePackagesCount,
            'allStudentDates'        => $allStudentDates, // هذا المتغير هو ما كان ناقصاً
        ]);

    } catch (\Throwable $e) {
        Log::error('Admin Students Index Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
    }
}

    /**
     * تحديث بيانات الطالب وتفاصيله الشخصية
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'qualification'       => 'nullable|string|max:255',
            'professional_status' => 'nullable|string|max:255',
            'gender'              => 'required|in:male,female',
        ]);

        try {
            $user = User::findOrFail($id);

            DB::beginTransaction();

            $user->update(['name' => $request->name]);

            $user->student()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone'               => $request->phone,
                    'qualification'       => $request->qualification,
                    'professional_status' => $request->professional_status,
                    'gender'              => $request->gender,
                ]
            );

            DB::commit();

            return redirect()->route('admin.students')->with('success', 'تم تحديث بيانات الطالب بنجاح');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Update Student Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء التحديث.');
        }
    }
    public function giftPackage(Request $request, $id)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        try {
            // 1. التأكد من أن المستخدم ليس لديه نفس هذه الباقة بحالة "نشطة" حالياً
            $alreadyHasActive = UserPackage::where('user_id', $id)
                ->where('package_id', $request->package_id)
                ->where('status', 'active')
                ->exists();

            if ($alreadyHasActive) {
                return redirect()->back()->with('error', 'هذا الطالب يمتلك هذه الباقة بالفعل وهي لا تزال نشطة.');
            }

            // 2. جلب بيانات الباقة
            $package = Package::findOrFail($request->package_id);

            // 3. إنشاء الاشتراك الجديد كهدية
            UserPackage::create([
                'user_id'           => $id,
                'package_id'        => $package->id,
                'remaining_minutes' => $package->base_minutes + $package->bonus_minutes,
                'expires_at'        => Carbon::now()->addDays($package->validity_days),
                'status'            => 'active',
                'is_gift'           => true,
            ]);

            return redirect()->back()->with('success', 'تم إهداء الباقة بنجاح! 🎁');
        } catch (\Throwable $e) {
            Log::error('Gift Package Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء عملية الإهداء.');
        }
    }
}
