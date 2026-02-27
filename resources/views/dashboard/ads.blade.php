@extends('dashboard.layouts.master')

@section('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #2dd4bf 0%, #0d9488 100%);
            --glass-white: rgba(255, 255, 255, 0.2);
            --surface-color: #ffffff;
            --border-color: #f1f5f9;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        /* --- نظام التنبيهات المنبثقة --- */
        .fixed-alert-container { position: fixed; top: 25px; right: 25px; z-index: 10000; width: 100%; max-width: 350px; pointer-events: none; }
        .custom-toast {
            pointer-events: auto; background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px); border-radius: 16px; margin-bottom: 15px;
            overflow: hidden; position: relative; border-left: 5px solid transparent;
            animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); direction: rtl;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }
        .custom-toast.success { border-left-color: #0d9488; }
        .custom-toast.error { border-left-color: #e11d48; }
        .toast-content { padding: 15px 20px; display: flex; align-items: center; gap: 14px; }
        .toast-icon { font-size: 1.8rem; }
        .success .toast-icon { color: #0d9488; }
        .error .toast-icon { color: #e11d48; }
        .toast-body { flex-grow: 1; text-align: right; }
        .toast-title { display: block; font-weight: 800; font-size: 0.95rem; color: var(--text-main); }
        .toast-message { margin: 0; font-size: 0.85rem; color: var(--text-muted); }
        .toast-close { background: none; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; order: -1; transition: 0.2s; }
        .toast-close:hover { color: #475569; }
        .toast-progress { height: 3px; width: 100%; background: #f1f5f9; position: absolute; bottom: 0; right: 0; }
        .toast-progress::before { content: ""; position: absolute; bottom: 0; right: 0; height: 100%; width: 100%; }
        .success .toast-progress::before { background: #0d9488; animation: progressRun 5s linear forwards; }
        .error .toast-progress::before { background: #e11d48; animation: progressRun 5s linear forwards; }

        @keyframes progressRun { from { width: 100%; } to { width: 0%; } }
        @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- التصميم الاحترافي للبنر --- */
        .premium-ad-banner {
            border-radius: 28px;
            padding: 25px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
            direction: rtl;
            text-align: right;
            min-height: 140px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            gap: 20px;
        }

        /* تأثيرات تزيينية داخل البنر */
        .premium-ad-banner::after {
            content: ''; position: absolute; top: -50%; right: -20%; width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); border-radius: 50%; pointer-events: none;
        }

        .premium-ad-banner .ad-text-content { flex: 1; position: relative; z-index: 2; }
        .premium-ad-banner h4 { font-weight: 800; font-size: clamp(1.1rem, 2vw, 1.4rem); margin-bottom: 6px; letter-spacing: -0.01em; line-height: 1.4; }
        .premium-ad-banner p { font-size: clamp(0.85rem, 1.2vw, 0.95rem); font-weight: 500; margin: 0; opacity: 0.9; line-height: 1.5; }

        /* شارة الكوبون (Coupon Badge) */
        .ad-coupon-badge {
            display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px); padding: 5px 12px; border-radius: 8px;
            font-size: 0.8rem; font-weight: 700; margin-top: 10px; border: 1px dashed rgba(255,255,255,0.4);
        }

        .premium-ad-banner .ad-icon-circle {
            width: 70px; height: 70px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.2);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            backdrop-filter: blur(8px); position: relative; z-index: 2; box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .premium-ad-banner .ad-icon-circle i { font-size: 1.8rem; color: #ffffff; transform: rotate(-10deg); }
        .premium-ad-banner .ad-icon-circle img { width: 55%; height: 55%; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)); }

        @media (max-width: 576px) {
            .premium-ad-banner { flex-direction: column-reverse; text-align: center; padding: 25px 20px; min-height: 180px; }
            .premium-ad-banner .ad-icon-circle { width: 60px; height: 60px; margin-bottom: 10px; }
            .fixed-alert-container { right: 10px; left: 10px; top: 10px; width: auto; max-width: none; }
        }

        /* --- نقاط السلايدر --- */
        .custom-indicators { position: relative; bottom: 0; margin-top: 20px; margin-bottom: 0; justify-content: center; gap: 8px; }
        .custom-indicators button { width: 8px !important; height: 8px !important; border-radius: 50% !important; background-color: #cbd5e1 !important; border: none !important; opacity: 1 !important; margin: 0 !important; transition: all 0.3s ease !important; }
        .custom-indicators button.active { background-color: #0d9488 !important; width: 24px !important; border-radius: 10px !important; }

        /* --- كرت إدارة الإعلان --- */
        .ad-manage-wrapper {
            background: var(--surface-color); border-radius: 32px; border: 1px solid var(--border-color);
            padding: 12px; transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column;
        }
        .ad-manage-wrapper:hover { transform: translateY(-5px); box-shadow: 0 20px 30px -5px rgba(0, 0, 0, 0.05); border-color: #e2e8f0; }
        .ad-controls { padding: 15px 10px 5px; display: flex; justify-content: space-between; align-items: center; margin-top: auto; flex-wrap: wrap; gap: 10px; }

        .form-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: #10b981; border-color: #10b981; }

        .btn-action-group { display: flex; gap: 8px; }
        .action-btn { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; color: var(--text-muted); transition: all 0.2s; }
        .action-btn:hover { background: #f8fafc; color: var(--text-main); border-color: #cbd5e1; transform: translateY(-2px); }
        .action-btn.delete:hover { background: #fef2f2; color: #ef4444; border-color: #fee2e2; }

        /* --- الفلاتر --- */
        .filter-scroll-wrapper { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .filter-scroll-wrapper::-webkit-scrollbar { display: none; }
        .filter-btn { border-radius: 50px; padding: 8px 22px; font-weight: 700; font-size: 0.85rem; white-space: nowrap; box-shadow: none !important; border: 1px solid #e2e8f0; background: white; color: var(--text-muted); transition: 0.3s; }
        .filter-btn.active { background: #0d9488; color: white; border-color: #0d9488; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2) !important; }
        .filter-btn:hover:not(.active) { border-color: #0d9488; color: #0d9488; }

        /* --- زر الإضافة --- */
        .add-new-ad-placeholder {
            border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 32px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            cursor: pointer; min-height: 220px; height: 100%; transition: all 0.3s ease; text-align: center; padding: 20px;
        }
        .add-new-ad-placeholder:hover { border-color: #0d9488; background: #f0fdfa; color: #0d9488; transform: translateY(-4px); }
        .add-new-ad-placeholder i { font-size: 3rem; margin-bottom: 15px; opacity: 0.5; }

        /* --- المودال والحقول --- */
        .custom-input { border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 18px; font-size: 0.95rem; background: #f8fafc; transition: 0.3s; }
        .custom-input:focus { background: #fff; border-color: #0d9488; box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); outline: none; }
        .form-label { font-weight: 700; color: var(--text-main); font-size: 0.85rem; margin-bottom: 8px; }

        .image-preview-box { width: 100%; height: 130px; border-radius: 16px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #fff; position: relative; }
        .image-preview-box img { max-width: 80%; max-height: 80%; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
        .image-preview-box .placeholder-text { color: #94a3b8; font-size: 0.85rem; font-weight: 600; }
    </style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100">
    <h5 class="m-0 fw-bold text-dark">إدارة المحتوى الإعلاني</h5>
</div>
@endsection

@section('content')

{{-- التنبيهات --}}
<div class="fixed-alert-container">
    @if ($errors->any())
        <div class="custom-toast error shadow-lg">
            <div class="toast-content">
                <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                <div class="toast-body">
                    <span class="toast-title">يوجد خطأ</span>
                    @foreach ($errors->all() as $error) <p class="toast-message">{{ $error }}</p> @endforeach
                </div>
                <div class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif

    @if (session('success'))
        <div class="custom-toast success shadow-lg">
            <div class="toast-content">
                <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                <div class="toast-body"><span class="toast-title">تم بنجاح</span><p class="toast-message">{{ session('success') }}</p></div>
                <div class="toast-icon"><i class="fa-solid fa-circle-check"></i></div>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif
</div>

<div class="container-fluid py-3 py-md-4">

    {{-- 1. Mobile Preview Section (Active Ads Carousel) --}}
    @php
        $activeAds = collect($ads)->where('status', 'active')->values();
    @endphp

    @if($activeAds->count() > 0)
    <div class="mb-5 pb-4 border-bottom">
        <h6 class="fw-bold text-muted mb-4"><i class="fa-solid fa-mobile-screen me-2 text-primary"></i>معاينة الإعلانات النشطة (تطبيق الموبايل)</h6>

        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                <div id="activeAdsCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner p-2">
                        @foreach($activeAds as $index => $ad)
                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                            <div class="premium-ad-banner" style="background: {{ $ad->bg_color ?? 'var(--primary-gradient)' }}">
                                <div class="ad-text-content">
                                    <h4>{{ $ad->title }}</h4>
                                    @if($ad->subtitle) <p>{{ $ad->subtitle }}</p> @endif

                                    {{-- عرض الكوبون إن وجد --}}
                                    @if($ad->coupon)
                                        <div class="ad-coupon-badge">
                                            <i class="fa-solid fa-ticket text-warning"></i> كود الخصم: {{ $ad->coupon->code }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ad-icon-circle">
                                    @if($ad->image)
                                        <img src="{{ asset('storage/' . $ad->image) }}" alt="ad icon">
                                    @else
                                        <i class="fa-solid fa-bullhorn"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="carousel-indicators custom-indicators">
                        @foreach($activeAds as $index => $ad)
                        <button type="button" data-bs-target="#activeAdsCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 2. Filter Section --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h5 class="fw-bold m-0 text-dark">لوحة الإعلانات</h5>
        <div class="filter-scroll-wrapper">
            <button type="button" class="filter-btn active" data-filter="all">عرض الكل</button>
            <button type="button" class="filter-btn" data-filter="active">النشطة</button>
            <button type="button" class="filter-btn" data-filter="inactive">المتوقفة</button>
        </div>
    </div>

    {{-- 3. Individual Ads Grid --}}
    <div class="row g-3 g-xl-4" id="adsGrid">

        {{-- Add New Ad Button --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="add-new-ad-placeholder shadow-sm" data-bs-toggle="modal" data-bs-target="#addAdModal">
                <i class="fa-solid fa-circle-plus text-primary"></i>
                <h5 class="fw-bold text-dark m-0">إنشاء إعلان جديد</h5>
                <span class="small text-muted mt-2">انقر هنا لتصميم بنر ترويجي</span>
            </div>
        </div>

        @foreach ($ads as $ad)
        <div class="col-12 col-md-6 col-xl-4 ad-grid-item" data-status="{{ $ad->status }}">
            <div class="ad-manage-wrapper">
                {{-- Banner Preview in Grid --}}
                <div class="premium-ad-banner" style="background: {{ $ad->bg_color ?? 'var(--primary-gradient)' }}; min-height: 120px; border-radius: 22px; padding: 20px;">
                    <div class="ad-text-content">
                        <h4 style="font-size: 1.15rem;">{{ $ad->title }}</h4>
                        @if($ad->subtitle)<p style="font-size: 0.85rem;">{{ Str::limit($ad->subtitle, 40) }}</p>@endif

                        @if($ad->coupon)
                            <div class="ad-coupon-badge" style="font-size: 0.7rem; padding: 4px 10px;">
                                <i class="fa-solid fa-ticket"></i> {{ $ad->coupon->code }}
                            </div>
                        @endif
                    </div>
                    <div class="ad-icon-circle" style="width: 55px; height: 55px;">
                        @if($ad->image)
                            <img src="{{ asset('storage/' . $ad->image) }}" alt="ad icon">
                        @else
                            <i class="fa-solid fa-bullhorn" style="font-size: 1.3rem;"></i>
                        @endif
                    </div>
                </div>

                {{-- Admin Controls --}}
                <div class="ad-controls">
                    <form action="{{ route('ads.update', $ad->id) }}" method="POST" id="statusForm{{ $ad->id }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="title" value="{{ $ad->title }}">
                        <input type="hidden" name="status" value="{{ $ad->status == 'active' ? 'inactive' : 'active' }}">
                        <div class="form-check form-switch d-flex align-items-center gap-2 m-0" title="تغيير حالة ظهور الإعلان">
                            <input class="form-check-input shadow-none m-0 me-2" type="checkbox" onchange="document.getElementById('statusForm{{ $ad->id }}').submit()" {{ $ad->status == 'active' ? 'checked' : '' }}>
                            <span class="d-block fw-bold mb-0 {{ $ad->status == 'active' ? 'text-success' : 'text-muted' }}" style="font-size: 0.85rem;">{{ $ad->status == 'active' ? 'نشط' : 'متوقف' }}</span>
                        </div>
                    </form>

                    <div class="btn-action-group">
                        <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editAd{{ $ad->id }}" title="تعديل الإعلان">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteAd{{ $ad->id }}" title="حذف نهائي">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Professional Edit Modal (Scrollable Fixed) --}}
        <div class="modal fade" id="editAd{{ $ad->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                {{-- Form acts as the modal-content to ensure correct scrolling layout --}}
                <form action="{{ route('ads.update', $ad->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    @csrf @method('PUT')

                    <div class="modal-header border-0 p-4 pb-3" style="background: #f8fafc; border-radius: 24px 24px 0 0;">
                        <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>تحديث الإعلان</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4 custom-scrollbar">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">العنوان الرئيسي <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="custom-input w-100" value="{{ $ad->title }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">الوصف المختصر</label>
                                <input type="text" name="subtitle" class="custom-input w-100" value="{{ $ad->subtitle }}">
                            </div>

                            {{-- قائمة الكوبونات المتاحة --}}
                            <div class="col-12">
                                <label class="form-label">ربط بكوبون خصم (اختياري)</label>
                                <select name="coupon_id" class="form-select custom-input">
                                    <option value="">-- بدون ارتباط --</option>
                                    @if(isset($coupons) && $coupons->count() > 0)
                                        @foreach($coupons as $coupon)
                                            <option value="{{ $coupon->id }}" {{ $ad->coupon_id == $coupon->id ? 'selected' : '' }}>
                                                {{ $coupon->code }} (خصم {{ $coupon->percent }}%)
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">لون السمة</label>
                                <input type="color" name="bg_color" class="form-control form-control-color w-100 rounded-3 shadow-none border-0 p-0" value="{{ $ad->bg_color ?? '#0d9488' }}" style="height: 48px; border-radius: 14px !important; cursor: pointer;">
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">تحديث الأيقونة</label>
                                <input type="file" name="image" class="form-control rounded-3 shadow-none border" style="padding: 10px; font-size: 0.9rem;" accept="image/*" onchange="previewImage(event, 'preview-edit-{{ $ad->id }}', 'placeholder-edit-{{ $ad->id }}')">
                            </div>

                            <div class="col-12">
                                <label class="form-label text-muted small mt-2">معاينة الأيقونة الحالية</label>
                                <div class="image-preview-box">
                                    <span class="placeholder-text" id="placeholder-edit-{{ $ad->id }}" style="display: {{ $ad->image ? 'none' : 'block' }}">لا توجد أيقونة مخصصة</span>
                                    <img id="preview-edit-{{ $ad->id }}" src="{{ $ad->image ? asset('storage/' . $ad->image) : '' }}" style="display: {{ $ad->image ? 'block' : 'none' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 pt-3" style="background: #fff; border-radius: 0 0 24px 24px;">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Professional Delete Modal --}}
        <div class="modal fade" id="deleteAd{{ $ad->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    <div class="modal-header border-0 p-4 pb-0">
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-center pt-0">
                        <div class="mb-3">
                            <i class="fa-solid fa-circle-exclamation text-danger opacity-75" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">تأكيد الحذف؟</h5>
                        <p class="text-muted small">هل أنت متأكد من حذف الإعلان <strong>"{{ $ad->title }}"</strong>؟ لا يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <form action="{{ route('ads.destroy', $ad->id) }}" method="POST" class="w-100 m-0">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold rounded-pill mb-2 shadow-sm">نعم، احذف الإعلان</button>
                            <button type="button" class="btn btn-light w-100 py-2 fw-bold rounded-pill border" data-bs-dismiss="modal">تراجع</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@section('modals')
{{-- Add Ad Modal (Scrollable Fixed) --}}
<div class="modal fade" id="addAdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('ads.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            @csrf
            <div class="modal-header border-0 p-4 pb-3" style="background: #f0fdfa; border-radius: 24px 24px 0 0;">
                <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-plus-circle text-primary me-2"></i>إنشاء إعلان جديد</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 custom-scrollbar">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">العنوان الرئيسي <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="custom-input w-100" placeholder="مثال: خصم خاص للمشتركين الجدد" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">الوصف المختصر</label>
                        <input type="text" name="subtitle" class="custom-input w-100" placeholder="مثال: احصل على خصم 20% عند استخدام الكود">
                    </div>

                    {{-- قائمة الكوبونات --}}
                    <div class="col-12">
                        <label class="form-label">ربط بكوبون خصم (اختياري)</label>
                        <select name="coupon_id" class="form-select custom-input">
                            <option value="">-- بدون ارتباط --</option>
                            @if(isset($coupons) && $coupons->count() > 0)
                                @foreach($coupons as $coupon)
                                    <option value="{{ $coupon->id }}">{{ $coupon->code }} (خصم {{ $coupon->percent }}%)</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label">لون الخلفية</label>
                        <input type="color" name="bg_color" class="form-control form-control-color w-100 rounded-3 shadow-none border-0 p-0" value="#0d9488" style="height: 48px; border-radius: 14px !important; cursor: pointer;">
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label">أيقونة العرض</label>
                        <input type="file" name="image" class="form-control rounded-3 shadow-none border" accept="image/*" style="padding: 10px; font-size: 0.9rem;" onchange="previewImage(event, 'preview-add', 'placeholder-add')">
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small mt-2">معاينة الأيقونة</label>
                        <div class="image-preview-box">
                            <span class="placeholder-text" id="placeholder-add">سيتم عرض الأيقونة المرفوعة هنا</span>
                            <img id="preview-add" src="" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-3" style="background: #fff; border-radius: 0 0 24px 24px;">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">تأكيد ونشر الإعلان</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // --- إخفاء التنبيهات تلقائياً ---
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.querySelectorAll('.custom-toast').forEach(toast => {
                toast.style.animation = "slideOutRight 0.5s ease-in forwards";
                setTimeout(() => toast.remove(), 500);
            });
        }, 5000);
    });

    // --- معاينة الصورة قبل الرفع في المودال ---
    function previewImage(event, previewId, placeholderId) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById(previewId);
            var placeholder = document.getElementById(placeholderId);
            output.src = reader.result;
            output.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        if(event.target.files[0]){
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    // --- فلترة شبكة الإعلانات ---
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const gridItems = document.querySelectorAll('.ad-grid-item');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');
                gridItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-status') === filterValue) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endsection
