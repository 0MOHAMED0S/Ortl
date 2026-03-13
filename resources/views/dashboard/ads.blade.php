@extends('dashboard.layouts.master')

@section('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #2dd4bf 0%, #0d9488 100%);
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
        .toast-progress { height: 3px; width: 100%; background: #f1f5f9; position: absolute; bottom: 0; right: 0; }
        .toast-progress::before { content: ""; position: absolute; bottom: 0; right: 0; height: 100%; width: 100%; }
        .success .toast-progress::before { background: #0d9488; animation: progressRun 5s linear forwards; }
        .error .toast-progress::before { background: #e11d48; animation: progressRun 5s linear forwards; }

        @keyframes progressRun { from { width: 100%; } to { width: 0%; } }
        @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- تصميم البنر الجديد (صورة كاملة) --- */
        .premium-ad-banner {
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            background: #f1f5f9;
            aspect-ratio: 16 / 7; /* نسبة عرض البنر المثالية للموبايل */
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        }

        .premium-ad-banner img.ad-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .ad-link-indicator {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            color: white;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* --- كرت الإدارة --- */
        .ad-manage-wrapper {
            background: var(--surface-color);
            border-radius: 30px;
            border: 1px solid var(--border-color);
            padding: 12px;
            transition: all 0.3s ease;
            height: 100%;
        }

        .ad-manage-wrapper:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); }

        .ad-controls {
            padding: 15px 10px 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-switch .form-check-input { width: 2.8em; height: 1.4em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: #10b981; border-color: #10b981; }

        .btn-action-group { display: flex; gap: 8px; }
        .action-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; color: var(--text-muted); transition: 0.2s; }
        .action-btn:hover { background: #f8fafc; color: #0d9488; border-color: #0d9488; }
        .action-btn.delete:hover { background: #fef2f2; color: #ef4444; border-color: #fee2e2; }

        /* --- زر الإضافة --- */
        .add-new-ad-placeholder {
            border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 30px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            cursor: pointer; min-height: 200px; height: 100%; transition: 0.3s; text-align: center;
        }
        .add-new-ad-placeholder:hover { border-color: #0d9488; background: #f0fdfa; color: #0d9488; }
        .add-new-ad-placeholder i { font-size: 2.5rem; margin-bottom: 10px; opacity: 0.6; }

        /* --- الفلاتر --- */
        .filter-btn { border-radius: 50px; padding: 6px 20px; font-weight: 700; font-size: 0.8rem; border: 1px solid #e2e8f0; background: white; color: var(--text-muted); transition: 0.3s; }
        .filter-btn.active { background: #0d9488; color: white; border-color: #0d9488; }

        /* --- المودال --- */
        .custom-input { border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 15px; font-size: 0.9rem; background: #f8fafc; width: 100%; transition: 0.3s; }
        .custom-input:focus { background: #fff; border-color: #0d9488; outline: none; box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); }
        .image-preview-box { width: 100%; aspect-ratio: 16/7; border-radius: 14px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #fff; margin-top: 10px; }
        .image-preview-box img { width: 100%; height: 100%; object-fit: cover; }
    </style>
@endsection

@section('content')

{{-- التنبيهات --}}
<div class="fixed-alert-container">
    @if ($errors->any())
        <div class="custom-toast error">
            <div class="toast-content">
                <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                <div class="toast-body">
                    <span class="toast-title">خطأ في البيانات</span>
                    @foreach ($errors->all() as $error) <p class="toast-message">{{ $error }}</p> @endforeach
                </div>
                <div class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif
    @if (session('success'))
        <div class="custom-toast success">
            <div class="toast-content">
                <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                <div class="toast-body"><span class="toast-title">تم بنجاح</span><p class="toast-message">{{ session('success') }}</p></div>
                <div class="toast-icon"><i class="fa-solid fa-circle-check"></i></div>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif
</div>

<div class="container-fluid py-4">

    {{-- 1. معاينة الموبايل --}}
    @php $activeAds = $ads->where('status', 'active'); @endphp
    @if($activeAds->count() > 0)
    <div class="mb-5 pb-4 border-bottom">
        <h6 class="fw-bold text-muted mb-4"><i class="fa-solid fa-mobile-screen me-2 text-primary"></i>معاينة بنرات التطبيق النشطة</h6>
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div id="activeAdsCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner shadow-sm" style="border-radius: 25px;">
                        @foreach($activeAds as $index => $ad)
                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                            <div class="premium-ad-banner">
                                <img src="{{ asset('storage/' . $ad->image) }}" class="ad-cover">
                                @if($ad->link) <span class="ad-link-indicator"><i class="fa-solid fa-link"></i> مرتبط برابط</span> @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 2. التحكم والفلاتر --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0">المحتوى الإعلاني</h5>
        <div class="d-flex gap-2">
            <button type="button" class="filter-btn active" data-filter="all">الكل</button>
            <button type="button" class="filter-btn" data-filter="active">نشط</button>
            <button type="button" class="filter-btn" data-filter="inactive">متوقف</button>
        </div>
    </div>

    {{-- 3. شبكة الإعلانات --}}
    <div class="row g-4" id="adsGrid">
        {{-- كرت الإضافة --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="add-new-ad-placeholder shadow-sm" data-bs-toggle="modal" data-bs-target="#addAdModal">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <h6 class="fw-bold m-0">رفع بنر جديد</h6>
                <span class="small text-muted mt-1">المقاس المفضل 16:7</span>
            </div>
        </div>

        @foreach ($ads as $ad)
        <div class="col-12 col-md-6 col-xl-4 ad-grid-item" data-status="{{ $ad->status }}">
            <div class="ad-manage-wrapper">
                <div class="premium-ad-banner">
                    <img src="{{ asset('storage/' . $ad->image) }}" class="ad-cover">
                </div>

                <div class="ad-controls">
                    <form action="{{ route('ads.update', $ad->id) }}" method="POST" class="d-flex align-items-center">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ $ad->status == 'active' ? 'inactive' : 'active' }}">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $ad->status == 'active' ? 'checked' : '' }}>
                            <span class="small fw-bold ms-2">{{ $ad->status == 'active' ? 'نشط' : 'متوقف' }}</span>
                        </div>
                    </form>

                    <div class="btn-action-group">
                        <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editAd{{ $ad->id }}"><i class="fa-solid fa-pen"></i></button>
                        <button class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteAd{{ $ad->id }}"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>

        {{-- مودال التعديل --}}
        <div class="modal fade" id="editAd{{ $ad->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('ads.update', $ad->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ $ad->status }}">
                    <div class="modal-header border-0 p-4 pb-0">
                        <h6 class="fw-bold m-0">تعديل البنر</h6>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <label class="small fw-bold mb-2">تحديث الصورة (اختياري)</label>
                        <input type="file" name="image" class="form-control custom-input mb-3" accept="image/*" onchange="previewImage(event, 'preview-edit-{{ $ad->id }}')">

                        <label class="small fw-bold mb-2">رابط التوجيه (اختياري)</label>
                        <input type="url" name="link" class="custom-input" value="{{ $ad->link }}" placeholder="https://...">

                        <div class="image-preview-box mt-3">
                            <img id="preview-edit-{{ $ad->id }}" src="{{ asset('storage/' . $ad->image) }}">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- مودال الحذف --}}
        <div class="modal fade" id="deleteAd{{ $ad->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-body p-4 text-center">
                        <i class="fa-solid fa-trash-can text-danger mb-3" style="font-size: 2.5rem;"></i>
                        <h6 class="fw-bold">تأكيد الحذف؟</h6>
                        <p class="small text-muted">سيتم حذف هذا الإعلان نهائياً من قاعدة البيانات.</p>
                        <form action="{{ route('ads.destroy', $ad->id) }}" method="POST" class="mt-3">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100 mb-2 rounded-pill fw-bold">نعم، احذف</button>
                            <button type="button" class="btn btn-light w-100 rounded-pill fw-bold" data-bs-dismiss="modal">إلغاء</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- مودال الإضافة --}}
<div class="modal fade" id="addAdModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('ads.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            @csrf
            <div class="modal-header border-0 p-4 pb-0">
                <h6 class="fw-bold m-0">إنشاء بنر إعلاني جديد</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <label class="small fw-bold mb-2">صورة البنر (مطلوب) <span class="text-danger">*</span></label>
                <input type="file" name="image" class="form-control custom-input mb-3" accept="image/*" required onchange="previewImage(event, 'preview-add')">

                <label class="small fw-bold mb-2">رابط التوجيه عند الضغط (اختياري)</label>
                <input type="url" name="link" class="custom-input" placeholder="https://example.com/offers">

                <div class="image-preview-box mt-3">
                    <span class="small text-muted" id="placeholder-add">معاينة الصورة ستظهر هنا</span>
                    <img id="preview-add" src="" style="display: none;">
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">تأكيد ونشر الإعلان</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // --- معاينة الصور ---
    function previewImage(event, previewId) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById(previewId);
            const placeholder = document.getElementById('placeholder-add');
            output.src = reader.result;
            output.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
    }

    // --- نظام الفلترة ---
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('.ad-grid-item').forEach(item => {
                item.style.display = (filter === 'all' || item.dataset.status === filter) ? 'block' : 'none';
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
</script>
@endsection
