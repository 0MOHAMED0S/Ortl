@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* --- التحسينات اللونية والجمالية --- */
        :root {
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            --input-focus: rgba(45, 138, 116, 0.15);
        }

        .profile-card {
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: 1px solid #f1f5f9;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .profile-cover {
            height: 140px;
            background: linear-gradient(135deg, #1b4d3e 0%, #2d8a74 100%);
            position: relative;
        }

        .profile-avatar-container {
            width: 120px;
            height: 120px;
            margin: -60px auto 15px;
            position: relative;
        }

        .profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 6px solid #fff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            object-fit: cover;
            background: #fff;
        }

        .settings-card {
            background: #fff;
            border-radius: 24px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            border: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .settings-header {
            margin-bottom: 25px;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 15px;
        }

        .settings-title {
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
        }

        /* --- ننسيق حقول الإدخال --- */
        .form-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-control {
            padding: 12px 16px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            background-color: #f8fafc;
        }

        .form-control:focus {
            border-color: #2d8a74;
            box-shadow: 0 0 0 4px var(--input-focus);
            background-color: #fff;
            color: #1e293b;
            outline: none;
        }

        /* --- أيقونات وكلمات المرور --- */
        .password-input-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            z-index: 10;
            padding: 5px;
            transition: color 0.2s;
            font-size: 0.9rem;
        }

        .toggle-password:hover {
            color: #2d8a74;
        }

        .input-group-text {
            border-radius: 12px 0 0 12px !important;
            background-color: #f1f5f9;
            border: 1.5px solid #e2e8f0;
            border-right: none;
            color: #64748b;
            padding: 0 15px;
            font-size: 0.9rem;
        }

        .input-group .form-control {
            border-radius: 0 12px 12px 0 !important;
        }

        /* --- الأزرار --- */
        .btn-update {
            padding: 10px 25px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* --- نظام التنبيهات الاحترافي (Toasts) --- */
        .fixed-alert-container {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 10000;
            width: 320px;
            pointer-events: none;
        }

        .custom-toast {
            pointer-events: auto;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            margin-bottom: 15px;
            overflow: hidden;
            position: relative;
            border-left: 5px solid transparent;
            animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            direction: rtl;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .custom-toast.success {
            border-left-color: #2d8a74;
        }

        .custom-toast.error {
            border-left-color: #e11d48;
        }

        .toast-content {
            padding: 15px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toast-icon {
            font-size: 1.4rem;
        }

        .success .toast-icon {
            color: #2d8a74;
        }

        .error .toast-icon {
            color: #e11d48;
        }

        .toast-body {
            flex-grow: 1;
            text-align: right;
        }

        .toast-title {
            display: block;
            font-weight: 800;
            font-size: 0.85rem;
            color: #1e293b;
        }

        .toast-message {
            margin: 0;
            font-size: 0.75rem;
            color: #64748b;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #94a3b8;
            cursor: pointer;
            order: -1;
        }

        .toast-progress {
            height: 3px;
            width: 100%;
            background: #f1f5f9;
            position: absolute;
            bottom: 0;
            right: 0;
        }

        .toast-progress::before {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            height: 100%;
            width: 100%;
        }

        .success .toast-progress::before {
            background: #2d8a74;
            animation: progressRun 5s linear forwards;
        }

        .error .toast-progress::before {
            background: #e11d48;
            animation: progressRun 5s linear forwards;
        }

        @keyframes progressRun {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        /* --- Media Queries --- */
        @media (max-width: 991px) {
            .profile-card {
                margin-bottom: 20px;
            }

            .settings-card {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .fixed-alert-container {
                width: 90%;
                right: 5%;
            }

            .btn-update {
                width: 100%;
            }
        }
    </style>
@endsection

@section('title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            <h5 class="m-0 fw-bold fs-5">إعدادات الحساب</h5>
        </div>
    </div>
@endsection

@section('content')
    {{-- نظام التنبيهات الاحترافي --}}
    <div class="fixed-alert-container">
        @if (session('success'))
            <div class="custom-toast success shadow-lg" role="alert">
                <div class="toast-content">
                    <button type="button" class="toast-close"
                        onclick="this.closest('.custom-toast').remove()">&times;</button>
                    <div class="toast-body">
                        <span class="toast-title">تم بنجاح</span>
                        <p class="toast-message">{{ session('success') }}</p>
                    </div>
                    <div class="toast-icon"><i class="fa-solid fa-circle-check"></i></div>
                </div>
                <div class="toast-progress"></div>
            </div>
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="custom-toast error shadow-lg" role="alert">
                    <div class="toast-content">
                        <button type="button" class="toast-close"
                            onclick="this.closest('.custom-toast').remove()">&times;</button>
                        <div class="toast-body">
                            <span class="toast-title">خطأ في العملية</span>
                            <p class="toast-message">{{ $error }}</p>
                        </div>
                        <div class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                    </div>
                    <div class="toast-progress"></div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="container-fluid p-3 p-md-4">
        <div class="row g-4">
            {{-- العمود الجانبي: معلومات الملف --}}
            <div class="col-12 col-lg-4">
                <div class="profile-card text-center pb-5">
                    <div class="profile-cover"></div>
                    <div class="profile-avatar-container">
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=1b4d3e&color=fff&size=128"
                            class="profile-avatar">
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted small mb-3">{{ Auth::user()->email }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-bold small">
                            <i class="fa-solid fa-shield-check me-1"></i> مدير النظام
                        </span>
                    </div>
                </div>
            </div>

            {{-- العمود الرئيسي: النماذج --}}
            <div class="col-12 col-lg-8">

                {{-- 1. البيانات الأساسية --}}
                <div class="settings-card">
                    <div class="settings-header">
                        <h6 class="settings-title m-0"><i class="fa-regular fa-user-circle me-2 text-primary"></i> المعلومات
                            الشخصية</h6>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" name="name" value="{{ Auth::user()->name }}"
                                    required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="email" value="{{ Auth::user()->email }}"
                                    required>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-update px-4">
                                تحديث البيانات
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 2. تغيير كلمة المرور --}}
                <div class="settings-card">
                    <div class="settings-header">
                        <h6 class="settings-title m-0"><i class="fa-solid fa-shield-halved me-2 text-danger"></i> الحماية
                            وكلمة المرور</h6>
                    </div>
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور الحالية</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" name="current_password" placeholder="••••••••"
                                    required>
                                <i class="fa-regular fa-eye toggle-password" onclick="togglePass(this)"></i>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">كلمة المرور الجديدة</label>
                                <div class="password-input-group">
                                    <input type="password" class="form-control" name="new_password" placeholder="••••••••"
                                        required>
                                    <i class="fa-regular fa-eye toggle-password" onclick="togglePass(this)"></i>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">تأكيد كلمة المرور</label>
                                <div class="password-input-group">
                                    <input type="password" class="form-control" name="new_password_confirmation"
                                        placeholder="••••••••" required>
                                    <i class="fa-regular fa-eye toggle-password" onclick="togglePass(this)"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-dark btn-update px-4">
                                تغيير كلمة المرور
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 3. معلومات التواصل --}}
                <div class="settings-card">
                    <div class="settings-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="settings-title m-0"><i class="fa-solid fa-headset me-2 text-success"></i> قنوات الدعم
                            الفني</h6>
                        <span class="badge bg-light text-muted fw-normal px-2 py-1 small">تظهر للطلاب في التطبيق</span>
                    </div>

                    @php $contact = \App\Models\ContactSetting::first(); @endphp

                    <form action="{{ route('settings.contact.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">بريد الدعم</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email"
                                        value="{{ $contact->email ?? '' }}" placeholder="support@domain.com" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">واتساب / هاتف</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                    <input type="text" class="form-control" name="phone"
                                        value="{{ $contact->phone ?? '' }}" placeholder="+201xxxxxxxxx" required>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success btn-update px-4 text-white"
                                style="background-color: #1b4d3e; border: none;">
                                حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function togglePass(icon) {
            const input = icon.previousElementSibling;
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }

        // إغلاق التنبيهات تلقائياً بعد 5 ثوانٍ
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelectorAll('.custom-toast').forEach(toast => {
                    toast.style.animation = "slideOutRight 0.5s ease-in forwards";
                    setTimeout(() => toast.remove(), 500);
                });
            }, 5000);
        });
    </script>
@endsection
