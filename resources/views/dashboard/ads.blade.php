@extends('dashboard.layouts.master')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1cc6a4 0%, #0d9488 100%);
        --glass-white: rgba(255, 255, 255, 0.2);
        --surface-color: #ffffff;
        --border-color: #f1f5f9;
        --text-main: #1e293b;
        --text-muted: #64748b;
    }

    /* --- Professional Banner Preview --- */
    .wartel-banner {
        border-radius: 24px;
        padding: 28px;
        color: white;
        display: flex;
        align-items: center;
        text-align: right;
        direction: rtl;
        position: relative;
        min-height: 145px;
        box-shadow: 0 12px 24px -8px rgba(13, 148, 136, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .speaker-icon-box {
        background: var(--glass-white);
        backdrop-filter: blur(8px);
        width: 64px;
        height: 64px;
        min-width: 64px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 20px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
    }

    .speaker-icon-box i { font-size: 24px; transform: rotate(-10deg); color: #fff; }
    .speaker-icon-box img { width: 100%; height: 100%; object-fit: cover; }

    .banner-content h4 { font-weight: 800; font-size: 1.4rem; margin-bottom: 4px; letter-spacing: -0.02em; }
    .banner-content p { font-size: 0.95rem; margin: 0; opacity: 0.85; font-weight: 400; }

    /* --- Ad Management Card --- */
    .ad-manage-wrapper {
        background: var(--surface-color);
        border-radius: 28px;
        border: 1px solid var(--border-color);
        padding: 10px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .ad-manage-wrapper:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .ad-controls {
        padding: 16px 12px 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* --- Toggle Switch Styling --- */
    .form-switch .form-check-input {
        width: 2.8em;
        height: 1.5em;
        cursor: pointer;
    }
    .form-switch .form-check-input:checked { background-color: #10b981; border-color: #10b981; }

    /* --- Action Buttons Group --- */
    .btn-action-group { display: flex; gap: 8px; }
    .action-btn {
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: var(--text-muted);
        transition: all 0.2s;
    }
    .action-btn:hover { background: #f8fafc; color: var(--text-main); border-color: #cbd5e1; }
    .action-btn.delete:hover { background: #fef2f2; color: #ef4444; border-color: #fee2e2; }

    /* --- Add Placeholder --- */
    .add-new-ad-placeholder {
        border: 2px dashed #e2e8f0;
        background: #f8fafc;
        border-radius: 28px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        min-height: 220px;
        transition: all 0.3s ease;
    }
    .add-new-ad-placeholder:hover {
        border-color: #0d9488;
        background: #f0fdfa;
        color: #0d9488;
    }
    .add-new-ad-placeholder i { font-size: 2.5rem; margin-bottom: 12px; opacity: 0.5; }

    @media (max-width: 768px) {
        .wartel-banner { padding: 20px; min-height: 130px; }
        .banner-content h4 { font-size: 1.2rem; }
    }
</style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100">
    <h5 class="m-0 fw-bold">إدارة المحتوى الإعلاني</h5>
</div>
@endsection

@section('content')
<div class="container-fluid py-4">

    {{-- Alert Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-radius: 15px;">
            <ul class="mb-0 small">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-radius: 15px;">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        @foreach ($ads as $ad)
        <div class="col-12 col-xl-6">
            <div class="ad-manage-wrapper">
                {{-- Preview Banner --}}
                <div class="wartel-banner" style="background: {{ $ad->bg_color ?? 'var(--primary-gradient)' }}">
                    <div class="speaker-icon-box">
                        @if($ad->image)
                            <img src="{{ asset('storage/' . $ad->image) }}" alt="ad icon">
                        @else
                            <i class="fa-solid fa-bullhorn"></i>
                        @endif
                    </div>
                    <div class="banner-content">
                        <h4>{{ $ad->title }}</h4>
                        <p>{{ $ad->subtitle }}</p>
                    </div>

                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge rounded-pill {{ $ad->status == 'active' ? 'bg-success' : 'bg-dark' }} px-3 py-2 shadow-sm">
                            {{ $ad->status == 'active' ? 'نشط الآن' : 'متوقف مؤقتاً' }}
                        </span>
                    </div>
                </div>

                {{-- Admin Controls --}}
                <div class="ad-controls">
                    <form action="{{ route('ads.update', $ad->id) }}" method="POST" id="statusForm{{ $ad->id }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="title" value="{{ $ad->title }}">
                        <input type="hidden" name="status" value="{{ $ad->status == 'active' ? 'inactive' : 'active' }}">
                        <div class="form-check form-switch d-flex align-items-center gap-3">
                            <input class="form-check-input shadow-none" type="checkbox" onchange="document.getElementById('statusForm{{ $ad->id }}').submit()" {{ $ad->status == 'active' ? 'checked' : '' }}>
                            <span class="small fw-bold text-muted">حالة الظهور</span>
                        </div>
                    </form>

                    <div class="btn-action-group">
                        <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editAd{{ $ad->id }}" title="تعديل">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        {{-- Delete Trigger --}}
                        <button class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteAd{{ $ad->id }}" title="حذف">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Professional Edit Modal --}}
        <div class="modal fade" id="editAd{{ $ad->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('ads.update', $ad->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    @csrf @method('PUT')
                    <div class="modal-header border-0 p-4 pb-0">
                        <h5 class="fw-bold m-0">تخصيص الإعلان</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small">العنوان الرئيسي</label>
                                <input type="text" name="title" class="form-control rounded-3 py-2 shadow-none" value="{{ $ad->title }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">العنوان الفرعي</label>
                                <input type="text" name="subtitle" class="form-control rounded-3 py-2 shadow-none" value="{{ $ad->subtitle }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small">لون السمة</label>
                                <input type="color" name="bg_color" class="form-control form-control-color w-100 rounded-3 shadow-none border-0 p-0" value="{{ $ad->bg_color ?? '#1cc6a4' }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small">تحديث الأيقونة</label>
                                <input type="file" name="image" class="form-control rounded-3 shadow-none border" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">تحديث البيانات</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Professional Delete Modal --}}
        <div class="modal fade" id="deleteAd{{ $ad->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    <div class="modal-header border-0 p-4 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fa-solid fa-circle-exclamation text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">حذف الإعلان؟</h5>
                        <p class="text-muted small">هل أنت متأكد من حذف "{{ $ad->title }}"؟ لا يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <form action="{{ route('ads.destroy', $ad->id) }}" method="POST" class="w-100">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold rounded-pill mb-2">تأكيد الحذف</button>
                            <button type="button" class="btn btn-light w-100 py-2 fw-bold rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <div class="col-12 col-xl-6">
            <div class="add-new-ad-placeholder shadow-sm" data-bs-toggle="modal" data-bs-target="#addAdModal">
                <i class="fa-solid fa-plus"></i>
                <span class="fw-bold text-muted">إضافة بنر ترويجي جديد</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" id="addAdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('ads.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            @csrf
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold m-0">بنر جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold small">العنوان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3 py-2 shadow-none" placeholder="أدخل العنوان الترويجي" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small">الوصف المختصر</label>
                        <input type="text" name="subtitle" class="form-control rounded-3 py-2 shadow-none" placeholder="تفاصيل العرض أو البرنامج">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">لون الخلفية</label>
                        <input type="color" name="bg_color" class="form-control form-control-color w-100 rounded-3 border-0 p-0 shadow-none" value="#1cc6a4">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">أيقونة العرض</label>
                        <input type="file" name="image" class="form-control rounded-3 shadow-none border" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold rounded-pill">تأكيد ونشر الإعلان</button>
            </div>
        </form>
    </div>
</div>
@endsection
