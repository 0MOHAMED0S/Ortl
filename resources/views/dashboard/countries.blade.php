@extends('dashboard.layouts.master')

@section('styles')
    <style>
        :root {
            --primary-color: #0d9488;
            --surface-color: #ffffff;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        /* --- نظام التنبيهات (Responsive Toasts) --- */
        .fixed-alert-container { position: fixed; top: 25px; right: 25px; z-index: 10000; width: 100%; max-width: 350px; pointer-events: none; }
        .custom-toast { pointer-events: auto; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); border-radius: 14px; margin-bottom: 15px; overflow: hidden; position: relative; border-left: 5px solid transparent; animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); direction: rtl; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
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

        @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- الإحصائيات --- */
        .stat-card { background: #fff; border-radius: 20px; padding: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 55px; height: 55px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }

        /* --- البحث --- */
        .search-box { position: relative; max-width: 400px; width: 100%; }
        .search-input { border-radius: 50px; border: 1px solid var(--border-color); padding: 12px 20px 12px 45px; width: 100%; transition: all 0.3s; font-size: 0.95rem; background: #fff; }
        .search-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); outline: none; }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; border: none; background: none; }

        /* --- الفلاتر المتجاوبة --- */
        .filter-scroll-wrapper { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .filter-scroll-wrapper::-webkit-scrollbar { display: none; }
        .filter-btn { border-radius: 50px; padding: 8px 24px; font-weight: 600; font-size: 0.9rem; white-space: nowrap; box-shadow: none !important; border: 1px solid #e2e8f0; background: white; color: var(--text-muted); transition: 0.3s; }
        .filter-btn.active { background: #0d9488; color: white; border-color: #0d9488; }
        .filter-btn:hover:not(.active) { border-color: #0d9488; color: #0d9488; }

        /* --- الجدول الاحترافي --- */
        .table-card { border-radius: 24px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.02); background: #fff; }
        .table-custom th { padding: 18px 20px; font-size: 0.85rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; background: #f8fafc; border-bottom: 2px solid var(--border-color); letter-spacing: 0.5px; }
        .table-custom td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .table-custom tbody tr:hover { background-color: #fcfcfc; }

        .country-flag { width: 40px; height: 30px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #f1f5f9; }

        .code-badge { font-family: 'Monaco', monospace; background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
        .phone-badge { color: #0d9488; font-weight: 800; direction: ltr; display: inline-block; background: #f0fdfa; padding: 4px 10px; border-radius: 50px; font-size: 0.85rem; border: 1px solid #ccfbf1; }

        /* --- Toggle Switch --- */
        .form-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: #10b981; border-color: #10b981; }

        /* Pagination Styling */
        .pagination { margin: 0; }
        .page-item.active .page-link { background-color: var(--primary-color); border-color: var(--primary-color); }
        .page-link { color: var(--text-muted); border-radius: 8px; margin: 0 2px; border: 1px solid var(--border-color); }
        .page-link:hover { color: var(--primary-color); background-color: var(--bg-light); }
    </style>
@endsection

@section('title')
<h5 class="m-0 fw-bold text-dark">إدارة الدول والمناطق</h5>
@endsection

@section('content')

<div class="container-fluid p-3 p-md-4">

    {{-- التنبيهات --}}
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
            </div>
        @endif
        @if(session('error'))
            <div class="custom-toast error shadow-lg">
                <div class="toast-content">
                    <div class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <div class="toast-body">
                        <span class="toast-title">خطأ</span>
                        <p class="toast-message">{{ session('error') }}</p>
                    </div>
                    <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                </div>
            </div>
        @endif
    </div>

    {{-- الإحصائيات --}}
    <div class="row g-4 mb-5">
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-globe"></i></div>
                <div>
                    <h6 class="text-muted fw-bold mb-1">إجمالي الدول المتاحة</h6>
                    <h3 class="fw-bold m-0 text-dark">{{ $totalCountries }} دولة</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-check-circle"></i></div>
                <div>
                    <h6 class="text-muted fw-bold mb-1">الدول المفعلة (بالتطبيق)</h6>
                    <h3 class="fw-bold m-0 text-dark">{{ $activeCountries }} دولة</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- البحث والفلاتر (تم دمجهم في Form واحد للعمل معاً) --}}
    <form action="{{ route('countries.index') }}" method="GET" id="filterForm" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

        {{-- أزرار الفلترة --}}
        <div class="filter-scroll-wrapper">
            <input type="hidden" name="filter" id="filterInput" value="{{ request('filter', 'all') }}">
            <button type="button" class="filter-btn {{ request('filter', 'all') === 'all' ? 'active' : '' }}" data-value="all">الكل</button>
            <button type="button" class="filter-btn {{ request('filter') === 'active' ? 'active' : '' }}" data-value="active">النشطة فقط</button>
            <button type="button" class="filter-btn {{ request('filter') === 'inactive' ? 'active' : '' }}" data-value="inactive">المتوقفة</button>
        </div>

        {{-- مربع البحث --}}
        <div class="search-box">
            <input type="text" name="search" class="search-input shadow-sm" placeholder="ابحث باسم الدولة أو الكود (مثل: EG, +20)..." value="{{ request('search') }}">
            <button type="submit" class="search-icon"><i class="fa-solid fa-search"></i></button>
        </div>
    </form>

    {{-- الجدول --}}
    <div class="table-card shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0 text-center" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th class="text-start">الدولة / الكود</th>
                        <th class="text-start">العملة المحلية</th>
                        <th class="text-start">مفتاح الاتصال</th>
                        <th class="text-start">سعر الصرف لـ USD</th>
                        <th class="text-end" style="width: 200px;">حالة التفعيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($countries as $country)
                    <tr class="{{ $country->status ? 'bg-light bg-opacity-50' : '' }}"> {{-- تمييز خفيف للسطر المفعل --}}
                        {{-- الدولة والعلم --}}
                        <td class="text-start">
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://flagcdn.com/w80/{{ strtolower($country->code) }}.png" class="country-flag" alt="{{ $country->name }}">
                                <div>
                                    <h6 class="m-0 fw-bold text-dark">{{ $country->name }}</h6>
                                    <span class="code-badge mt-1">{{ $country->code }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- العملة --}}
                        <td class="text-start">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark">{{ $country->currency_code }}</span>
                                <small class="text-muted">{{ $country->currency_name }} ({{ $country->currency_symbol }})</small>
                            </div>
                        </td>

                        {{-- مفتاح الهاتف --}}
                        <td class="text-start">
                            @if($country->phone_code)
                                <span class="phone-badge">{{ $country->phone_code }}</span>
                            @else
                                <span class="text-muted small">غير متوفر</span>
                            @endif
                        </td>

                        {{-- سعر الصرف --}}
                        <td class="text-start">
                            @if($country->rate_to_usd)
                                <span class="fw-bold text-dark">{{ number_format($country->rate_to_usd, 2) }}</span>
                                <small class="text-muted">{{ $country->currency_code }}</small>
                            @else
                                <span class="text-muted small">غير متوفر</span>
                            @endif
                        </td>

                        {{-- زر التفعيل --}}
                        <td class="text-end">
                            <form action="{{ route('countries.toggle_status', $country->id) }}" method="POST" class="m-0 d-flex justify-content-end align-items-center">
                                @csrf @method('PUT')
                                <div class="form-check form-switch m-0 p-0 d-flex align-items-center" title="تفعيل / إيقاف الدولة في التطبيق">
                                    <span class="me-3 fw-bold {{ $country->status ? 'text-success' : 'text-muted' }}" style="font-size: 0.85rem;">
                                        {{ $country->status ? 'مفعلة' : 'متوقفة' }}
                                    </span>
                                    <input class="form-check-input m-0 shadow-none" type="checkbox" onchange="this.form.submit()" {{ $country->status ? 'checked' : '' }}>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-globe fs-1 mb-3 opacity-25"></i>
                            <h6 class="fw-bold">لا توجد دول مطابقة للبحث أو الفلتر الحالي</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- روابط التصفح (Pagination) --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $countries->links('pagination::bootstrap-5') }}
    </div>

</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // إخفاء التنبيهات تلقائياً
        setTimeout(() => {
            document.querySelectorAll('.custom-toast').forEach(toast => {
                toast.style.animation = "slideOutRight 0.5s ease-in forwards";
                setTimeout(() => toast.remove(), 500);
            });
        }, 5000);

        // تفعيل الفلاتر برمجياً لتعمل مع البحث في نفس الـ Form
        const filterBtns = document.querySelectorAll('.filter-btn');
        const filterInput = document.getElementById('filterInput');
        const filterForm = document.getElementById('filterForm');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // إزالة الأكتيف من الكل وإضافته للزر المضغوط
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // تحديث قيمة الـ Input المخفي وإرسال الفورم (ليطبق الفلتر مع البحث إن وُجد)
                filterInput.value = this.getAttribute('data-value');
                filterForm.submit();
            });
        });
    });
</script>
@endsection
