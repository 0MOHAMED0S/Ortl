@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/teachers.css') }}">
    <style>
        /* --- Stats Cards --- */
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); border: 1px solid transparent; transition: 0.3s; display: flex; align-items: center; justify-content: space-between; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05); }
        .stat-icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .stat-purple { border-left: 4px solid #6f42c1; } .stat-purple .stat-icon-box { background: #f3e8ff; color: #6f42c1; }
        .stat-orange { border-left: 4px solid #fd7e14; } .stat-orange .stat-icon-box { background: #fff4e6; color: #fd7e14; }
        .stat-green { border-left: 4px solid #198754; } .stat-green .stat-icon-box { background: #d1e7dd; color: #198754; }
        .stat-red { border-left: 4px solid #dc3545; } .stat-red .stat-icon-box { background: #f8d7da; color: #dc3545; }

        /* --- Filter Buttons --- */
        .filter-btn { border: 1px solid #eee; background: white; color: #666; padding: 8px 16px; border-radius: 30px; font-weight: 600; font-size: 0.9rem; transition: 0.2s; margin-left: 5px; white-space: nowrap; }
        .filter-btn:hover, .filter-btn.active { background: var(--primary-dark); color: white; border-color: var(--primary-dark); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

        /* Mobile Scrollable Filters */
        #filterButtons {
            overflow-x: auto;
            padding-bottom: 5px;
            -webkit-overflow-scrolling: touch;
        }
        #filterButtons::-webkit-scrollbar { height: 4px; }
        #filterButtons::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

        /* --- Details & Modal --- */
        .info-section-title { font-size: 0.9rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 15px; border-bottom: 2px solid var(--gold-main); padding-bottom: 8px; display: inline-block; }
        .detail-item { margin-bottom: 12px; }
        .detail-label { font-size: 0.75rem; color: #999; display: block; margin-bottom: 3px; }
        .detail-val { font-weight: 600; color: #333; font-size: 0.95rem; word-break: break-word; }
        .tag-badge { background: #f8f9fa; border: 1px solid #eee; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; color: #555; display: inline-block; margin-left: 5px; margin-bottom: 5px; }
        .track-badge { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; display: inline-block; margin-left: 5px; margin-bottom: 5px; }

        /* Profile Image */
        .profile-img-container { position: relative; width: 120px; height: 120px; margin: 0 auto 15px; }
        .profile-img-main { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 3px solid var(--gold-main); padding: 3px; background: white; }
        .upload-btn-wrapper { position: absolute; bottom: 5px; right: 5px; }
        .btn-upload-icon { width: 32px; height: 32px; border-radius: 50%; background: var(--primary-dark); color: white; display: flex; align-items: center; justify-content: center; border: 2px solid white; cursor: pointer; transition: 0.3s; }
        .btn-upload-icon:hover { background: var(--gold-main); transform: scale(1.1); }

        /* Inputs & Read Only Boxes */
        .admin-input-box { background: #f8f9fa; padding: 15px; border-radius: 12px; border: 1px solid #eee; margin-bottom: 15px; }
        .admin-info-box { background: #f0fdf4; padding: 15px; border-radius: 12px; border: 1px solid #bbf7d0; margin-bottom: 15px; text-align: center; }
        .form-label { font-size: 0.8rem; font-weight: 700; color: #666; margin-bottom: 5px; }

        /* Responsive Modal Sidebar */
        .modal-sidebar-col { border-bottom: 1px solid #eee; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        @media (min-width: 992px) {
            .modal-sidebar-col { border-bottom: none; border-left: 1px solid #eee; padding-bottom: 0; margin-bottom: 0; height: 100%; overflow-y: auto; }
        }

        /* Header Switch */
        .registration-control { background: #fff; padding: 10px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; border: 1px solid #eee; }
        .registration-control .form-check-input { width: 2.5em; height: 1.3em; cursor: pointer; margin: 0; }
        .registration-control .form-check-input:checked { background-color: #198754; border-color: #198754; }
        .status-text { font-weight: 700; font-size: 0.85rem; transition: 0.3s; }
        .text-open { color: #198754; } .text-closed { color: #dc3545; }

        /* Table Responsive Fixes */
        .card-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #eee;
            overflow: hidden;
            transition: opacity 0.3s;
            position: relative;
        }
        .teacher-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .teacher-profile { display: flex; align-items: center; gap: 10px; }
        .teacher-name { font-weight: 700; display: block; font-size: 0.9rem; }
        .teacher-sub { font-size: 0.75rem; color: #888; }

        @media (max-width: 768px) {
            .registration-control { margin-top: 15px; width: 100%; justify-content: space-between; }
            #searchInput { width: 100%; margin-top: 10px; }
        }

        /* Pagination Styles */
        .pagination-wrapper { margin-top: 20px; display: flex; justify-content: center; }

        /* --- Smart Search Styles --- */
        .search-container { position: relative; }
        .search-icon-wrapper {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            z-index: 10;
            color: #94a3b8;
            background: none;
            border: none;
            outline: none;
            padding: 0;
            transition: 0.3s;
        }
        .search-icon-wrapper:hover { color: var(--primary-dark); cursor: pointer; }
        .search-input { padding-right: 40px !important; border-radius: 20px; transition: 0.3s; border: 1px solid #e2e8f0; }
        .search-input:focus { box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); border-color: var(--primary-dark); }
        .search-hint { font-size: 0.75rem; position: absolute; bottom: -20px; right: 10px; font-weight: 600; transition: 0.3s; }
    </style>
@endsection

@section('title')
<h5 class="m-0 fw-bold">معلمون التلاوة</h5>
@endsection

@section('content')
    <div class="container-fluid p-3 p-md-4">

        {{-- Header Section --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h5 class="fw-bold m-0 text-dark">إدارة طلبات التسجيل</h5>
                <small class="text-muted">التحكم في حالة تسجيل المعلمين</small>
            </div>

            <div class="registration-control mt-3 mt-md-0">
                <span class="text-muted small fw-bold">حالة التسجيل:</span>
                <form action="{{ route('settings.toggleRegistration') }}" method="POST" class="m-0 d-flex align-items-center">
                    @csrf
                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                        @php
                            $setting = \App\Models\Setting::first();
                            $isOpen = optional($setting)->teacher_application_status === 'open';
                        @endphp
                        <input class="form-check-input" type="checkbox" name="teacher_application_status" id="registrationToggle" onchange="this.form.submit()" {{ $isOpen ? 'checked' : '' }}>
                        <label class="status-text {{ $isOpen ? 'text-open' : 'text-closed' }}" for="registrationToggle" style="cursor: pointer;">
                            {{ $isOpen ? 'مفتوح' : 'مغلق' }}
                        </label>
                    </div>
                </form>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-exclamation fs-4 me-2"></i>
                    <div>
                        <h6 class="fw-bold mb-1">يوجد أخطاء في البيانات المدخلة:</h6>
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- 🟢 Stats Grid (يتم قراءة المتغيرات الجديدة من الكنترولر لحساب كل البيانات في قاعدة البيانات) --}}
        <div class="row g-3 mb-4" id="stats-wrapper">
            <div class="col-6 col-xl-3">
                <div class="stat-card stat-purple">
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">نتائج البحث الحالي</h6>
                        {{-- إجمالي نتائج البحث فقط (باستخدام total من الـ pagination) --}}
                        <h3 class="fw-bold m-0 text-dark" id="totalCount">{{ method_exists($teachers, 'total') ? $teachers->total() : $teachers->count() }}</h3>
                    </div>
                    <div class="stat-icon-box"><i class="fa-solid fa-folder-open"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card stat-orange">
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">قيد المراجعة (الكل)</h6>
                        {{-- يعرض جميع الطلبات قيد المراجعة في قاعدة البيانات بغض النظر عن الصفحة --}}
                        <h3 class="fw-bold m-0 text-dark">{{ $pendingCount ?? \App\Models\Teacher_application::where('status', 'pending')->count() }}</h3>
                    </div>
                    <div class="stat-icon-box"><i class="fa-solid fa-clock"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card stat-green">
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">مقبول (الكل)</h6>
                        {{-- يعرض جميع المعلمين المقبولين في قاعدة البيانات بالكامل --}}
                        <h3 class="fw-bold m-0 text-dark">{{ $approvedCount ?? \App\Models\Teacher_application::where('status', 'approved')->count() }}</h3>
                    </div>
                    <div class="stat-icon-box"><i class="fa-solid fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card stat-red">
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">مرفوض/غير مفعل (الكل)</h6>
                        {{-- يعرض كل المرفوضين في قاعدة البيانات بالكامل --}}
                        <h3 class="fw-bold m-0 text-dark">{{ $rejectedCount ?? \App\Models\Teacher_application::whereIn('status', ['rejected', 'not_active'])->count() }}</h3>
                    </div>
                    <div class="stat-icon-box"><i class="fa-solid fa-ban"></i></div>
                </div>
            </div>
        </div>

        {{-- Filters & Smart Search Form (Standard Request) --}}
        <form action="{{ url()->current() }}" method="GET" id="searchFilterForm" class="d-flex flex-column flex-md-row justify-content-between align-items-md-start mb-4 gap-3">

            {{-- حقل مخفي لحفظ حالة الفلتر --}}
            <input type="hidden" name="status" id="statusInput" value="{{ request('status', 'all') }}">

            <div class="d-flex w-100 overflow-auto pb-2 pb-md-0 mt-2" id="filterButtons">
                <button type="button" class="filter-btn {{ request('status', 'all') == 'all' ? 'active' : '' }}" onclick="submitFilter('all')">الكل</button>
                <button type="button" class="filter-btn {{ request('status') == 'pending' ? 'active' : '' }}" onclick="submitFilter('pending')">قيد المراجعة</button>
                <button type="button" class="filter-btn {{ request('status') == 'approved' ? 'active' : '' }}" onclick="submitFilter('approved')">مقبول</button>
                <button type="button" class="filter-btn {{ request('status') == 'not_active' ? 'active' : '' }}" onclick="submitFilter('not_active')">غير مفعل</button>
                <button type="button" class="filter-btn {{ request('status') == 'rejected' ? 'active' : '' }}" onclick="submitFilter('rejected')">مرفوض</button>
            </div>

            {{-- حقل البحث الذكي --}}
            <div class="w-100 w-md-auto search-container" style="min-width: 300px;">
                <button type="submit" class="search-icon-wrapper" id="searchIcon" title="بحث">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <input type="text" name="search" id="searchInput" class="form-control search-input" placeholder="اكتب للبحث واضغط Enter..." value="{{ request('search') }}">
                <div id="searchHint" class="search-hint" style="display: none;"></div>
            </div>
        </form>

        <div id="data-wrapper">
            {{-- Table --}}
            <div class="card-box">
                <div class="table-responsive">
                    <table class="table custom-table mb-0 text-nowrap align-middle" id="teachersTable">
                        <thead class="table-light">
                            <tr>
                                <th>المعلم</th>
                                <th>الدولة</th>
                                <th>المؤهل</th>
                                <th>رصيد الدقائق</th>
                                <th>تاريخ الطلب</th>
                                <th>الحالة</th>
                                <th class="text-end">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teachers as $teacher)
                                @php
                                    $userName = optional(optional($teacher->profile)->user)->name ?? $teacher->full_name;
                                    $userEmail = optional(optional($teacher->profile)->user)->email ?? $teacher->email;
                                    $imagePath = $teacher->status == 'pending' ? $teacher->profile_photo_path : (optional($teacher->profile)->profile_photo_path ?? $teacher->profile_photo_path);
                                    $minutes = optional($teacher->profile)->minutes ?? 0;
                                @endphp

                                <tr class="teacher-row">
                                    <td>
                                        <div class="teacher-profile">
                                            <img src="{{ $imagePath ? asset('storage/' . $imagePath) : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=1a4d2e&color=fff' }}"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($userName) }}&background=1a4d2e&color=fff&size=64'"
                                                 class="teacher-avatar">
                                            <div>
                                                <span class="teacher-name">{{ $userName }}</span>
                                                <span class="teacher-sub">{{ $userEmail }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $teacher->origin_country ?? 'غير محدد' }}</td>
                                    <td><span class="badge bg-light text-dark border fw-normal">{{ Str::limit($teacher->qualification, 20) }}</span></td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-regular fa-clock text-primary"></i>
                                            <span class="fw-bold">{{ number_format($minutes) }}</span>
                                            <small class="text-muted">دقيقة</small>
                                        </div>
                                    </td>

                                    <td class="text-muted small">{{ $teacher->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        @if ($teacher->status == 'pending')
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">قيد المراجعة</span>
                                        @elseif($teacher->status == 'approved')
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">مقبول</span>
                                        @elseif($teacher->status == 'not_active')
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">غير مفعل</span>
                                        @elseif($teacher->status == 'rejected')
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">مرفوض</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light border" title="مراجعة الطلب" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $teacher->id }}">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="mb-3">
                                            <i class="fa-solid fa-search fs-1 text-muted opacity-25"></i>
                                        </div>
                                        <h6 class="fw-bold">لم يتم العثور على بيانات!</h6>
                                        <p class="small">لا توجد نتائج تطابق بحثك في قاعدة البيانات.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- إضافة روابط الترقيم (Pagination Links) أسفل الجدول --}}
                @if(method_exists($teachers, 'links'))
                    <div class="pagination-wrapper pb-3">
                        {{ $teachers->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>

            {{-- MODALS --}}
            @foreach ($teachers as $teacher)
                @php
                    $modalName = optional(optional($teacher->profile)->user)->name ?? $teacher->full_name;
                    $modalImg = $teacher->status == 'pending' ? $teacher->profile_photo_path : (optional($teacher->profile)->profile_photo_path ?? $teacher->profile_photo_path);
                @endphp
                <div class="modal fade details-modal" id="detailsModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">
                                    <span class="text-muted fw-light fs-6">طلب رقم #{{ $teacher->id }}</span> | مراجعة بيانات المعلم
                                </h5>
                                <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-0">
                                <div class="row g-0 h-100">

                                    {{-- Sidebar (Image & Edit Forms) --}}
                                    <div class="col-lg-3 modal-sidebar-col bg-light p-4 d-flex flex-column border-end">

                                        <div class="text-center mb-3">
                                            <div class="profile-img-container">
                                                <img src="{{ $modalImg ? asset('storage/' . $modalImg) : 'https://ui-avatars.com/api/?name=' . urlencode($modalName) . '&background=1a4d2e&color=fff&size=128' }}"
                                                     class="profile-img-main preview-img-{{ $teacher->id }}">

                                                <div class="upload-btn-wrapper">
                                                    <label for="photoInput{{ $teacher->id }}" class="btn-upload-icon" title="تغيير الصورة">
                                                        <i class="fa-solid fa-camera"></i>
                                                    </label>
                                                    <input
                                                        form="{{ $teacher->status == 'pending' ? 'approveForm'.$teacher->id : 'updateForm'.$teacher->id }}"
                                                        type="file"
                                                        name="profile_photo" id="photoInput{{ $teacher->id }}"
                                                        style="display: none;" accept="image/*"
                                                        {{ $teacher->status == 'pending' ? 'required' : '' }}
                                                        onchange="previewFile(this, 'preview-img-{{ $teacher->id }}')">
                                                </div>
                                            </div>

                                            <h5 class="fw-bold mb-1">{{ $modalName }}</h5>
                                            <span class="badge bg-primary mb-2">{{ $teacher->gender == 'male' ? 'ذكر' : 'أنثى' }}</span>

                                            @if ($teacher->status == 'approved')
                                                <div class="mt-2"><span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i> الحساب نشط</span></div>
                                            @elseif($teacher->status == 'not_active')
                                                <div class="mt-2"><span class="badge bg-secondary"><i class="fa-solid fa-pause-circle me-1"></i> الحساب غير مفعل</span></div>
                                            @elseif($teacher->status == 'rejected')
                                                <div class="mt-2"><span class="badge bg-danger"><i class="fa-solid fa-times-circle me-1"></i> الطلب مرفوض</span></div>
                                            @endif
                                        </div>

                                        {{-- PENDING STATE FORM --}}
                                        @if ($teacher->status == 'pending')
                                            <form action="{{ route('teacher.approve', $teacher->id) }}" method="POST"
                                                enctype="multipart/form-data" id="approveForm{{ $teacher->id }}" class="flex-grow-1">
                                                @csrf
                                                <div class="text-center mb-3"><small class="text-danger fw-bold" style="font-size:0.7rem">* الصورة الشخصية مطلوبة للقبول</small></div>

                                                <div class="admin-input-box text-start">
                                                    <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                                        <input type="email" name="email" class="form-control border-start-0" value="{{ $teacher->email }}" required>
                                                    </div>
                                                </div>

                                                <div class="admin-input-box text-start">
                                                    <label class="form-label">راتب الساعة المتفق عليه <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-dollar-sign text-success"></i></span>
                                                        <input type="number" name="salary" class="form-control border-start-0" placeholder="0.00" required min="1">
                                                    </div>
                                                </div>

                                                <div class="admin-input-box text-start">
                                                    <label class="form-label">تعيين كلمة المرور <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                                        <input type="password" name="password" class="form-control border-start-0" placeholder="********" required minlength="8">
                                                    </div>
                                                </div>
                                            </form>

                                        {{-- EDIT STATE FORM --}}
                                        @elseif($teacher->status == 'approved' || $teacher->status == 'not_active')
                                            <form action="{{ route('teacher.updateDetails', $teacher->id) }}" method="POST"
                                                  enctype="multipart/form-data" id="updateForm{{ $teacher->id }}" class="flex-grow-1">
                                                @csrf

                                                <div class="admin-input-box text-start">
                                                    <label class="form-label">الاسم الكامل</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                                                        <input type="text" name="name" class="form-control border-start-0"
                                                               value="{{ optional(optional($teacher->profile)->user)->name ?? $teacher->full_name }}" required>
                                                    </div>
                                                </div>

                                                <div class="admin-input-box text-start">
                                                    <label class="form-label">البريد الإلكتروني</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                                        <input type="email" name="email" class="form-control border-start-0"
                                                               value="{{ optional(optional($teacher->profile)->user)->email ?? $teacher->email }}" required>
                                                    </div>
                                                </div>

                                                <div class="admin-input-box text-start">
                                                    <label class="form-label">حالة الحساب</label>
                                                    <select name="status" class="form-select border-0 bg-transparent ps-0 fw-bold {{ $teacher->status == 'approved' ? 'text-success' : 'text-secondary' }}">
                                                        <option value="approved" {{ $teacher->status == 'approved' ? 'selected' : '' }}>نشط (مقبول)</option>
                                                        <option value="not_active" {{ $teacher->status == 'not_active' ? 'selected' : '' }}>غير مفعل</option>
                                                    </select>
                                                </div>

                                                <div class="admin-input-box text-start">
                                                    <label class="form-label">تعديل الراتب</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-dollar-sign text-success"></i></span>
                                                        <input type="number" name="salary" class="form-control border-start-0"
                                                               value="{{ $teacher->profile->salary ?? '0.00' }}" min="0" step="0.01">
                                                    </div>
                                                </div>

                                                <div class="admin-input-box text-start">
                                                    <label class="form-label">تغيير كلمة المرور</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                                        <input type="password" name="password" class="form-control border-start-0"
                                                               placeholder="اتركه فارغاً للإبقاء عليه" minlength="8">
                                                    </div>
                                                </div>
                                            </form>
                                        @endif

                                    </div>

                                    {{-- Details Content --}}
                                    <div class="col-lg-9 p-4">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <h6 class="info-section-title">البيانات الشخصية</h6>
                                                <div class="row">
                                                    <div class="col-6 detail-item">
                                                        <span class="detail-label">الاسم (في الطلب)</span>
                                                        <span class="detail-val">{{ $teacher->full_name }}</span>
                                                    </div>
                                                    <div class="col-6 detail-item">
                                                        <span class="detail-label">رقم الهاتف</span>
                                                        <a href="https://wa.me/{{ str_replace(['+', ' '], '', $teacher->phone) }}" target="_blank" class="detail-val text-success text-decoration-none" style="direction: ltr; display:inline-block;">
                                                            <i class="fa-brands fa-whatsapp me-1"></i> {{ $teacher->phone }}
                                                        </a>
                                                    </div>
                                                    <div class="col-6 detail-item">
                                                        <span class="detail-label"> البريد</span>
                                                        <span class="detail-val">{{ $teacher->email }}</span>
                                                    </div>
                                                    <div class="col-6 detail-item">
                                                        <span class="detail-label">بلد الأصل</span>
                                                        <span class="detail-val">{{ $teacher->origin_country }}</span>
                                                    </div>
                                                    <div class="col-6 detail-item">
                                                        <span class="detail-label">مكان الإقامة</span>
                                                        <span class="detail-val">{{ $teacher->residence_location }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <h6 class="info-section-title">المؤهلات واللغات</h6>
                                                <div class="detail-item">
                                                    <span class="detail-label">المؤهل العلمي</span>
                                                    <span class="detail-val">{{ $teacher->qualification }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="detail-label">اللغات</span>
                                                    <div>
                                                        @if (isset($teacher->languages) && (is_array($teacher->languages) || is_object($teacher->languages)))
                                                            @foreach ($teacher->languages as $lang)
                                                                <span class="tag-badge">{{ $lang }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted small">لا يوجد</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <h6 class="info-section-title">المسارات المختارة (التخصص)</h6>
                                                <div class="mb-3">
                                                    @foreach ($teacher->tracks as $track)
                                                        <span class="track-badge">
                                                            <i class="fa-solid fa-check-circle me-1"></i> {{ $track->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <h6 class="info-section-title">الخبرة والقدرات التقنية</h6>
                                                <div class="row bg-light p-3 rounded-3 mx-0 border">
                                                    <div class="col-6 col-md-3 mb-3 mb-md-0 text-center border-end">
                                                        <span class="detail-label">سنوات الخبرة</span>
                                                        <h5 class="fw-bold text-primary m-0">{{ $teacher->experience_years }} سنوات</h5>
                                                    </div>
                                                    <div class="col-6 col-md-3 mb-3 mb-md-0 text-center border-end-md">
                                                        <span class="detail-label">ساعات العمل</span>
                                                        <h5 class="fw-bold text-success m-0">{{ $teacher->work_hours }} ساعات</h5>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center border-end">
                                                        <span class="detail-label">رصيد الدقائق</span>
                                                        <h5 class="fw-bold text-warning m-0">{{ optional($teacher->profile)->minutes ?? 0 }} دقيقة</h5>
                                                    </div>
                                                    <div class="col-6 col-md-3 text-center">
                                                        <span class="detail-label">جودة الإنترنت</span>
                                                        <span class="badge bg-success bg-opacity-10 text-success mt-1">{{ $teacher->internet_quality }}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-3 px-2">
                                                    <span class="detail-label d-inline">المهارات التقنية:</span>
                                                    <span class="fw-bold text-dark">{{ $teacher->tech_skills }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <h6 class="info-section-title">المرفقات والإجازات</h6>
                                                <div class="mb-3">
                                                    <span class="detail-label">نص الإجازات:</span>
                                                    <p class="detail-val bg-white p-3 rounded border text-muted small" style="line-height: 1.6; max-height: 100px; overflow-y: auto;">
                                                        {{ $teacher->ijazas_text ?? 'لا يوجد نص مكتوب' }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <span class="detail-label">السيرة الذاتية والشهادات:</span>
                                                    @if ($teacher->cv_pdf_path)
                                                        <a href="{{ asset('storage/' . $teacher->cv_pdf_path) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 text-start">
                                                            <i class="fa-solid fa-file-pdf me-2"></i> عرض ملف الـ CV والشهادات (PDF)
                                                        </a>
                                                    @else
                                                        <div class="alert alert-warning py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> لا يوجد ملف مرفق</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer bg-light justify-content-between">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>

                                @if ($teacher->status == 'pending')
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('teacher.reject', $teacher->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger fw-bold" onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">
                                                <i class="fa-solid fa-xmark me-1"></i> رفض
                                            </button>
                                        </form>

                                        <button type="button" onclick="document.getElementById('approveForm{{ $teacher->id }}').submit();" class="btn btn-success fw-bold">
                                            <i class="fa-solid fa-check me-1"></i> قبول وتفعيل
                                        </button>
                                    </div>
                                @elseif($teacher->status == 'approved' || $teacher->status == 'not_active')
                                    <button type="button" onclick="document.getElementById('updateForm{{ $teacher->id }}').submit();" class="btn btn-primary fw-bold">
                                        <i class="fa-solid fa-save me-1"></i> حفظ التعديلات
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewFile(input, imgIdClass) {
            const modal = input.closest('.modal');
            const preview = modal.querySelector('.' + imgIdClass);
            const file = input.files[0];
            const reader = new FileReader();
            reader.addEventListener("load", function() {
                preview.src = reader.result;
            }, false);
            if (file) {
                reader.readAsDataURL(file);
            }
        }

        // ===============================================
        // إرسال البحث والفلترة عن طريق Standard Request (بدون AJAX)
        // ===============================================

        // دالة الفلترة وإرسال الفورم (تعمل عند النقر على الأزرار)
        function submitFilter(status) {
            document.getElementById('statusInput').value = status;
            document.getElementById('searchFilterForm').submit();
        }

        // إظهار التلميح الذكي أثناء الكتابة في حقل البحث
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            let search = this.value.trim();
            let hintBox = document.getElementById('searchHint');

            if (search.length > 0) {
                hintBox.style.display = 'block';
                if (search.includes('@')) {
                    hintBox.innerHTML = '<i class="fa-solid fa-envelope text-primary ms-1"></i> اضغط Enter للبحث بالبريد...';
                    hintBox.className = 'search-hint text-primary';
                } else if (/^\d+$/.test(search)) {
                    hintBox.innerHTML = '<i class="fa-solid fa-phone text-success ms-1"></i> اضغط Enter للبحث بالرقم...';
                    hintBox.className = 'search-hint text-success';
                } else {
                    hintBox.innerHTML = '<i class="fa-solid fa-user text-info ms-1"></i> اضغط Enter للبحث بالاسم...';
                    hintBox.className = 'search-hint text-info';
                }
            } else {
                hintBox.style.display = 'none';
            }
        });
    </script>
@endsection
