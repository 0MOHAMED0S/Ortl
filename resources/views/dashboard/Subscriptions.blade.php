@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        :root {
            --primary-color: #2d8a74;
            --primary-dark: #1b4d3e;
            --surface-color: #ffffff;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #f1f5f9;
        }

        /* --- تحسينات بصرية Premium --- */
        .main-card { border: none; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); overflow: hidden; background: #fff; }
        .order-id { font-family: 'Monaco', monospace; color: #6366f1; font-weight: 700; background: #f5f7ff; padding: 5px 12px; border-radius: 10px; font-size: 0.8rem; border: 1px solid #eef0ff; }

        /* كروت الأرباح والإحصائيات */
        .revenue-section { background: var(--bg-light); border-radius: 30px; padding: 30px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
        .fin-card { background: #fff; border-radius: 20px; padding: 25px; border: 1px solid #edf2f7; transition: 0.3s; height: 100%; position: relative; }
        .fin-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .fin-card.highlight { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%); color: #fff; border: none; box-shadow: 0 10px 25px rgba(45, 138, 116, 0.3); }

        .stat-badge-mini { position: absolute; top: 15px; left: 15px; font-size: 0.65rem; padding: 4px 10px; border-radius: 8px; background: rgba(0,0,0,0.05); font-weight: 800; letter-spacing: 0.5px; }
        .highlight .stat-badge-mini { background: rgba(255,255,255,0.2); color: white; }

        /* أزرار الفلترة السريعة (متجاوبة) */
        .filter-scroll-wrapper { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
        .filter-scroll-wrapper::-webkit-scrollbar { display: none; }

        .fast-filter-btn { border-radius: 12px; border: none; background: #fff; padding: 8px 20px; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); white-space: nowrap; }
        .fast-filter-btn:hover, .fast-filter-btn.active { background: var(--primary-color); color: #fff; box-shadow: 0 4px 12px rgba(45, 138, 116, 0.2); }

        .search-box { position: relative; flex-grow: 1; min-width: 250px; }
        .search-box input { padding-right: 40px; border-radius: 50px; border: 1px solid #e2e8f0; padding-top: 10px; padding-bottom: 10px; transition: 0.3s; width: 100%; }
        .search-box input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(45, 138, 116, 0.1); outline: none; }
        .search-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* --- تصميم المودال الاحترافي المتجاوب --- */
        .modal-profile-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            height: 120px;
            border-radius: 20px 20px 0 0;
            position: relative;
        }

        .avatar-overlap {
            margin-top: -60px;
            text-align: center;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .avatar-overlap img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            object-fit: cover;
            background: #fff;
        }

        .detail-card {
            background: var(--bg-light);
            border-radius: 16px;
            padding: 20px;
            height: 100%;
            border: 1px solid var(--border-color);
        }

        .detail-card.highlight-card {
            background: #f0fdfa;
            border-color: #ccfbf1;
        }

        .detail-card-title {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--text-muted);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
        }

        .info-row { margin-bottom: 12px; }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 2px; }
        .info-value { font-size: 0.95rem; color: var(--text-main); font-weight: 700; word-break: break-word; }

        /* الكوبون في المودال */
        .modal-coupon-box {
            background: #eef2ff; border: 1px dashed #c7d2fe; border-radius: 16px; padding: 15px;
            display: flex; justify-content: space-between; align-items: center; margin-top: 15px;
        }

        .student-img-table { width: 40px; height: 40px; border-radius: 12px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-right: 12px; }

        .price-tag { font-size: 0.85rem; font-weight: 800; padding: 6px 12px; border-radius: 10px; white-space: nowrap; }
        .price-before { text-decoration: line-through; color: #94a3b8; background: #f1f5f9; margin-left: 5px; }
        .price-after { color: #10b981; background: #ecfdf5; }

        .status-badge { padding: 6px 16px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fef9c3; color: #a16207; }
        .status-failed { background: #fee2e2; color: #b91c1c; }

        .new-order-badge { background: #6366f1; color: #fff; font-size: 0.65rem; padding: 3px 8px; border-radius: 6px; margin-right: 8px; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }

        /* شارة الحالة داخل المودال */
        .modal-status-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
        }

        /* --- ننسيق الطباعة الاحترافي --- */
        @media print {
            body * { visibility: hidden; }
            #dossierModal, #dossierModal * { visibility: visible; }
            #dossierModal { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; }
            .modal-footer, .btn-close, .filter-scroll-wrapper { display: none !important; }
            .modal-content { border: none !important; box-shadow: none !important; }
            .detail-card { border: 1px solid #ddd !important; background: #fff !important; }
            .modal-profile-header { background: #eee !important; -webkit-print-color-adjust: exact; }
        }

        /* Responsive Fixes */
        @media (max-width: 991px) {
            .revenue-section { padding: 20px; border-radius: 20px; }
        }
        @media (max-width: 768px) {
            .filter-container-wrapper { flex-direction: column; align-items: stretch !important; gap: 10px; }
            .filter-container-wrapper select, .filter-container-wrapper .search-box { width: 100% !important; }
            .fin-card { margin-bottom: 10px; }
        }
    </style>
@endsection

@section('title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div><h5 class="m-0 fw-bold fs-5 text-dark">مركز إدارة الاشتراكات</h5></div>
    </div>
@endsection

@section('content')
<div class="container-fluid p-3 p-md-4">

    {{-- الإحصائيات الذكية المتجاوبة --}}
    <div class="revenue-section shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h5 class="fw-bold m-0 text-dark">التحليل المالي والكمي</h5>
                <small class="text-muted">مراقبة التدفق المالي المباشر للمنصة</small>
            </div>
            <div class="filter-scroll-wrapper" id="dateFilterTabs">
                <button class="fast-filter-btn active" onclick="quickDateFilter('all', this)">الكل</button>
                <button class="fast-filter-btn" onclick="quickDateFilter('today', this)">اليوم</button>
                <button class="fast-filter-btn" onclick="quickDateFilter('month', this)">هذا الشهر</button>
                <button class="fast-filter-btn" onclick="quickDateFilter('year', this)">هذه السنة</button>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="fin-card highlight">
                    <span class="stat-badge-mini">PAYTABS</span>
                    <small class="text-white text-opacity-75 fw-bold d-block mb-1 mt-2">صافي الأرباح المحققة</small>
                    <h2 class="fw-bold m-0" id="display-net-revenue">{{ number_format($stats['net_revenue_egp'], 2) }} ج.م</h2>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="fin-card text-center">
                    <span class="stat-badge-mini text-success">SUCCESS</span>
                    <small class="text-muted d-block fw-bold mb-1 mt-2">العمليات الناجحة</small>
                    <h3 class="fw-bold m-0 text-dark" id="display-success-count">{{ $stats['success_orders'] }}</h3>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="fin-card text-center">
                    <span class="stat-badge-mini text-warning">WAITING</span>
                    <small class="text-muted d-block fw-bold mb-1 mt-2">عمليات قيد الانتظار</small>
                    <h3 class="fw-bold m-0 text-dark">{{ $stats['pending_orders'] }}</h3>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="fin-card text-center">
                    <span class="stat-badge-mini text-danger">DISCOUNTS</span>
                    <small class="text-muted d-block fw-bold mb-1 mt-2">إجمالي الخصومات</small>
                    <h3 class="fw-bold m-0 text-danger" id="display-discounts">-{{ number_format($stats['total_discounts_egp'], 2) }} ج.م</h3>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-6 col-lg-3">
                <label class="info-label text-dark">من تاريخ</label>
                <input type="date" id="filterFromDate" class="form-control border-0 bg-white rounded-3 shadow-sm" onchange="runFilters()">
            </div>
            <div class="col-6 col-lg-3">
                <label class="info-label text-dark">إلى تاريخ</label>
                <input type="date" id="filterToDate" class="form-control border-0 bg-white rounded-3 shadow-sm" onchange="runFilters()">
            </div>
            <div class="col-12 col-lg-6 d-flex align-items-end mt-3 mt-lg-0">
                <div class="p-3 bg-white rounded-3 border shadow-sm w-100 d-flex justify-content-between align-items-center">
                    <span class="text-muted fw-bold small"><i class="fa-solid fa-calculator text-primary me-2"></i>متوسط الفاتورة للعمليات المفلترة:</span>
                    <span class="fw-bold text-primary fs-5" id="display-avg">{{ $stats['success_orders'] > 0 ? number_format($stats['net_revenue_egp'] / $stats['success_orders'], 2) : 0 }} ج.م</span>
                </div>
            </div>
        </div>
    </div>

    {{-- أدوات الجدول --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 filter-container-wrapper">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold m-0 text-dark">سجل المعاملات</h6>
                <span class="badge bg-soft-primary text-primary border rounded-pill px-3 py-1 fw-bold" id="results-count">جاري الحساب...</span>
            </div>

            <div class="d-flex gap-2 flex-wrap flex-md-nowrap flex-grow-1 justify-content-end w-100">
                <select id="statusTableFilter" class="form-select form-select-sm border-light bg-light rounded-pill shadow-none px-3 py-2" onchange="runFilters()">
                    <option value="all">كل الحالات</option>
                    <option value="paid">ناجحة فقط</option>
                    <option value="pending">قيد الانتظار</option>
                    <option value="failed">فاشلة</option>
                </select>
                <select id="countryTableFilter" class="form-select form-select-sm border-light bg-light rounded-pill shadow-none px-3 py-2" onchange="runFilters()">
                    <option value="all">كل الدول</option>
                    @foreach($orders->unique('country.name') as $o)
                        <option value="{{ $o->country->name }}">{{ $o->country->name }}</option>
                    @endforeach
                </select>
                <select id="packageTableFilter" class="form-select form-select-sm border-light bg-light rounded-pill shadow-none px-3 py-2" onchange="runFilters()">
                    <option value="all">كل الباقات</option>
                    @foreach($orders->unique('package_name') as $o)
                        <option value="{{ $o->package_name }}">{{ $o->package_name }}</option>
                    @endforeach
                </select>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="orderSearch" class="form-control bg-light rounded-pill shadow-none border-light" placeholder="ابحث بالاسم، البريد، أو رقم المعاملة..." onkeyup="runFilters()">
                </div>
            </div>
        </div>
    </div>

    {{-- الجدول المتجاوب --}}
    <div class="main-card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" style="min-width: 900px;">
                <thead class="bg-light">
                    <tr class="small fw-bold text-muted text-uppercase">
                        <th class="p-3 border-0 text-start">تفاصيل العملية</th>
                        <th class="p-3 border-0 text-start">بيانات الطالب</th>
                        <th class="p-3 border-0">تحليل السعر (ج.م)</th>
                        <th class="p-3 border-0">التحصيل المحلي</th>
                        <th class="p-3 border-0">الحالة</th>
                        <th class="p-3 border-0 text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    @foreach($orders as $order)
                    <tr class="order-row"
                        data-status="{{ $order->status }}"
                        data-country="{{ $order->country->name }}"
                        data-package="{{ $order->package_name }}"
                        data-date="{{ $order->date_human }}"
                        data-search="{{ strtolower($order->user->name . ' ' . $order->user->email . ' ' . $order->transaction_id) }}"
                        data-net="{{ $order->price_after_egp }}"
                        data-discount="{{ $order->discount_amount_egp }}">
                        <td class="p-3 text-start">
                            <div class="d-flex align-items-center mb-1">
                                <span class="order-id">#{{ $order->transaction_id }}</span>
                                @if($order->is_new) <span class="new-order-badge">جديد</span> @endif
                            </div>
                            <div class="small text-muted"><i class="fa-regular fa-calendar me-1"></i>{{ $order->date_human }}</div>
                        </td>
                        <td class="p-3 text-start">
                            <div class="d-flex align-items-center">
                                <img src="{{ $order->user->image }}" class="student-img-table">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $order->user->name }}</div>
                                    <div class="small text-muted"><i class="fa-solid fa-location-dot text-danger opacity-75 me-1"></i>{{ $order->country->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3">
                            <span class="price-tag price-before">{{ $order->price_before_egp }} ج.م</span>
                            <i class="fa-solid fa-arrow-left-long mx-1 text-muted small"></i>
                            <span class="price-tag price-after">{{ $order->price_after_egp }} ج.م</span>
                        </td>
                        <td class="p-3">
                            <div class="fw-bold text-dark fs-6">{{ $order->amount_local }} <span class="text-muted small">{{ $order->currency }}</span></div>
                        </td>
                        <td class="p-3">
                            <span class="status-badge status-{{ $order->status }}">
                                {{ $order->status == 'paid' ? 'ناجحة' : ($order->status == 'pending' ? 'انتظار' : 'فاشلة') }}
                            </span>
                        </td>
                        <td class="p-3 text-end">
                            <button class="btn btn-light btn-sm rounded-pill shadow-sm px-4 fw-bold text-primary border transition-hover" onclick="viewComprehensiveDossier({{ json_encode($order) }})">
                                <i class="fa-solid fa-file-invoice me-1"></i> التفاصيل
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top bg-light bg-opacity-50">
            {{ $orders->links() }}
        </div>
    </div>
</div>

{{-- MODAL الاحترافي لمعلومات الفاتورة --}}
<div class="modal fade" id="dossierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">

            {{-- Header with Gradient --}}
            <div class="modal-profile-header">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4" data-bs-dismiss="modal"></button>

                {{-- شارة الحالة الديناميكية --}}
                <span id="m-modalStatusBadge" class="modal-status-badge"></span>
            </div>

            <div class="modal-body p-0 pb-4 bg-white">
                {{-- Overlapping Avatar & Name --}}
                <div class="avatar-overlap">
                    <img src="" id="m-avatar-img" alt="User Avatar">
                    <h4 class="fw-bold mt-2 mb-0 text-dark" id="m-name"></h4>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 mt-2">
                        معرف النظام: <span class="font-monospace text-primary fw-bold" id="m-userId"></span>
                    </span>
                </div>

                <div class="px-4">
                    <div class="row g-4">
                        {{-- معلومات العميل --}}
                        <div class="col-12 col-lg-5">
                            <div class="detail-card">
                                <h6 class="detail-card-title"><i class="fa-solid fa-user text-primary"></i> بيانات العميل</h6>
                                <div class="info-row">
                                    <span class="info-label">البريد الإلكتروني</span>
                                    <span class="info-value" id="m-email"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">رقم الهاتف</span>
                                    <span class="info-value" id="m-phone" dir="ltr"></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">الدولة المستحقة</span>
                                    <span class="info-value"><i class="fa-solid fa-location-dot text-danger me-1"></i> <span id="m-country"></span></span>
                                </div>
                            </div>
                        </div>

                        {{-- التحليل المالي --}}
                        <div class="col-12 col-lg-7">
                            <div class="detail-card highlight-card position-relative">
                                <h6 class="detail-card-title"><i class="fa-solid fa-sack-dollar text-success"></i> التحليل المالي للعملية</h6>

                                {{-- تنبيه هام يظهر فقط إذا كانت المعاملة غير مدفوعة --}}
                                <div id="m-unpaidWarning" class="alert alert-warning border-warning d-none mb-3 py-2 px-3 d-flex align-items-center">
                                    <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-warning"></i>
                                    <div class="small text-dark">
                                        <strong>تنبيه:</strong> هذه المعاملة <span id="m-unpaidStatusText" class="text-danger fw-bold"></span>، ولم يتم تحصيل المبلغ الفعلي.
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-6">
                                        <span class="info-label">السعر الأساسي (ج.م)</span>
                                        <span class="info-value fs-5 text-dark" id="m-pkgPrice"></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">المبلغ الصافي المطلوب (ج.م)</span>
                                        <span class="info-value fs-4 text-success" id="m-netEGP"></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">المبلغ المحصل (محلي)</span>
                                        <span class="info-value" id="m-localPaid"></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">سعر الصرف لـ EGP</span>
                                        <span class="info-value text-primary font-monospace" id="m-rate"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- تفاصيل الباقة --}}
                        <div class="col-12">
                            <div class="detail-card border-start border-4 border-primary">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h6 class="detail-card-title m-0"><i class="fa-solid fa-box-open text-primary"></i> تفاصيل الباقة المشتراة</h6>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 rounded-pill" id="m-pkgName"></span>
                                </div>
                                <div class="row g-3 text-center">
                                    <div class="col-4 border-end">
                                        <span class="info-label">الدقائق الأساسية</span>
                                        <span class="info-value"><i class="fa-regular fa-clock me-1 text-muted"></i><span id="m-pkgBaseMins"></span></span>
                                    </div>
                                    <div class="col-4 border-end">
                                        <span class="info-label">الدقائق الهدية</span>
                                        <span class="info-value text-success"><i class="fa-solid fa-gift me-1"></i><span id="m-pkgBonusMins"></span></span>
                                    </div>
                                    <div class="col-4">
                                        <span class="info-label">مدة الصلاحية</span>
                                        <span class="info-value"><span id="m-pkgValidity"></span> يوم</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top text-center">
                                    <span class="info-value text-muted fw-normal small" id="m-pkgDesc"></span>
                                </div>
                            </div>
                        </div>

                        {{-- قسم الكوبون (يتم حسابه تلقائياً من الفاتورة) --}}
                        <div class="col-12 d-none" id="m-couponBox">
                            <div class="modal-coupon-box">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white p-3 rounded-circle shadow-sm text-indigo"><i class="fa-solid fa-tag fs-5"></i></div>
                                    <div>
                                        <span class="info-label text-indigo mb-1">تم تطبيق خصم</span>
                                        <div class="info-value text-indigo fs-6 font-monospace" id="m-couponCode"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="info-label text-muted">قيمة الخصم الفعلي</span>
                                    <span class="fw-bold text-danger fs-4" id="m-savings"></span>
                                </div>
                            </div>
                        </div>

                        {{-- معلومات الدفع الفنية --}}
                        <div class="col-12">
                            <div class="detail-card bg-light">
                                <div class="row text-center g-2">
                                    <div class="col-sm-4 border-end border-white">
                                        <span class="info-label">المعرف الداخلي</span>
                                        <div class="order-id d-inline-block mt-1" id="m-internalId"></div>
                                    </div>
                                    <div class="col-sm-4 border-end border-white">
                                        <span class="info-label">بوابة الدفع (PayTabs)</span>
                                        <div class="info-value font-monospace small mt-1" id="m-paytabs"></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <span class="info-label">توقيت العملية</span>
                                        <div class="info-value small mt-1" id="m-date"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0 bg-white justify-content-center justify-content-md-end">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" data-bs-dismiss="modal">إغلاق النافذة</button>
                <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm" onclick="window.print()">
                    <i class="fa-solid fa-print me-2"></i> طباعة الفاتورة
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // --- فلترة الجدول والإحصائيات الحية ---
    function runFilters() {
        const q = document.getElementById('orderSearch').value.toLowerCase();
        const statusFilter = document.getElementById('statusTableFilter').value;
        const countryFilter = document.getElementById('countryTableFilter').value;
        const packageFilter = document.getElementById('packageTableFilter').value;
        const fromDate = document.getElementById('filterFromDate').value;
        const toDate = document.getElementById('filterToDate').value;
        const rows = document.querySelectorAll('.order-row');

        let netTotal = 0, discountTotal = 0, count = 0, visibleCount = 0;

        rows.forEach(row => {
            const rowDate = row.dataset.date.split(' ')[0].replace(/\//g, '-');
            const rowSearch = row.dataset.search;
            const status = row.dataset.status;
            const country = row.dataset.country;
            const pkg = row.dataset.package;

            const matchesSearch = rowSearch.includes(q);
            const matchesStatus = (statusFilter === 'all' || status === statusFilter);
            const matchesCountry = (countryFilter === 'all' || country === countryFilter);
            const matchesPackage = (packageFilter === 'all' || pkg === packageFilter);
            const matchesDate = (!fromDate || rowDate >= fromDate) && (!toDate || rowDate <= toDate);

            if (matchesSearch && matchesStatus && matchesCountry && matchesPackage && matchesDate) {
                row.style.display = '';
                visibleCount++;
                if(status === 'paid') {
                    netTotal += parseFloat(row.dataset.net);
                    discountTotal += parseFloat(row.dataset.discount);
                    count++;
                }
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('results-count').innerText = `${visibleCount} نتيجة`;
        document.getElementById('display-net-revenue').innerText = netTotal.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' ج.م';
        document.getElementById('display-discounts').innerText = '-' + discountTotal.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' ج.م';
        document.getElementById('display-success-count').innerText = count;
        document.getElementById('display-avg').innerText = (count > 0 ? (netTotal / count).toFixed(2) : '0.00') + ' ج.م';
    }

    function quickDateFilter(period, btn) {
        document.querySelectorAll('.fast-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const now = new Date();
        let from = '', to = now.toISOString().split('T')[0];
        if (period === 'today') from = to;
        else if (period === 'month') from = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        else if (period === 'year') from = new Date(now.getFullYear(), 0, 1).toISOString().split('T')[0];
        document.getElementById('filterFromDate').value = from;
        document.getElementById('filterToDate').value = to;
        runFilters();
    }

    // --- عرض تفاصيل المودال الاحترافي ---
    function viewComprehensiveDossier(order) {
        // إدارة التنبيهات والشارات للحالات غير المدفوعة
        const statusBadge = document.getElementById('m-modalStatusBadge');
        const unpaidWarning = document.getElementById('m-unpaidWarning');
        const unpaidStatusText = document.getElementById('m-unpaidStatusText');

        if (order.status === 'paid') {
            statusBadge.className = 'badge rounded-pill px-3 py-2 fs-6 modal-status-badge bg-success text-white shadow-sm';
            statusBadge.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> مدفوعة بنجاح';
            unpaidWarning.classList.add('d-none');
        } else {
            let statusName = order.status === 'pending' ? 'قيد الانتظار ولم تكتمل' : 'فاشلة أو ملغاة';
            let statusColor = order.status === 'pending' ? 'bg-warning text-dark' : 'bg-danger text-white';

            statusBadge.className = `badge rounded-pill px-3 py-2 fs-6 modal-status-badge shadow-sm ${statusColor}`;
            statusBadge.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i> ${order.status === 'pending' ? 'انتظار الدفع' : 'عملية فاشلة'}`;

            unpaidWarning.classList.remove('d-none');
            unpaidStatusText.innerText = statusName;
        }

        // البيانات الأساسية
        document.getElementById('m-name').innerText = order.user.name;
        document.getElementById('m-email').innerText = order.user.email;
        document.getElementById('m-userId').innerText = order.id;
        document.getElementById('m-phone').innerText = order.user.phone || 'غير متوفر';
        document.getElementById('m-country').innerText = order.country.name;
        document.getElementById('m-avatar-img').src = order.user.image;

        // البيانات المالية
        document.getElementById('m-pkgPrice').innerText = order.price_before_egp + ' ج.م';
        document.getElementById('m-localPaid').innerText = `${order.amount_local} ${order.currency}`;
        document.getElementById('m-netEGP').innerText = order.price_after_egp + ' ج.م';
        document.getElementById('m-rate').innerText = `1 ج.م = ${order.country.rate} ${order.country.currency_code}`;

        // بيانات الباقة
        document.getElementById('m-pkgName').innerText = order.package_name;
        document.getElementById('m-pkgBaseMins').innerText = order.package_base_minutes || 0;
        document.getElementById('m-pkgBonusMins').innerText = order.package_bonus_minutes || 0;
        document.getElementById('m-pkgValidity').innerText = order.package_validity_days || 0;
        document.getElementById('m-pkgDesc').innerText = order.package_description || 'باقة تعليمية مميزة للطلاب';

        // بيانات الدفع
        document.getElementById('m-internalId').innerText = '#' + order.transaction_id;
        document.getElementById('m-paytabs').innerText = order.paymob_id || 'غير متوفر';
        document.getElementById('m-date').innerText = order.date_human;

        // 🟢 منطق الكوبون المعدل: حساب نسبة الخصم تلقائياً من بيانات الطلب
        const couponBox = document.getElementById('m-couponBox');
        const discountAmount = parseFloat(order.discount_amount_egp);
        const originalPrice = parseFloat(order.price_before_egp);

        if (discountAmount > 0) {
            couponBox.classList.remove('d-none');

            // حساب نسبة الخصم المئوية تلقائياً من الطلب
            let calculatedPercentage = 0;
            if (originalPrice > 0) {
                calculatedPercentage = Math.round((discountAmount / originalPrice) * 100);
            }

            // إذا كان الكوبون موجوداً نعرض كوده، وإلا نعرض رسالة عامة
            let couponCodeDisplay = order.coupon ? `كود: ${order.coupon.code}` : 'تم تطبيق خصم مخصص';

            // إضافة النسبة المحسوبة إلى النص المعروض
            document.getElementById('m-couponCode').innerText = `${couponCodeDisplay} (${calculatedPercentage}%)`;

            document.getElementById('m-savings').innerText = `-${discountAmount} ج.م`;
        } else {
            couponBox.classList.add('d-none');
        }

        new bootstrap.Modal(document.getElementById('dossierModal')).show();
    }

    // تشغيل الفلاتر عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', runFilters);
</script>
@endsection
