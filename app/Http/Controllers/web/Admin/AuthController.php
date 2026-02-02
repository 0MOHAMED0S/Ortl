<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
public function login(AdminLoginRequest $request)
{
    $credentials = $request->only('email', 'password');
    $credentials['role'] = 'admin';

    if (!Auth::attempt($credentials, true)) {
        return back()
            ->withErrors([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ])
            ->withInput();
    }

    $request->session()->regenerate();

    return redirect()
        ->route('admin.dashboard')
        ->with('success', 'مرحباً بك في لوحة التحكم');
}
public function logout(Request $request)
{
    Auth::guard('web')->logout();

    // Invalidate session
    $request->session()->invalidate();

    // Regenerate CSRF token
    $request->session()->regenerateToken();

    return redirect()
        ->route('admin.login')
        ->with('success', 'تم تسجيل الخروج بنجاح');
}
}
