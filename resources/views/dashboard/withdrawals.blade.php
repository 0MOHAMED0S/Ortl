@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #0d9488;
            --primary-light: #ccfbf1;
            --danger-color: #e11d48;
            --danger-light: #ffe4e6;
            --warning-color: #d97706;
            --warning-light: #fef3c7;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
        }

        /* --- التنبيهات العائمة --- */
        .fixed-alert-container { position: fixed; top: 25px; right: 25px; z-index: 10000; width: 100%; max-width: 350px; pointer-events: none; }
        .custom-toast { pointer-events: auto; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); border-radius: 16px; margin-bottom: 15px; overflow: hidden; position: relative; border-left: 5px solid transparent; animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); direction: rtl; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .custom-toast.success { border-left-color: var(--primary-color); }
        .custom-toast.error { border-left-color: var(--danger-color); }
        .toast-content { padding: 15px 20px; display: flex; align-items: flex-start; gap: 14px; }
        .toast-icon { font-size: 1.5rem; margin-top: 2px; }
        .success .toast-icon { color: var(--primary-color); }
        .error .toast-icon { color: var(--danger-color); }
        .toast-body { flex-grow: 1; text-align: right; }
        .toast-title { display: block; font-weight: 800; font-size: 0.95rem; color: var(--text-main); margin-bottom: 2px;}
        .toast-message { margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;}
        .toast-close { background: none; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; padding: 0; margin-right: -5px; transition: 0.2s;}
        .toast-close:hover { color: var(--text-main); }
        @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- الإحصائيات العلوية --- */
        .stat-card { background: #fff; border-radius: 24px; padding: 24px; border: 1px solid var(--border-color); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px; transition: transform 0.3s ease; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); }
        .stat-icon { width: 60px; height: 60px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; }

        /* --- الفلاتر والبحث (جديد) --- */
        .filters-wrapper { background: #fff; border-radius: 20px; padding: 15px 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between; }
        .filter-btn-group { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; }
        .btn-filter { border-radius: 50px; padding: 8px 20px; font-weight: 700; font-size: 0.85rem; border: 1px solid var(--border-color); background: #fff; color: var(--text-muted); transition: 0.3s; white-space: nowrap; cursor: pointer; }
        .btn-filter:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .btn-filter.active { background: var(--primary-color); color: #fff; border-color: var(--primary-color); box-shadow: 0 4px 10px rgba(13, 148, 136, 0.2); }
        .search-box-custom { position: relative; min-width: 280px; flex-grow: 1; max-width: 400px; }
        .search-box-custom input { border-radius: 50px; padding: 10px 20px 10px 45px; border: 1px solid var(--border-color); width: 100%; background: var(--bg-light); font-size: 0.9rem; transition: 0.3s; }
        .search-box-custom input:focus { background: #fff; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); outline: none; }
        .search-box-custom i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .results-count { font-weight: 800; color: var(--primary-color); background: var(--primary-light); padding: 5px 14px; border-radius: 50px; font-size: 0.85rem; border: 1px solid #a7f3d0;}

        /* --- الجدول الاحترافي --- */
        .table-card { border-radius: 20px; overflow: hidden; border: 1px solid var(--border-color); background: #fff; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
        .table-custom th { padding: 18px 20px; font-size: 0.85rem; font-weight: 800; color: var(--text-muted); background: #f8fafc; border-bottom: 2px solid var(--border-color); white-space: nowrap; text-transform: uppercase;}
        .table-custom td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
        .table-custom tbody tr { transition: background-color 0.2s ease; }
        .table-custom tbody tr:hover { background-color: #fcfcfc; }
        .teacher-avatar { width: 45px; height: 45px; object-fit: cover; border-radius: 12px; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }

        /* --- شارات الحالة --- */
        .badge-status { padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px;}
        .badge-pending { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .badge-approved { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-rejected { background: #fee2e2; color: #e11d48; border: 1px solid #fecaca; }

        /* --- تفاصيل الدفع --- */
        .payment-box { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 20px; margin-top: 15px; }
        .amount-text { font-size: 2rem; font-weight: 900; color: var(--primary-color); line-height: 1;}

        .modal-content-pro { border-radius: 24px; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .wallet-stat-box { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 16px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.01); height: 100%;}

        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
@endsection
@section('title')
<h5 class="m-0 fw-bold">طلبات السحب</h5>
@endsection

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success fw-bold rounded-4 border-0 shadow-sm"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fw-bold rounded-4 border-0 shadow-sm"><i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}</div>
    @endif

    <div class="mb-4">
        <h3 class="fw-800 text-dark m-0">الطلبات المالية (سحب الرصيد)</h3>
        <p class="text-muted small mt-1">إدارة ومراجعة طلبات سحب الأرباح الخاصة بالمعلمين.</p>
    </div>

    {{-- الإحصائيات --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div>
                    <h6 class="text-muted fw-bold mb-1">طلبات قيد المراجعة</h6>
                    <h3 class="fw-900 m-0 text-dark">{{ $stats['pending'] }} <small class="fs-6 fw-normal text-muted">طلب</small></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-sack-dollar"></i></div>
                <div>
                    <h6 class="text-muted fw-bold mb-1">إجمالي المدفوعات السابقة</h6>
                    <h3 class="fw-900 m-0 text-dark">${{ number_format($stats['approved'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fa-solid fa-ban"></i></div>
                <div>
                    <h6 class="text-muted fw-bold mb-1">الطلبات المرفوضة</h6>
                    <h3 class="fw-900 m-0 text-dark">{{ $stats['rejected'] }} <small class="fs-6 fw-normal text-muted">طلب</small></h3>
                </div>
            </div>
        </div>
    </div>

    {{-- الفلاتر وشريط البحث --}}
    <div class="filters-wrapper">
        <div class="filter-btn-group">
            <button type="button" class="btn-filter active" data-filter="all">عرض الكل</button>
            <button type="button" class="btn-filter" data-filter="pending">قيد المراجعة <span class="badge bg-warning text-dark ms-1 rounded-pill">{{ $stats['pending'] }}</span></button>
            <button type="button" class="btn-filter" data-filter="approved">مكتملة</button>
            <button type="button" class="btn-filter" data-filter="rejected">مرفوضة</button>
        </div>

        <div class="d-flex align-items-center gap-3 w-100 w-md-auto" style="flex: 1; max-width: 500px; justify-content: flex-end;">
            <span class="results-count" id="tableCount">{{ $requests->count() }} طلب</span>
            <div class="search-box-custom">
                <input type="text" id="searchInput" placeholder="ابحث برقم الطلب، اسم المعلم، أو الحساب...">
                <i class="fa-solid fa-search"></i>
            </div>
        </div>
    </div>

    {{-- الجدول --}}
    <div class="table-card shadow-sm">
        <div class="table-responsive custom-scrollbar">
            <table class="table table-custom align-middle mb-0 text-center" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th class="text-start">رقم الطلب والتاريخ</th>
                        <th class="text-start">المعلم</th>
                        <th class="text-center">المبلغ المطلوب</th>
                        <th class="text-center">طريقة الدفع (الحساب)</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="requestsTableBody">
                    @forelse($requests as $req)
                        @php
                            $tName = optional(optional($req->teacher)->user)->name ?? 'معلم محذوف';

                            // تجهيز النصوص للبحث (رقم الطلب + اسم المعلم + رقم الحساب)
                            $searchString = strtolower('req-'.$req->id.' '.$tName.' '.$req->account_number);
                        @endphp
                        {{-- إضافة خصائص الفلترة في السطر --}}
                        <tr class="request-row" data-status="{{ $req->status }}" data-search="{{ $searchString }}">
                            <td class="text-start">
                                <span class="fw-bold text-dark d-block">#REQ-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i>{{ $req->created_at->format('Y-m-d h:i A') }}</small>
                            </td>

                            <td class="text-start">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($tName) }}&background=0d9488&color=fff" class="teacher-avatar">
                                    <div>
                                        <h6 class="m-0 fw-bold text-dark mb-1">{{ $tName }}</h6>
                                        <button class="btn btn-link btn-sm p-0 text-decoration-none small fw-bold" data-bs-toggle="modal" data-bs-target="#teacherInfoModal{{ $req->id }}">
                                            <i class="fa-solid fa-wallet me-1"></i>المحفظة والسجل
                                        </button>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="fw-900 text-success fs-5">${{ number_format($req->amount, 2) }}</span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border font-monospace px-3 py-2 fs-6 shadow-sm">{{ $req->account_number }}</span>
                            </td>

                            <td class="text-center">
                                @if($req->status == 'pending')
                                    <span class="badge-status badge-pending"><i class="fa-solid fa-hourglass-half"></i> قيد المراجعة</span>
                                @elseif($req->status == 'approved')
                                    <span class="badge-status badge-approved"><i class="fa-solid fa-check"></i> مكتمل</span>
                                @else
                                    <span class="badge-status badge-rejected"><i class="fa-solid fa-xmark"></i> مرفوض</span>
                                @endif
                            </td>

                            <td class="text-end">
                                @if($req->status == 'pending')
                                    <button class="btn btn-dark btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#processModal{{ $req->id }}">معالجة الطلب</button>
                                @else
                                    <button class="btn btn-light border btn-sm rounded-pill px-4 py-2 fw-bold text-muted" disabled>تمت المعالجة</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyDbRow">
                            <td colspan="6" class="text-center py-5">
                                <i class="fa-solid fa-receipt fs-1 text-muted opacity-25 mb-3"></i>
                                <h5 class="text-muted fw-bold m-0">لا يوجد طلبات سحب حالياً</h5>
                            </td>
                        </tr>
                    @endforelse

                    {{-- صف فارغ يظهر عند عدم تطابق الفلتر أو البحث --}}
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="6" class="text-center py-5">
                            <i class="fa-solid fa-search fs-1 text-muted opacity-25 mb-3"></i>
                            <h6 class="text-muted fw-bold m-0">لا توجد نتائج مطابقة للبحث أو الفلتر</h6>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

{{-- ========================================== --}}
{{-- قسم المودالات --}}
{{-- ========================================== --}}
@section('modals')

    @foreach($requests as $req)
        @php
            $tName = optional(optional($req->teacher)->user)->name ?? 'معلم محذوف';
            $tMinutes = $req->teacher->minutes ?? 0;
            $tSalary = $req->teacher->salary ?? 0;
            $tBalance = ($tMinutes / 60) * $tSalary;
        @endphp

        {{-- 1. مودال عرض تفاصيل محفظة المعلم --}}
        <div class="modal fade" id="teacherInfoModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content modal-content-pro">
                    <div class="modal-header modal-header-pro d-flex justify-content-between align-items-center border-bottom p-4">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($tName) }}&background=0d9488&color=fff" class="teacher-avatar" style="width: 55px; height: 55px;">
                            <div>
                                <h5 class="fw-800 m-0 text-dark">{{ $tName }}</h5>
                                <span class="text-muted small">بيانات المحفظة والسجل المالي</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="wallet-stat-box">
                                    <span class="text-muted d-block small fw-bold mb-1">الرصيد المتاح حالياً</span>
                                    <h3 class="fw-900 text-primary m-0">${{ number_format($tBalance, 2) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="wallet-stat-box">
                                    <span class="text-muted d-block small fw-bold mb-1">الدقائق المتبقية</span>
                                    <h3 class="fw-900 text-dark m-0">{{ $tMinutes }} <small class="fs-6 fw-normal text-muted">دقيقة</small></h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="wallet-stat-box">
                                    <span class="text-muted d-block small fw-bold mb-1">سعر الساعة</span>
                                    <h3 class="fw-900 text-dark m-0">${{ number_format($tSalary, 2) }}</h3>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-clock-rotate-left text-muted me-2"></i>آخر 5 طلبات سحب للمعلم:</h6>
                        <div class="bg-white border rounded-4 overflow-hidden shadow-sm">
                            <table class="table table-borderless align-middle m-0 text-center small">
                                <thead class="bg-light border-bottom">
                                    <tr>
                                        <th class="py-3">رقم الطلب والتاريخ</th>
                                        <th class="py-3">المبلغ</th>
                                        <th class="py-3">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(optional($req->teacher)->withdrawalRequests ?? [] as $history)
                                        <tr class="border-bottom">
                                            <td class="py-3 text-start ps-4">
                                                <span class="fw-bold d-block text-dark">#REQ-{{ str_pad($history->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                <span class="text-muted" style="font-size: 0.75rem;">{{ $history->created_at->format('Y-m-d') }}</span>
                                            </td>
                                            <td class="fw-bold text-dark fs-6">${{ number_format($history->amount, 2) }}</td>
                                            <td>
                                                @if($history->status == 'pending') <span class="badge-status badge-pending px-3">معلق</span>
                                                @elseif($history->status == 'approved') <span class="badge-status badge-approved px-3">مكتمل</span>
                                                @else <span class="badge-status badge-rejected px-3">مرفوض</span> @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-muted py-4">لا يوجد سجل سابق.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 mt-3">
                        <button type="button" class="btn btn-dark rounded-pill px-5 fw-bold w-100 shadow-sm" data-bs-dismiss="modal">إغلاق النافذة</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. مودال معالجة الطلب --}}
        @if($req->status == 'pending')
        <div class="modal fade" id="processModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-pro">
                    <div class="modal-header border-bottom p-4">
                        <h5 class="fw-800 m-0 text-dark"><i class="fa-solid fa-money-bill-transfer text-primary me-2"></i>معالجة طلب السحب</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <p class="text-muted mb-1 fw-bold">يطلب المعلم <span class="text-dark">{{ $tName }}</span> سحب مبلغ:</p>
                            <div class="amount-text">${{ number_format($req->amount, 2) }}</div>
                        </div>

                        <div class="payment-box">
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-building-columns text-primary me-2"></i>تفاصيل حساب التحويل</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <span class="text-muted small d-block mb-2">رقم الحساب / المحفظة:</span>
                                    <div class="bg-white border rounded-3 p-3 text-center shadow-sm">
                                        <span class="fw-bold fs-4 font-monospace text-dark user-select-all">{{ $req->account_number }}</span>
                                    </div>
                                </div>
                                @if($req->notes)
                                <div class="col-12">
                                    <span class="text-muted small d-block mb-1">ملاحظات المعلم:</span>
                                    <div class="bg-white border rounded-3 p-3 text-dark small shadow-sm">{{ $req->notes }}</div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4 border-0 small rounded-3 d-flex align-items-start gap-2">
                            <i class="fa-solid fa-circle-info fs-5 mt-1"></i>
                            <span><strong>ملاحظة هامة:</strong> عند اختيار "رفض الطلب"، سيقوم النظام تلقائياً بإرجاع قيمة المبلغ (كدقائق) إلى محفظة المعلم.</span>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 d-flex flex-nowrap gap-2">
                        <form action="{{ route('admin.withdrawals.update_status', $req->id) }}" method="POST" class="w-50 m-0">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn btn-danger bg-opacity-10 text-white border-danger w-100 rounded-pill fw-bold py-3 shadow-none" onclick="return confirm('هل أنت متأكد من الرفض وإرجاع الرصيد للمعلم؟')">رفض وإرجاع الرصيد</button>
                        </form>
                        <form action="{{ route('admin.withdrawals.update_status', $req->id) }}" method="POST" class="w-50 m-0">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-3 shadow-sm" onclick="return confirm('هل قمت بتحويل المبلغ للمعلم وتود تأكيد العملية؟')">تم الدفع (تأكيد)</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @endforeach

@endsection

@section('scripts')
{{-- إضافة مكتبة jQuery (مطلوبة لعمل الفلتر بسلاسة) --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // 1. إخفاء التنبيهات تلقائياً
        setTimeout(function() {
            $('.custom-toast').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);

        // 2. نظام الفلترة والبحث المباشر
        let currentFilter = 'all';

        function filterTable() {
            let searchTerm = String($('#searchInput').val() || '').toLowerCase();
            let visibleCount = 0;

            $('.request-row').each(function() {
                // جلب القيم وتوحيدها بحروف صغيرة لتجنب أخطاء حساسية الأحرف
                let rowStatus = String($(this).attr('data-status') || '').toLowerCase();
                let rowText = String($(this).attr('data-search') || '').toLowerCase();

                let matchesFilter = (currentFilter === 'all' || rowStatus === currentFilter);
                let matchesSearch = rowText.includes(searchTerm);

                if (matchesFilter && matchesSearch) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            // تحديث عداد النتائج
            $('#tableCount').text(visibleCount + ' طلب');

            // إظهار رسالة "لا توجد نتائج" إذا كان الجدول فارغاً من البحث
            if (visibleCount === 0 && $('.request-row').length > 0) {
                $('#noResultsRow').show();
            } else {
                $('#noResultsRow').hide();
            }
        }

        // عند الضغط على أزرار الفلتر
        $('.btn-filter').on('click', function(e) {
            e.preventDefault();
            $('.btn-filter').removeClass('active');
            $(this).addClass('active');

            currentFilter = String($(this).attr('data-filter') || 'all').toLowerCase();
            filterTable();
        });

        // عند الكتابة في مربع البحث
        $('#searchInput').on('keyup', function() {
            filterTable();
        });
    });
</script>
@endsection
