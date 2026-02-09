@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* --- Profile Header Card --- */
        .profile-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
            height: 100%; /* Ensure full height on desktop */
        }

        .profile-cover {
            height: 140px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-main) 100%);
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
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            object-fit: cover;
            background: #fff;
        }

        .upload-icon {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 35px;
            height: 35px;
            background: var(--gold-main);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #fff;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .upload-icon:hover { transform: scale(1.1); background: #b08d45; }

        /* --- Settings Cards --- */
        .settings-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .settings-header {
            margin-bottom: 25px;
            border-bottom: 1px solid #f8f9fa;
            padding-bottom: 15px;
        }

        /* --- Form Elements --- */
        .form-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 8px;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-main);
            box-shadow: 0 0 0 4px rgba(45, 138, 116, 0.1);
            background-color: #fff;
        }

        /* Password Input Wrapper */
        .password-input-group { position: relative; }
        .toggle-password {
            position: absolute;
            left: 15px; /* RTL */
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            z-index: 10;
            padding: 5px;
        }

        /* --- RESPONSIVE ADJUSTMENTS --- */
        @media (max-width: 991px) {
            .profile-card { margin-bottom: 25px; height: auto; }
            .col-xl-4 { margin-bottom: 20px; }
        }

        @media (max-width: 768px) {
            .settings-card { padding: 20px; } /* Reduce padding on mobile */
            .profile-cover { height: 120px; }
            .profile-avatar-container { width: 100px; height: 100px; margin-top: -50px; }
        }
    </style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h5 class="m-0 fw-bold">الملف الشخصي</h5>
        <small class="text-muted">إدارة بيانات حساب المدير</small>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid p-3 p-md-4">

    {{-- Alert Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <ul class="mb-0 small list-unstyled">
                @foreach ($errors->all() as $error)
                    <li><i class="fa-solid fa-circle-exclamation me-2"></i>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- LEFT COLUMN: Profile Visual & Stats --}}
        <div class="col-12 col-lg-4 col-xl-4">
            <div class="profile-card text-center pb-4 h-100">
                <div class="profile-cover"></div>

                <div class="profile-avatar-container">
                    {{-- Avatar Image --}}
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=1b4d3e&color=fff&size=128"
                         class="profile-avatar"
                         id="avatarPreview">

                    {{-- Upload Icon (Label for hidden file input) --}}
                    <label for="avatarInput" class="upload-icon" title="تغيير الصورة">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                </div>

                <h5 class="fw-bold text-dark mb-1">{{ Auth::user()->name }}</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-shield-halved me-1"></i> المدير العام
                </span>
            </div>
        </div>

        {{-- RIGHT COLUMN: Forms --}}
        <div class="col-12 col-lg-8 col-xl-8">

            {{-- 1. General Info Form --}}
            <div class="settings-card">
                <div class="settings-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0 text-dark"><i class="fa-regular fa-id-card me-2 text-muted"></i> البيانات الأساسية</h6>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Hidden input for avatar upload triggered by the icon above --}}
                    <input type="file" id="avatarInput" name="avatar" style="display: none;" onchange="previewImage(this)" accept="image/*">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">الاسم الكامل</label>
                            <input type="text" class="form-control" name="name" value="{{ Auth::user()->name }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" name="email" value="{{ Auth::user()->email }}" required>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                            <i class="fa-solid fa-check me-2"></i> حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. Security (Password) Form --}}
            <div class="settings-card">
                <div class="settings-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0 text-dark"><i class="fa-solid fa-lock me-2 text-muted"></i> الأمان وكلمة المرور</h6>
                </div>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">كلمة المرور الحالية</label>
                        <div class="password-input-group">
                            <input type="password" class="form-control" name="current_password" required>
                            <i class="fa-regular fa-eye toggle-password" onclick="togglePass(this)"></i>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">كلمة المرور الجديدة</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" name="new_password" required minlength="8">
                                <i class="fa-regular fa-eye toggle-password" onclick="togglePass(this)"></i>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">تأكيد كلمة المرور</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" name="new_password_confirmation" required>
                                <i class="fa-regular fa-eye toggle-password" onclick="togglePass(this)"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-dark fw-bold px-4 shadow-sm">
                            <i class="fa-solid fa-key me-2"></i> تحديث كلمة المرور
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
    // 1. Preview Image on Select
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 2. Toggle Password Visibility
    function togglePass(icon) {
        const input = icon.previousElementSibling;
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>
@endsection
