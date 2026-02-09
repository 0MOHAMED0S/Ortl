<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Profile\UpdatePasswordProfileRequest;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Models\ContactSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        // 2. Fetch the settings (get the first row)
        $contact = ContactSetting::first();

        // 3. Pass the variable to the view
        return view('dashboard.profile', compact('contact'));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = Auth::user();

            $user->update([
                'name'  => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->back()->with('success', 'تم تحديث البيانات الشخصية بنجاح');
        } catch (\Throwable $e) {

            Log::error('Profile update error', [
                'user_id' => Auth::id(),
                'data' => $request->only(['name', 'email']),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث البيانات الشخصية');
        }
    }

    /**
     * Update the password.
     */
    public function updatePassword(UpdatePasswordProfileRequest $request)
    {
        try {
            $user = Auth::user();

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return redirect()->back()->with('success', 'تم تغيير كلمة المرور بنجاح');
        } catch (\Throwable $e) {

            Log::error('Password update error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تغيير كلمة المرور');
        }
    }
}
