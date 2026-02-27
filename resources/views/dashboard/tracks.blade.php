@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        :root {
            --primary-color: #1cc6a4;
            --primary-dark: #0d9488;
            --surface-color: #ffffff;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        /* --- نظام التنبيهات (Responsive Toasts) --- */
        .fixed-alert-container { position: fixed; top: 25px; right: 25px; z-index: 10000; width: 100%; max-width: 350px; pointer-events: none; }
        .custom-toast {
            pointer-events: auto; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); border-radius: 14px; margin-bottom: 15px; overflow: hidden; position: relative; border-left: 5px solid transparent; animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); direction: rtl; box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .custom-toast.success { border-left-color: var(--primary-color); }
        .custom-toast.error { border-left-color: #ef4444; }
        .toast-content { padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
        .toast-icon { font-size: 1.6rem; }
        .success .toast-icon { color: var(--primary-color); }
        .error .toast-icon { color: #ef4444; }
        .toast-body { flex-grow: 1; text-align: right; }
        .toast-title { display: block; font-weight: 800; font-size: 0.95rem; color: var(--text-main); }
        .toast-message { margin: 0; font-size: 0.85rem; color: var(--text-muted); }
        .toast-close { background: none; border: none; font-size: 1.3rem; color: #94a3b8; cursor: pointer; order: -1; }
        .toast-progress { height: 3px; width: 100%; background: #f1f5f9; position: absolute; bottom: 0; right: 0; }
        .toast-progress::before { content: ""; position: absolute; bottom: 0; right: 0; height: 100%; width: 100%; }
        .success .toast-progress::before { background: var(--primary-color); animation: progressRun 5s linear forwards; }
        .error .toast-progress::before { background: #ef4444; animation: progressRun 5s linear forwards; }

        @keyframes progressRun { from { width: 100%; } to { width: 0%; } }
        @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- Scrollbars --- */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* --- الفلاتر وأدوات البحث (متجاوبة) --- */
        .filter-scroll-wrapper { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
        .filter-scroll-wrapper::-webkit-scrollbar { display: none; }
        .filter-btn { padding: 8px 22px; border-radius: 50px; background: #fff; border: 1px solid var(--border-color); font-size: 0.9rem; font-weight: 700; color: var(--text-muted); cursor: pointer; transition: 0.3s; white-space: nowrap; }
        .filter-btn.active { background: var(--primary-color); color: #fff; border-color: var(--primary-color); box-shadow: 0 4px 15px rgba(28, 198, 164, 0.25); }
        .filter-btn:hover:not(.active) { border-color: var(--primary-color); color: var(--primary-color); }

        .search-box { position: relative; flex-grow: 1; min-width: 250px; max-width: 400px; }
        .search-input { border-radius: 50px; border: 1px solid var(--border-color); padding: 10px 20px 10px 45px; width: 100%; transition: all 0.3s; background: #fff; font-size: 0.95rem; }
        .search-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(28, 198, 164, 0.1); outline: none; }
        .search-icon { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* --- كروت المسارات --- */
        .track-item { transition: opacity 0.4s ease, transform 0.4s ease; }
        .track-card { background: #fff; border-radius: 28px; border: 1px solid var(--border-color); display: flex; flex-direction: column; height: 100%; position: relative; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(0,0,0,0.02); padding: 25px; z-index: 1; }
        .track-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(28, 198, 164, 0.1); border-color: var(--primary-color); z-index: 5; }
        .track-card.stopped { opacity: 0.75; filter: grayscale(40%); }
        .track-card.stopped:hover { opacity: 1; filter: grayscale(0%); }

        .track-icon-wrapper { width: 70px; height: 70px; border-radius: 20px; background: var(--bg-light); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--primary-color); margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden; }
        .track-icon-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .track-title { font-weight: 800; font-size: 1.25rem; color: var(--text-main); margin-bottom: 10px; }
        .track-desc { font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px; flex-grow: 1; }

        .audience-info { display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #475569; padding: 5px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; margin-bottom: 15px; }
        .marketing-badge { position: absolute; top: 20px; right: 20px; background: #f59e0b; color: #fff; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); z-index: 2; }

        .track-stats { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .ts-item { display: flex; align-items: center; gap: 5px; font-size: 0.9rem; font-weight: 700; color: var(--text-main); }
        .ts-item i { color: var(--primary-color); }

        .track-actions { position: absolute; top: 15px; left: 15px; z-index: 10; }
        .track-actions .btn-dots { width: 35px; height: 35px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: transparent; border: none; color: var(--text-muted); transition: 0.2s; }
        .track-actions .btn-dots:hover { background: var(--bg-light); color: var(--primary-color); }
        .track-actions .dropdown-toggle::after { display: none; }
        .dropdown-menu-custom { border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-radius: 16px; padding: 8px; min-width: 160px; z-index: 1050; }
        .dropdown-item-custom { border-radius: 10px; padding: 8px 15px; font-size: 0.85rem; font-weight: 700; color: var(--text-main); transition: 0.2s; display: flex; align-items: center; }
        .dropdown-item-custom:hover { background-color: var(--bg-light); color: var(--primary-color); }
        .dropdown-item-custom.text-danger:hover { background-color: #fef2f2; color: #ef4444; }

        .track-footer { padding-top: 15px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; }
        .form-switch .form-check-input { width: 2.8em; height: 1.4em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: var(--primary-color); border-color: var(--primary-color); }

        .add-track-btn { border: 2px dashed #cbd5e1; background: transparent; color: #94a3b8; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; min-height: 100%; transition: all 0.3s ease; text-align: center; padding: 40px 20px; border-radius: 28px; }
        .add-track-btn:hover { border-color: var(--primary-color); background: rgba(28, 198, 164, 0.02); color: var(--primary-color); transform: translateY(-5px); }
        .add-icon-circle { width: 70px; height: 70px; border-radius: 50%; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 15px; color: inherit; transition: 0.3s; }
        .add-track-btn:hover .add-icon-circle { background: var(--primary-color); color: #fff; box-shadow: 0 8px 20px rgba(28, 198, 164, 0.3); }

        /* --- جدول المعلمين (Professional Table) --- */
        .table-card { border-radius: 24px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.02); background: #fff; }
        .table-custom th { padding: 18px 20px; font-size: 0.85rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; background: #f8fafc; border-bottom: 2px solid var(--border-color); letter-spacing: 0.5px; }
        .table-custom td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .table-custom tr:last-child td { border-bottom: none; }
        .table-custom tbody tr { transition: background-color 0.2s ease; }
        .table-custom tbody tr:hover { background-color: #fcfcfc; }

        .teacher-avatar { width: 50px; height: 50px; object-fit: cover; border-radius: 14px; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.08); background: var(--bg-light); display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--primary-color); font-size: 1.2rem; flex-shrink: 0; }

        .teacher-id-badge { font-family: 'Monaco', monospace; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; display: inline-block; margin-top: 4px; }
        .salary-badge { color: #059669; font-weight: 800; font-size: 1rem; }
        .minutes-badge { background: #e0e7ff; color: #4f46e5; padding: 6px 14px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }

        .table-btn { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--border-color); color: var(--text-muted); transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .table-btn:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(28, 198, 164, 0.2); }

        /* زر تعديل المسار في الجدول */
        .table-btn.edit-tracks { color: #f59e0b; border-color: #fef3c7; background: #fffbeb; }
        .table-btn.edit-tracks:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; box-shadow: 0 5px 15px rgba(245, 158, 11, 0.2); }

        /* --- مودال تفاصيل المعلم (Premium Profile Modal) --- */
        .modal-profile-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%); height: 140px; border-radius: 24px 24px 0 0; position: relative; }
        .avatar-overlap { margin-top: 55px; text-align: center; margin-bottom: 25px; position: relative; z-index: 2; }
        .avatar-overlap img, .avatar-overlap .fallback-avatar {
            width: 120px; height: 120px; border-radius: 50%; border: 6px solid #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12); object-fit: cover; background: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 3.5rem; font-weight: 800; color: var(--primary-color);
        }

        .profile-id-tag { background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05); color: var(--text-muted); font-family: monospace; font-size: 0.85rem; padding: 4px 12px; border-radius: 50px; font-weight: bold; }

        .stat-box-mini { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 15px; text-align: center; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02); height: 100%; }
        .stat-box-mini:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); }
        .stat-box-icon { width: 45px; height: 45px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 10px; }

        .detail-card { background: var(--bg-light); border-radius: 20px; padding: 20px; height: 100%; border: 1px solid var(--border-color); }
        .detail-card-title { font-size: 0.9rem; font-weight: 800; color: var(--text-main); margin-bottom: 18px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px; }

        .info-row { margin-bottom: 14px; display: flex; flex-direction: column; }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
        .info-value { font-size: 0.95rem; color: var(--text-main); font-weight: 600; word-break: break-word; }

        .custom-input { border: 1px solid var(--border-color); border-radius: 14px !important; padding: 12px 18px; font-size: 0.95rem; background: var(--bg-light); transition: all 0.3s ease; }
        .custom-input:focus { background: #fff; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(28, 198, 164, 0.1); }
        .form-label { font-weight: 700; color: var(--text-main); font-size: 0.85rem; margin-bottom: 8px; }
        .modal-header-custom { background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%); border-bottom: 1px solid var(--border-color); padding: 25px; border-radius: 24px 24px 0 0; }

        /* Image Preview Box */
        .image-preview-box { width: 100px; height: 100px; border-radius: 20px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #fff; position: relative; margin: 0 auto 15px; }
        .image-preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .image-preview-box i { font-size: 2rem; color: #cbd5e1; }

        /* --- تصميم اختيار المسارات (Checkbox Grid) --- */
        .track-checkbox-wrapper { display: block; position: relative; cursor: pointer; height: 100%; }
        .track-checkbox-input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
        .track-checkbox-box { border: 2px solid var(--border-color); border-radius: 14px; padding: 15px; display: flex; align-items: center; gap: 12px; transition: all 0.2s; background: #fff; height: 100%; }
        .track-checkbox-input:checked ~ .track-checkbox-box { border-color: var(--primary-color); background: rgba(28, 198, 164, 0.05); box-shadow: 0 4px 10px rgba(28, 198, 164, 0.1); }
        .track-checkbox-box i.check-icon { color: #cbd5e1; font-size: 1.2rem; transition: 0.2s; }
        .track-checkbox-input:checked ~ .track-checkbox-box i.check-icon { color: var(--primary-color); }

        /* --- Responsive Fixes --- */
        @media (max-width: 992px) {
            .filter-container-wrapper { flex-direction: column; align-items: stretch !important; gap: 15px; }
            .filter-container-wrapper .search-box, .filter-container-wrapper select { width: 100% !important; max-width: none; }
        }
        @media (max-width: 768px) {
            .fixed-alert-container { right: 10px; left: 10px; top: 10px; width: auto; max-width: none; }
            .table-responsive { border-radius: 20px; }
            .modal-body { padding: 15px !important; }
            .detail-card { padding: 15px; }
            .avatar-overlap img, .avatar-overlap .fallback-avatar { width: 100px; height: 100px; font-size: 2.5rem; }
            .avatar-overlap { margin-top: 50px; }
        }
    </style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100">
    <h5 class="m-0 fw-bold text-dark">إدارة المسارات </h5>
</div>
@endsection

@section('content')

<div class="container-fluid p-3 p-md-4">

    {{-- نظام التنبيهات --}}
    <div class="fixed-alert-container">
        @if(session('success'))
            <div class="custom-toast success shadow-lg">
                <div class="toast-content">
                    <div class="toast-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="toast-body">
                        <span class="toast-title">تم بنجاح</span>
                        <p class="toast-message">{{ session('success') }}</p>
                    </div>
                    <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                </div>
                <div class="toast-progress"></div>
            </div>
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="custom-toast error shadow-lg">
                    <div class="toast-content">
                        <div class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                        <div class="toast-body">
                            <span class="toast-title">خطأ</span>
                            <p class="toast-message">{{ $error }}</p>
                        </div>
                        <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                    </div>
                    <div class="toast-progress"></div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- الإحصائيات وفلاتر المسارات --}}
    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-5">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-layer-group fs-3"></i>
                </div>
                <div>
                    <h4 class="m-0 fw-bold text-dark">{{ $tracks->count() }} مسار تعليمي</h4>
                    <small class="text-muted fw-bold">{{ $tracks->where('status', 'active')->count() }} نشط حالياً</small>
                </div>
            </div>
        </div>
        <div class="col-md-7 d-flex justify-content-md-end">
            <div class="filter-scroll-wrapper">
                <button type="button" class="filter-btn track-filter-btn active" data-filter="all">عرض الكل</button>
                <button type="button" class="filter-btn track-filter-btn" data-filter="active">المسارات النشطة</button>
                <button type="button" class="filter-btn track-filter-btn" data-filter="stopped">المتوقفة</button>
            </div>
        </div>
    </div>

    {{-- القسم الأول: شبكة المسارات (Track Cards) --}}
    <div class="row g-4 mb-5" id="tracksGrid">
        {{-- كرت إضافة مسار جديد --}}
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="add-track-btn shadow-sm" data-bs-toggle="modal" data-bs-target="#addTrackModal">
                <div class="add-icon-circle"><i class="fa-solid fa-plus"></i></div>
                <h5 class="fw-bold text-dark m-0">إضافة مسار جديد</h5>
                <span class="small text-muted mt-2">انقر هنا لإنشاء منهج</span>
            </div>
        </div>

        @foreach ($tracks as $track)
        <div class="col-12 col-md-6 col-lg-4 col-xl-3 track-item" data-status="{{ $track->status }}">
            <div class="track-card {{ $track->status == 'stopped' ? 'stopped' : '' }}">

                @if($track->marketing_value)
                    <div class="marketing-badge"><i class="fa-solid fa-bolt me-1"></i> {{ $track->marketing_value }}</div>
                @endif

                {{-- القائمة المنسدلة للكرت --}}
                <div class="track-actions dropdown">
                    <button class="btn-dots dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-custom text-end shadow-lg">
                        <li>
                            <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editTrack{{ $track->id }}">
                                <i class="fa-solid fa-pen-to-square me-2 text-primary" style="width: 15px;"></i> تعديل المسار
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <button type="button" class="dropdown-item dropdown-item-custom text-danger" data-bs-toggle="modal" data-bs-target="#deleteTrack{{ $track->id }}">
                                <i class="fa-solid fa-trash-can me-2" style="width: 15px;"></i> حذف نهائي
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="track-icon-wrapper mt-3">
                    @if($track->icon)
                        <img src="{{ asset('storage/' . $track->icon) }}" alt="{{ $track->name }}">
                    @else
                        <i class="fa-solid fa-book-open-reader"></i>
                    @endif
                </div>

                <h5 class="track-title">{{ $track->name }}</h5>

                @if($track->target_group)
                    <div class="mb-2">
                        <span class="audience-info"><i class="fa-solid fa-users-viewfinder text-primary"></i> {{ $track->target_group }}</span>
                    </div>
                @endif

                <p class="track-desc">{{ Str::limit($track->description, 70, '...') }}</p>

                <div class="track-stats">
                    <div class="ts-item" title="عدد المعلمين">
                        <i class="fa-solid fa-chalkboard-user bg-primary bg-opacity-10 p-2 rounded-circle text-primary"></i>
                        <span>{{ $track->teachers_count ?? 0 }} معلم مسجل</span>
                    </div>
                </div>

                <div class="track-footer">
                    <span class="badge {{ $track->status == 'active' ? 'bg-success text-success' : 'bg-secondary text-secondary' }} bg-opacity-10 status-badge px-3 py-2">
                        {{ $track->status == 'active' ? 'نشط' : 'متوقف' }}
                    </span>

                    <form action="{{ route('tracks.update', $track->id) }}" method="POST" class="m-0">
                        @csrf @method('PUT')
                        <input type="hidden" name="name" value="{{ $track->name }}">
                        <input type="hidden" name="description" value="{{ $track->description }}">
                        <input type="hidden" name="status" value="{{ $track->status == 'active' ? 'stopped' : 'active' }}">
                        <div class="form-check form-switch m-0 p-0" title="تغيير حالة المسار">
                            <input class="form-check-input m-0 shadow-none" type="checkbox" onchange="this.form.submit()" {{ $track->status == 'active' ? 'checked' : '' }}>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- موديول التعديل للمسار --}}
        <div class="modal fade" id="editTrack{{ $track->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form method="POST" action="{{ route('tracks.update', $track->id) }}" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    @csrf @method('PUT')
                    <div class="modal-header-custom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;"><i class="fa-solid fa-pen-to-square fs-5"></i></div>
                            <div><h5 class="fw-bold m-0 text-dark">تعديل المسار</h5><small class="text-muted">{{ $track->name }}</small></div>
                        </div>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <label class="form-label d-block text-muted">أيقونة المسار (اختياري)</label>
                            <div class="image-preview-box">
                                @if($track->icon)
                                    <img id="preview-edit-{{ $track->id }}" src="{{ asset('storage/' . $track->icon) }}">
                                @else
                                    <i id="icon-placeholder-edit-{{ $track->id }}" class="fa-solid fa-image text-muted"></i>
                                    <img id="preview-edit-{{ $track->id }}" src="" style="display: none;">
                                @endif
                            </div>
                            <input type="file" class="form-control custom-input form-control-sm mx-auto w-75" name="icon" accept="image/*" onchange="previewImage(event, 'preview-edit-{{ $track->id }}', 'icon-placeholder-edit-{{ $track->id }}')">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">اسم المسار <span class="text-danger">*</span></label>
                            <input type="text" class="form-control custom-input" name="name" value="{{ $track->name }}" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">الفئة المستهدفة</label>
                                <input type="text" class="form-control custom-input" name="target_group" value="{{ $track->target_group }}" placeholder="مثال: المبتدئين">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">بطاقة التسويق</label>
                                <input type="text" class="form-control custom-input" name="marketing_value" value="{{ $track->marketing_value }}" placeholder="مثال: الأكثر طلباً">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الوصف <span class="text-danger">*</span></label>
                            <textarea class="form-control custom-input" name="description" rows="3" required>{{ $track->description }}</textarea>
                        </div>
                        <input type="hidden" name="status" value="{{ $track->status }}">
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 bg-white">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- موديول الحذف التحذيري للمسار --}}
        <div class="modal fade" id="deleteTrack{{ $track->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-0 p-4 pb-0">
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3 text-danger opacity-75"><i class="fa-solid fa-circle-exclamation fa-4x"></i></div>
                        <h5 class="fw-bold mb-2 text-dark">تأكيد الحذف؟</h5>
                        <p class="text-muted small">هل أنت متأكد أنك تريد حذف مسار <strong>{{ $track->name }}</strong> نهائياً؟</p>
                        <div class="mt-4">
                            <form action="{{ route('tracks.destroy', $track->id) }}" method="POST" class="m-0 w-100">
                                @csrf @method('DELETE')
                                <div class="d-flex flex-column gap-2">
                                    <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold shadow-sm py-2">نعم، احذف المسار</button>
                                    <button type="button" class="btn btn-light rounded-pill w-100 fw-bold border py-2" data-bs-dismiss="modal">تراجع</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- القسم الثاني: جدول المعلمين (Teachers Table) --}}
    <div class="mt-5 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3 filter-container-wrapper">
        <div class="d-flex align-items-center gap-3">
            <h4 class="fw-bold m-0 text-dark"><i class="fa-solid fa-chalkboard-user text-primary me-2"></i>طاقم المعلمين</h4>
            <span class="badge bg-primary text-white rounded-pill px-3 py-2 fs-6 shadow-sm" id="teachersCount">{{ $teachers->count() }} معلم</span>
        </div>

        <div class="d-flex gap-2 flex-wrap flex-md-nowrap w-100 w-lg-50">
            <select id="teacherTrackFilter" class="form-select border-light bg-white rounded-pill shadow-sm px-4 py-2 fw-bold text-muted" style="min-width: 180px;" onchange="filterTeachersTable()">
                <option value="all">كل المسارات</option>
                @foreach($tracks as $t)
                    <option value="{{ $t->name }}">{{ $t->name }}</option>
                @endforeach
            </select>
            <div class="search-box flex-grow-1">
                <i class="fa-solid fa-search search-icon"></i>
                <input type="text" id="teacherSearch" class="search-input shadow-sm" placeholder="ابحث باسم المعلم، الإيميل، أو المعرف..." onkeyup="filterTeachersTable()">
            </div>
        </div>
    </div>

    <div class="table-card shadow-sm mb-5">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0 text-center" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th class="text-start">المعلم / المعرف</th>
                        <th class="text-start">الراتب المتفق عليه</th>
                        <th class="text-start">الرصيد الزمني</th>
                        <th class="text-start" style="width: 25%">المسارات المسندة</th>
                        <th class="text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="teachersTableBody">
                    @forelse($teachers as $teacher)
                    @php
                        // تجهيز مسار الصورة
                        $avatar = null;
                        if(isset($teacher->profile_photo_path) && $teacher->profile_photo_path) {
                            $avatar = asset('storage/' . $teacher->profile_photo_path);
                        } elseif (isset($teacher->user->image) && $teacher->user->image) {
                            $avatar = $teacher->user->image;
                        }
                    @endphp
                    <tr class="teacher-row"
                        data-search="{{ strtolower(($teacher->user->name ?? '') . ' ' . ($teacher->user->email ?? '') . ' ' . $teacher->id) }}"
                        data-tracks="{{ implode(',', $teacher->tracks->pluck('name')->toArray()) }}">

                        {{-- بيانات المعلم --}}
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                @if($avatar)
                                    <img src="{{ $avatar }}" class="teacher-avatar me-3">
                                @else
                                    <div class="teacher-avatar me-3">{{ mb_substr($teacher->user->name ?? 'م', 0, 1) }}</div>
                                @endif
                                <div>
                                    <h6 class="m-0 fw-bold text-dark mb-1">{{ $teacher->user->name ?? 'غير معروف' }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="teacher-id-badge">ID: #{{ $teacher->id }}</span>
                                        <small class="text-muted d-none d-md-inline" style="font-size: 0.75rem;">{{ $teacher->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- الراتب --}}
                        <td class="text-start">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-sack-dollar text-success fs-5 opacity-75"></i>
                                <span class="salary-badge">${{ number_format($teacher->salary ?? 0, 2) }}</span>
                            </div>
                        </td>

                        {{-- الدقائق --}}
                        <td class="text-start">
                            <div class="minutes-badge">
                                <i class="fa-regular fa-clock"></i> {{ $teacher->minutes ?? 0 }} دقيقة
                            </div>
                        </td>

                        {{-- المسارات --}}
                        <td class="text-start">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($teacher->tracks as $t)
                                    <span class="badge bg-light text-dark border border-secondary border-opacity-25 rounded-pill px-2 py-1 fw-bold shadow-sm" style="font-size: 0.7rem;">{{ $t->name }}</span>
                                @endforeach
                            </div>
                        </td>

                        {{-- الإجراءات --}}
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                {{-- نمرر كائن المعلم كـ JSON للجافاسكريبت --}}
                                @php $teacher->computed_avatar = $avatar; @endphp

                                {{-- زر تعديل المسارات --}}
                                <button class="table-btn edit-tracks" data-bs-toggle="modal" data-bs-target="#editTeacherTracksModal{{ $teacher->id }}" title="تعديل مسارات المعلم">
                                    <i class="fa-solid fa-layer-group"></i>
                                </button>

                                {{-- زر عرض التفاصيل --}}
                                <button class="table-btn" onclick="showTeacherDetails({{ json_encode($teacher) }})" title="عرض التفاصيل الكاملة">
                                    <i class="fa-solid fa-id-card fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- مودال تعديل مسارات المعلم (متجاوب ومزود بسكرولبار داخلي) --}}
                    <div class="modal fade" id="editTeacherTracksModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                <form action="{{ route('teachers.tracks.update', $teacher->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-header-custom d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;"><i class="fa-solid fa-layer-group fs-5"></i></div>
                                            <div><h5 class="fw-bold m-0 text-dark">تعديل مسارات المعلم</h5><small class="text-muted">{{ $teacher->user->name ?? 'المعلم' }}</small></div>
                                        </div>
                                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body p-4">
                                        <p class="text-muted small mb-3">قم بتحديد المسارات التي يُسمح لهذا المعلم بتدريسها، يمكنك اختيار أكثر من مسار:</p>

                                        {{-- منطقة التمرير لعدد المسارات الكبير --}}
                                        <div class="tracks-checkbox-container custom-scrollbar" style="max-height: 350px; overflow-y: auto; padding-right: 5px;">
                                            <div class="row g-3">
                                                @foreach($tracks as $t)
                                                <div class="col-12 col-sm-6">
                                                    <label class="track-checkbox-wrapper">
                                                        <input type="checkbox" name="tracks[]" value="{{ $t->id }}" class="track-checkbox-input"
                                                        {{ $teacher->tracks->contains('id', $t->id) ? 'checked' : '' }}>
                                                        <div class="track-checkbox-box">
                                                            <i class="fa-solid fa-circle-check check-icon"></i>
                                                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $t->name }}</span>
                                                        </div>
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-0 p-4 pt-0 bg-white">
                                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-warning text-dark rounded-pill px-5 fw-bold shadow-sm">حفظ التغييرات</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fa-solid fa-folder-open fs-1 mb-3 opacity-25"></i><br>لا يوجد معلمين مرتبطين بأي مسار حالياً</td></tr>
                    @endforelse
                    <tr id="noTeacherResults" style="display: none;"><td colspan="5" class="text-center py-5 text-muted"><i class="fa-solid fa-magnifying-glass fs-2 mb-3 opacity-25"></i><h6 class="fw-bold">لا توجد نتائج تطابق بحثك</h6></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODALS الإضافية --}}

{{-- مودال إضافة مسار --}}
<div class="modal fade" id="addTrackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('tracks.store') }}" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            <div class="modal-header-custom d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;"><i class="fa-solid fa-plus fs-5"></i></div>
                    <div><h5 class="fw-bold m-0 text-dark">إنشاء مسار جديد</h5><small class="text-muted">إضافة منهج تلاوة للمنصة</small></div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <label class="form-label d-block text-muted">أيقونة المسار (اختياري)</label>
                    <div class="image-preview-box">
                        <i id="icon-placeholder-add" class="fa-solid fa-image text-muted"></i>
                        <img id="preview-add" src="" style="display: none;">
                    </div>
                    <input type="file" class="form-control custom-input form-control-sm mx-auto w-75" name="icon" accept="image/*" onchange="previewImage(event, 'preview-add', 'icon-placeholder-add')">
                </div>

                <div class="mb-3">
                    <label class="form-label">اسم المسار <span class="text-danger">*</span></label>
                    <input type="text" class="form-control custom-input" name="name" placeholder="مثال: التجويد المتقدم" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">الفئة المستهدفة</label>
                        <input type="text" class="form-control custom-input" name="target_group" placeholder="مثال: كافة الأعمار">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">بطاقة التسويق</label>
                        <input type="text" class="form-control custom-input" name="marketing_value" placeholder="مثال: أساسي للإجازة">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">الوصف <span class="text-danger">*</span></label>
                    <textarea class="form-control custom-input" name="description" rows="3" placeholder="اشرح تفاصيل وماهية هذا المسار..." required></textarea>
                </div>
                <input type="hidden" name="status" value="active">
            </div>
            <div class="modal-footer border-0 p-4 pt-0 bg-white">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">تأكيد ونشر المسار</button>
            </div>
        </form>
    </div>
</div>

{{-- مودال تفاصيل المعلم الشامل (Premium Profile Modal) --}}
<div class="modal fade" id="teacherDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-profile-header">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0 pb-5 bg-white">
                <div class="avatar-overlap">
                    <img src="" id="mt-avatar-img" style="display:none;" alt="Teacher Avatar">
                    <div class="fallback-avatar" id="mt-avatar-initials" style="display:none;"></div>
                    <h3 class="fw-bold mt-3 mb-1 text-dark" id="mt-name"></h3>
                    <div class="profile-id-tag mt-2">معرف المعلم: #<span id="mt-id"></span></div>
                </div>

                <div class="px-3 px-md-4 px-lg-5 mt-2">

                    {{-- الإحصائيات المالية والتشغيلية --}}
                    <div class="row justify-content-center g-3 mb-4">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="stat-box-mini">
                                <div class="stat-box-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-sack-dollar"></i></div>
                                <div class="text-muted small fw-bold mb-1">الراتب المتفق عليه</div>
                                <h4 class="m-0 fw-bold text-dark" id="mt-salary"></h4>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="stat-box-mini">
                                <div class="stat-box-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                <div class="text-muted small fw-bold mb-1">دقائق الرصيد الحالية</div>
                                <h4 class="m-0 fw-bold text-dark" id="mt-minutes"></h4>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">

                        {{-- 1. البيانات الشخصية والتواصل --}}
                        <div class="col-12 col-lg-4">
                            <div class="detail-card">
                                <h6 class="detail-card-title"><i class="fa-solid fa-address-card text-primary fs-5"></i> البيانات الشخصية والتواصل</h6>
                                <div class="info-row">
                                    <span class="info-label">البريد الإلكتروني</span>
                                    <span class="info-value text-primary" id="mt-email" style="word-break: break-all;"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">رقم الهاتف</span>
                                    <span class="info-value" id="mt-phone" dir="ltr"></span>
                                </div>
                                <div class="info-row mt-3 pt-3 border-top">
                                    <span class="info-label">الجنس</span>
                                    <span class="info-value" id="mt-gender"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">بلد الأصل (الجنسية)</span>
                                    <span class="info-value fw-bold text-dark" id="mt-origin"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">بلد الإقامة الحالي</span>
                                    <span class="info-value fw-bold text-dark" id="mt-residence"></span>
                                </div>
                            </div>
                        </div>

                        {{-- 2. المؤهلات والخبرات --}}
                        <div class="col-12 col-lg-4">
                            <div class="detail-card">
                                <h6 class="detail-card-title"><i class="fa-solid fa-graduation-cap text-primary fs-5"></i> المؤهلات والخبرات</h6>
                                <div class="info-row">
                                    <span class="info-label">المؤهل الأكاديمي</span>
                                    <span class="info-value" id="mt-qualification"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">اللغات المتقنة</span>
                                    <span class="info-value" id="mt-languages"></span>
                                </div>
                                <div class="info-row mt-3 pt-3 border-top">
                                    <span class="info-label">سنوات الخبرة (تعليم)</span>
                                    <span class="info-value"><span id="mt-exp-years" class="text-primary fs-5"></span> سنوات</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">ساعات التفرغ الأسبوعية</span>
                                    <span class="info-value"><span id="mt-work-hours" class="text-primary fs-5"></span> ساعة / أسبوعياً</span>
                                </div>
                            </div>
                        </div>

                        {{-- 3. القدرات التقنية والمرفقات --}}
                        <div class="col-12 col-lg-4">
                            <div class="detail-card">
                                <h6 class="detail-card-title"><i class="fa-solid fa-laptop text-primary fs-5"></i> القدرات التقنية والمرفقات</h6>
                                <div class="info-row">
                                    <span class="info-label">الخبرة في التعليم عن بعد</span>
                                    <span class="info-value" id="mt-online-exp"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">المهارات التقنية (الحاسوب)</span>
                                    <span class="info-value" id="mt-tech-skills"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">جودة وموثوقية الإنترنت</span>
                                    <span class="info-value" id="mt-internet"></span>
                                </div>

                                <div class="info-row mt-3 pt-3 border-top" id="mt-cv-container">
                                    <span class="info-label">السيرة الذاتية (CV)</span>
                                    </div>
                            </div>
                        </div>

                        {{-- 4. المسارات والإجازات (عرض كامل) --}}
                        <div class="col-12">
                            <div class="detail-card border-start border-4 border-primary shadow-sm bg-white">
                                <div class="row g-4">
                                    <div class="col-md-5 border-end-md">
                                        <h6 class="detail-card-title"><i class="fa-solid fa-layer-group text-primary fs-5"></i> المسارات المسندة للمعلم</h6>
                                        <div class="d-flex flex-wrap gap-2 mt-3" id="mt-tracks-container-modal">
                                            </div>
                                    </div>
                                    <div class="col-md-7">
                                        <h6 class="detail-card-title"><i class="fa-solid fa-certificate text-primary fs-5"></i> تفاصيل الإجازات والأسانيد</h6>
                                        <div class="p-3 bg-light rounded-3 border small text-dark fw-bold" id="mt-ijazas" style="white-space: pre-wrap; max-height: 180px; overflow-y: auto; line-height: 1.8;">
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 bg-white justify-content-center">
                <button type="button" class="btn btn-light rounded-pill px-5 fw-bold border shadow-sm" data-bs-dismiss="modal">إغلاق الملف</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // --- معاينة الصورة ---
    function previewImage(event, previewId, placeholderId) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById(previewId);
            var placeholder = document.getElementById(placeholderId);
            if(placeholder) placeholder.style.display = 'none';
            output.src = reader.result;
            output.style.display = 'block';
        }
        if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
    }

    // --- فلترة كروت المسارات ---
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.track-filter-btn');
        const trackItems = document.querySelectorAll('.track-item');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const filterValue = button.getAttribute('data-filter');

                trackItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-status') === filterValue) {
                        item.style.display = 'block';
                        setTimeout(() => { item.style.opacity = '1'; item.style.transform = 'scale(1)'; }, 10);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.95)';
                        setTimeout(() => { item.style.display = 'none'; }, 300);
                    }
                });
            });
        });

        // --- إخفاء التنبيهات تلقائياً ---
        setTimeout(() => {
            document.querySelectorAll('.custom-toast').forEach(toast => {
                toast.style.animation = "slideOutRight 0.5s ease-in forwards";
                setTimeout(() => toast.remove(), 500);
            });
        }, 5000);
    });

    // --- فلترة وبحث جدول المعلمين ---
    function filterTeachersTable() {
        const searchInput = document.getElementById('teacherSearch').value.toLowerCase();
        const trackFilter = document.getElementById('teacherTrackFilter').value;
        const rows = document.querySelectorAll('.teacher-row');
        const noResults = document.getElementById('noTeacherResults');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search');
            const rowTracks = row.getAttribute('data-tracks');

            const matchesSearch = rowSearch.includes(searchInput);
            const matchesTrack = trackFilter === 'all' || rowTracks.includes(trackFilter);

            if (matchesSearch && matchesTrack) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        noResults.style.display = visibleCount === 0 ? '' : 'none';
        document.getElementById('teachersCount').innerText = `${visibleCount} معلم`;
    }

    // --- قواميس الترجمة ---
    const dictGender = { 'male': 'ذكر', 'female': 'أنثى' };
    const dictLevels = { 'beginner': 'مبتدئ', 'intermediate': 'متوسط', 'advanced': 'متقدم', 'expert': 'خبير' };
    const dictNet    = { 'weak': 'ضعيف', 'acceptable': 'مقبول', 'good': 'جيد', 'excellent': 'ممتاز' };

    // --- فتح مودال تفاصيل المعلم (الاحترافي الشامل) ---
    function showTeacherDetails(teacher) {
        // --- 1. البيانات الأساسية ---
        const name = teacher.user?.name || 'غير معروف';
        document.getElementById('mt-id').innerText = teacher.id;
        document.getElementById('mt-name').innerText = name;
        document.getElementById('mt-email').innerText = teacher.user?.email || 'لا يوجد بريد مسجل';
        document.getElementById('mt-phone').innerText = teacher.user?.phone || 'غير متوفر';

        const salary = parseFloat(teacher.salary) || 0;
        document.getElementById('mt-salary').innerText = '$' + salary.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('mt-minutes').innerText = (teacher.minutes || 0) + ' دقيقة';

        // الصورة الرمزية
        const imgEl = document.getElementById('mt-avatar-img');
        const initEl = document.getElementById('mt-avatar-initials');
        if (teacher.computed_avatar) {
            imgEl.src = teacher.computed_avatar;
            imgEl.style.display = 'inline-flex';
            initEl.style.display = 'none';
        } else {
            imgEl.style.display = 'none';
            initEl.innerText = name.charAt(0);
            initEl.style.display = 'inline-flex';
        }

        // المسارات
        const tracksContainer = document.getElementById('mt-tracks-container-modal');
        tracksContainer.innerHTML = '';
        if (teacher.tracks && teacher.tracks.length > 0) {
            teacher.tracks.forEach(t => {
                tracksContainer.innerHTML += `<span class="badge bg-light text-dark border border-primary border-opacity-50 px-3 py-2 rounded-pill shadow-sm fs-6"><i class="fa-solid fa-check text-primary me-2"></i> ${t.name}</span>`;
            });
        } else {
            tracksContainer.innerHTML = '<span class="text-muted small">لا يوجد مسارات مرتبطة حالياً.</span>';
        }

        // --- 2. البيانات التفصيلية من Application ---
        const app = teacher.application || {};

        document.getElementById('mt-gender').innerText = dictGender[app.gender] || '-';
        document.getElementById('mt-origin').innerText = app.origin_country || '-';
        document.getElementById('mt-residence').innerText = app.residence_location || '-';
        document.getElementById('mt-qualification').innerText = app.qualification || '-';

        let langs = '-';
        if(app.languages) {
            try {
                const parsedLangs = typeof app.languages === 'string' ? JSON.parse(app.languages) : app.languages;
                langs = Array.isArray(parsedLangs) ? parsedLangs.join('، ') : parsedLangs;
            } catch(e) { langs = app.languages; }
        }
        document.getElementById('mt-languages').innerText = langs;

        document.getElementById('mt-exp-years').innerText = app.experience_years || '0';
        document.getElementById('mt-work-hours').innerText = app.work_hours || '0';
        document.getElementById('mt-online-exp').innerText = dictLevels[app.online_experience] || '-';
        document.getElementById('mt-tech-skills').innerText = dictLevels[app.tech_skills] || '-';
        document.getElementById('mt-internet').innerText = dictNet[app.internet_quality] || '-';
        document.getElementById('mt-ijazas').innerText = app.ijazas_text || 'لم يتم إرفاق تفاصيل الأسانيد والإجازات.';

        const cvContainer = document.getElementById('mt-cv-container');
        if(app.cv_pdf_path) {
            cvContainer.innerHTML = `<a href="/storage/${app.cv_pdf_path}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm mt-2 fw-bold"><i class="fa-solid fa-file-pdf me-2"></i> عرض ملف السيرة الذاتية</a>`;
        } else {
            cvContainer.innerHTML = `<span class="info-value text-muted small mt-2">لم يتم إرفاق ملف PDF</span>`;
        }

        new bootstrap.Modal(document.getElementById('teacherDetailsModal')).show();
    }
</script>
@endsection
