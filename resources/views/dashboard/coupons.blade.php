@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --bg-light: #f8fafc;
        }

        /* --- الأساسيات والجماليات --- */
        .coupon-ticket {
            background: #fff; border-radius: 24px; position: relative; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid #f1f5f9;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; height: 100%;
        }
        .coupon-ticket:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(99, 102, 241, 0.1); border-color: var(--primary-color); }

        .coupon-top {
            background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
            padding: 35px 20px 25px; text-align: center; position: relative;
            border-bottom: 2px dashed #e2e8f0;
        }

        .discount-circle {
            width: 90px; height: 90px; background: var(--primary-color); color: #fff;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 2rem; font-weight: 900;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
        }
        .discount-circle small { font-size: 1.1rem; margin-right: 2px; }

        /* Ticket Cutouts */
        .coupon-ticket::before, .coupon-ticket::after {
            content: ""; position: absolute; top: 165px; width: 30px; height: 30px;
            background-color: var(--bg-light); border-radius: 50%; z-index: 2;
        }
        .coupon-ticket::before { left: -15px; }
        .coupon-ticket::after { right: -15px; }

        /* Copy Code Box Professional */
        .coupon-code-wrapper {
            display: inline-flex; align-items: center; background: #f1f5f9;
            border: 1px dashed #cbd5e1; border-radius: 14px; overflow: hidden;
            transition: all 0.2s; cursor: pointer;
        }
        .coupon-code-wrapper:hover { border-color: var(--primary-color); background: #eef2ff; }
        .coupon-code-text {
            padding: 10px 20px; font-family: 'Monaco', monospace; font-weight: 800;
            color: #1e293b; letter-spacing: 2px; font-size: 1.15rem;
        }
        .coupon-copy-btn {
            background: #e2e8f0; color: #64748b; padding: 12px 15px;
            display: flex; align-items: center; justify-content: center; transition: 0.3s;
        }
        .coupon-code-wrapper:hover .coupon-copy-btn { background: var(--primary-color); color: white; }
        .coupon-copy-btn.copied { background: var(--success-color); color: white; }

        .coupon-body { padding: 30px 25px; flex-grow: 1; }

        .coupon-footer {
            padding: 18px 25px; background: #f8fafc; border-top: 1px solid #f1f5f9;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* --- نظام التنبيهات الاحترافي (Toasts) --- */
        .fixed-alert-container { position: fixed; top: 25px; right: 25px; z-index: 10000; width: 100%; max-width: 350px; pointer-events: none; }
        .custom-toast {
            pointer-events: auto; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);
            border-radius: 14px; margin-bottom: 15px; overflow: hidden; border-left: 5px solid #10b981;
            animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); direction: rtl;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .custom-toast.error { border-left-color: #ef4444; }
        .toast-content { padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
        .toast-icon { font-size: 1.6rem; }
        .success .toast-icon { color: #10b981; }
        .error .toast-icon { color: #ef4444; }
        .toast-body { flex-grow: 1; text-align: right; }
        .toast-title { display: block; font-weight: 800; font-size: 0.95rem; color: #1e293b; }
        .toast-message { margin: 0; font-size: 0.85rem; color: #64748b; }
        .toast-close { background: none; border: none; font-size: 1.3rem; color: #94a3b8; cursor: pointer; order: -1; }
        .toast-progress { height: 3px; width: 100%; background: #f1f5f9; position: absolute; bottom: 0; }
        .toast-progress::before { content: ""; position: absolute; bottom: 0; right: 0; height: 100%; width: 100%; }
        .success .toast-progress::before { background: #10b981; animation: progressRun 5s linear forwards; }
        .error .toast-progress::before { background: #ef4444; animation: progressRun 5s linear forwards; }

        @keyframes progressRun { from { width: 100%; } to { width: 0%; } }
        @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- الفلاتر وأشرطة البحث --- */
        .filter-scroll-wrapper { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; }
        .filter-scroll-wrapper::-webkit-scrollbar { display: none; }
        .filter-badge {
            padding: 8px 22px; border-radius: 50px; background: #fff; border: 1px solid #e2e8f0;
            font-size: 0.9rem; font-weight: 700; color: #64748b; cursor: pointer; transition: 0.3s; white-space: nowrap;
        }
        .filter-badge.active { background: var(--primary-color); color: #fff; border-color: var(--primary-color); box-shadow: 0 4px 15px rgba(99, 102, 241, 0.25); }
        .filter-badge:hover:not(.active) { border-color: var(--primary-color); color: var(--primary-color); }

        .search-box { position: relative; flex-grow: 1; min-width: 250px; }
        .search-box input { padding-right: 45px; border-radius: 50px; border: 1px solid #e2e8f0; padding-top: 12px; padding-bottom: 12px; }
        .search-box input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); outline: none; }
        .search-icon { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem;}

        .stat-icon-box { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }

        .action-btn { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: white; transition: 0.2s; }
        .action-btn:hover { background: #f1f5f9; transform: scale(1.05); }

        /* Responsive Fixes */
        @media (max-width: 768px) {
            .coupon-ticket::before, .coupon-ticket::after { top: 140px; }
            .fixed-alert-container { right: 10px; left: 10px; top: 10px; width: auto; max-width: none; }
            .filter-container-wrapper { flex-direction: column; align-items: stretch !important; }
        }
    </style>
@endsection
@section('title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            <h5 class="m-0 fw-bold fs-5">إدارة الكوبونات</h5>
        </div>
    </div>
@endsection
@section('content')

{{-- نظام التنبيهات --}}
<div class="fixed-alert-container">
    @if(session('success'))
        <div class="custom-toast success">
            <div class="toast-content">
                <div class="toast-icon"><i class="fa-solid fa-circle-check"></i></div>
                <div class="toast-body"><span class="toast-title">تم بنجاح</span><p class="toast-message">{{ session('success') }}</p></div>
                <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif
    @if($errors->any())
        @foreach($errors->all() as $error)
            <div class="custom-toast error">
                <div class="toast-content">
                    <div class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <div class="toast-body"><span class="toast-title">خطأ في العملية</span><p class="toast-message">{{ $error }}</p></div>
                    <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                </div>
                <div class="toast-progress"></div>
            </div>
        @endforeach
    @endif
</div>

<div class="container-fluid p-3 p-md-4" style="background: var(--bg-light); min-height: 100vh;">

    {{-- الإحصائيات --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 transition-hover h-100">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-box bg-indigo bg-opacity-10 text-primary me-3" style="background-color: #e0e7ff;"><i class="fa-solid fa-ticket"></i></div>
                    <div><h3 class="m-0 fw-bold text-dark">{{ $coupons->where('status', 'active')->count() }}</h3><small class="text-muted fw-bold">كوبونات نشطة</small></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 transition-hover h-100">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-box bg-success bg-opacity-10 text-success me-3"><i class="fa-solid fa-bolt"></i></div>
                    <div><h3 class="m-0 fw-bold text-dark">{{ $coupons->sum('used') }}</h3><small class="text-muted fw-bold">إجمالي مرات الاستفادة</small></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 transition-hover h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center">
                    <div class="stat-icon-box bg-warning bg-opacity-10 text-warning me-3"><i class="fa-solid fa-chart-pie"></i></div>
                    <div><h3 class="m-0 fw-bold text-dark">{{ round($coupons->avg('percent'), 1) }}%</h3><small class="text-muted fw-bold">متوسط الخصم</small></div>
                </div>
            </div>
        </div>
    </div>

    {{-- شريط البحث والفلترة --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 filter-container-wrapper">
                <div class="filter-scroll-wrapper">
                    <button class="filter-badge active" onclick="applyFilter('all', this)">الكل</button>
                    <button class="filter-badge" onclick="applyFilter('active', this)">النشطة</button>
                    <button class="filter-badge" onclick="applyFilter('expired', this)">المنتهية</button>
                    <button class="filter-badge" onclick="applyFilter('popular', this)">الأكثر استخداماً</button>
                </div>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="couponSearch" class="form-control bg-light" placeholder="ابحث بكود الخصم..." onkeyup="searchCoupons()">
                </div>
            </div>
        </div>
    </div>

    {{-- شبكة الكوبونات --}}
    <div class="row g-4" id="couponsGrid">
        @foreach ($coupons as $coupon)
        @php
            $isExpired = $coupon->expiry_date->isPast() || $coupon->used >= $coupon->limit;
            $usagePercent = $coupon->limit > 0 ? ($coupon->used / $coupon->limit) * 100 : 0;
        @endphp
        <div class="col-12 col-md-6 col-lg-4 col-xl-3 coupon-item"
             data-status="{{ $coupon->status }}"
             data-expired="{{ $isExpired ? 'true' : 'false' }}"
             data-used="{{ $coupon->used }}"
             data-code="{{ strtolower($coupon->code) }}">

            <div class="coupon-ticket {{ $coupon->status == 'inactive' ? 'opacity-50' : '' }}">
                <div class="coupon-top">
                    <div class="discount-circle">{{ $coupon->percent }}<small>%</small></div>

                    {{-- Professional Copy Box --}}
                    <div class="coupon-code-wrapper" onclick="copyToClipboard('{{ $coupon->code }}', this)">
                        <div class="coupon-code-text">{{ $coupon->code }}</div>
                        <div class="coupon-copy-btn" title="نسخ الكود">
                            <i class="fa-regular fa-copy"></i>
                        </div>
                    </div>
                </div>

                <div class="coupon-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small fw-bold mb-2">
                            <span class="text-muted">معدل الاستهلاك</span>
                            <span class="{{ $isExpired ? 'text-danger' : 'text-dark' }}">{{ $coupon->used }} / {{ $coupon->limit }}</span>
                        </div>
                        <div class="progress rounded-pill bg-light" style="height: 8px;">
                            <div class="progress-bar {{ $usagePercent >= 100 ? 'bg-danger' : ($usagePercent > 80 ? 'bg-warning' : 'bg-primary') }}"
                                 style="width: {{ $usagePercent }}%;"></div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted fw-bold"><i class="fa-regular fa-clock me-1"></i> ينتهي: {{ $coupon->expiry_date->format('Y-m-d') }}</div>
                        @if($isExpired) <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1">منتهي</span> @endif
                    </div>
                </div>

                <div class="coupon-footer">
                    <div class="d-flex gap-2">
                        <button class="action-btn border shadow-sm" data-bs-toggle="modal" data-bs-target="#editCoupon{{ $coupon->id }}"><i class="fa-solid fa-pen-to-square text-primary"></i></button>
                        <button class="action-btn border shadow-sm" data-bs-toggle="modal" data-bs-target="#deleteCoupon{{ $coupon->id }}"><i class="fa-solid fa-trash text-danger"></i></button>
                    </div>

                    <form action="{{ route('coupons.update', $coupon->id) }}" method="POST" class="m-0">
                        @csrf @method('PUT')
                        <input type="hidden" name="status_toggle" value="1">
                        <div class="form-check form-switch m-0" style="transform: scale(1.1);">
                            <input class="form-check-input shadow-none" type="checkbox" onchange="this.form.submit()" {{ $coupon->status == 'active' ? 'checked' : '' }} style="cursor: pointer">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- المودالات (تعديل وحذف) --}}
        <div class="modal fade" id="editCoupon{{ $coupon->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('coupons.update', $coupon->id) }}">
                        @csrf @method('PUT')
                        <div class="modal-header border-0 bg-light rounded-top-4 p-4">
                            <h5 class="modal-title fw-bold text-dark">تعديل بيانات الكوبون</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold small">رمز القسيمة</label>
                                <input type="text" class="form-control form-control-lg text-uppercase fw-bold bg-light" name="code" value="{{ $coupon->code }}" required>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">نسبة الخصم</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control bg-light" name="percent" value="{{ $coupon->percent }}" required>
                                        <span class="input-group-text bg-white">%</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">الحد الأقصى</label>
                                    <input type="number" class="form-control bg-light" name="limit" value="{{ $coupon->limit }}" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="form-label fw-bold small">تاريخ الصلاحية</label>
                                <input type="date" class="form-control bg-light" name="expiry_date" value="{{ $coupon->expiry_date->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background-color: var(--primary-color); border: none;">حفظ التغييرات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteCoupon{{ $coupon->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow rounded-4">
                    <form action="{{ route('coupons.destroy', $coupon->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <div class="modal-body p-4 text-center">
                            <div class="mb-3 text-danger"><i class="fa-solid fa-circle-exclamation fa-4x"></i></div>
                            <h5 class="fw-bold mb-2">هل أنت متأكد؟</h5>
                            <p class="text-muted small">سيتم حذف الكوبون <strong>{{ $coupon->code }}</strong> نهائياً.</p>
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold flex-grow-1">احذف</button>
                                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold flex-grow-1" data-bs-dismiss="modal">تراجع</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        {{-- إضافة كوبون جديد --}}
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="coupon-ticket border-0" data-bs-toggle="modal" data-bs-target="#addCouponModal"
                 style="border: 2px dashed #cbd5e1; background: transparent; cursor: pointer; min-height: 280px; justify-content: center; align-items: center;">
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 80px; height: 80px; transition: 0.3s;">
                    <i class="fa-solid fa-plus text-primary fs-1"></i>
                </div>
                <h5 class="fw-bold text-muted m-0">إنشاء كوبون جديد</h5>
            </div>
        </div>
    </div>
</div>

{{-- موديول الإضافة --}}
<div class="modal fade" id="addCouponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" action="{{ route('coupons.store') }}">
                @csrf
                <div class="modal-header border-0 bg-primary bg-opacity-10 rounded-top-4 p-4">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-plus-circle me-2"></i>إنشاء كوبون خصم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold small">كود الخصم <span class="text-danger">*</span></label>
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                            <input type="text" id="newCodeInput" class="form-control form-control-lg text-uppercase fw-bold border-0 bg-light" name="code" placeholder="EX: SAVE20" required>
                            <button class="btn btn-dark fw-bold px-4 border-0" type="button" onclick="generateCode()" title="توليد كود عشوائي"><i class="fa-solid fa-magic"></i></button>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">نسبة الخصم (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control bg-light border-0 shadow-sm" name="percent" placeholder="20" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">الحد الأقصى للمرات <span class="text-danger">*</span></label>
                            <input type="number" class="form-control bg-light border-0 shadow-sm" name="limit" placeholder="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">تاريخ الانتهاء <span class="text-danger">*</span></label>
                        <input type="date" class="form-control bg-light border-0 shadow-sm" name="expiry_date" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">حفظ ونشر الكوبون</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // --- نسخ الكود باحترافية ---
    function copyToClipboard(text, element) {
        navigator.clipboard.writeText(text);

        // تغيير أيقونة الزر برمجياً للتأكيد
        const iconBtn = element.querySelector('.coupon-copy-btn');
        const originalHTML = iconBtn.innerHTML;

        iconBtn.classList.add('copied');
        iconBtn.innerHTML = '<i class="fa-solid fa-check"></i>';

        // عرض توست
        const toast = document.createElement('div');
        toast.className = 'fixed-bottom mb-4 start-50 translate-middle-x badge bg-dark p-3 rounded-pill shadow-lg d-flex align-items-center gap-2';
        toast.style.zIndex = "10001";
        toast.style.fontSize = "0.9rem";
        toast.innerHTML = `<i class="fa-solid fa-check text-success fs-5"></i> تم نسخ الكود: <b class="text-warning">${text}</b>`;
        document.body.appendChild(toast);

        // إعادة الزر والتوست لحالتهم الأصلية
        setTimeout(() => {
            iconBtn.classList.remove('copied');
            iconBtn.innerHTML = originalHTML;
            toast.remove();
        }, 2000);
    }

    // توليد كود عشوائي
    function generateCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let res = '';
        for (let i = 0; i < 8; i++) res += chars.charAt(Math.floor(Math.random() * chars.length));
        document.getElementById('newCodeInput').value = res;
    }

    // البحث الفوري
    function searchCoupons() {
        const q = document.getElementById('couponSearch').value.toLowerCase();
        const items = document.querySelectorAll('.coupon-item');
        items.forEach(item => {
            const code = item.dataset.code;
            item.style.display = code.includes(q) ? 'block' : 'none';
        });
    }

    // الفلترة الذكية
    function applyFilter(type, btn) {
        document.querySelectorAll('.filter-badge').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const items = document.querySelectorAll('.coupon-item');
        items.forEach(item => {
            if(type === 'all') item.style.display = 'block';
            else if(type === 'active') item.style.display = item.dataset.status === 'active' && item.dataset.expired === 'false' ? 'block' : 'none';
            else if(type === 'expired') item.style.display = item.dataset.expired === 'true' ? 'block' : 'none';
            else if(type === 'popular') {
                item.style.display = parseInt(item.dataset.used) > 0 ? 'block' : 'none';
            }
        });
    }

    // إغلاق التنبيهات تلقائياً
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.querySelectorAll('.custom-toast').forEach(t => {
                t.style.animation = "slideOutRight 0.5s ease-in forwards";
                setTimeout(() => t.remove(), 500);
            });
        }, 5000);
    });
</script>
@endsection
