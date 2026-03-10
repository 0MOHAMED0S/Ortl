@extends('dashboard.layouts.master')

@section('styles')
    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #0d9488;
            --primary-light: #ccfbf1;
            --surface-color: #ffffff;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        /* --- Toasts --- */
        .fixed-alert-container {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 10000;
            width: 100%;
            max-width: 350px;
            pointer-events: none;
        }

        .custom-toast {
            pointer-events: auto;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            margin-bottom: 15px;
            overflow: hidden;
            position: relative;
            border-left: 5px solid transparent;
            animation: slideInRight 0.5s ease;
            direction: rtl;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .custom-toast.success {
            border-left-color: #10b981;
        }

        .custom-toast.error {
            border-left-color: #ef4444;
        }

        .toast-content {
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .toast-title {
            font-weight: 800;
            font-size: 0.95rem;
            color: #1e293b;
            display: block;
        }

        .toast-message {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* --- Filters & Search Bar --- */
        .filters-wrapper {
            background: #fff;
            border-radius: 16px;
            padding: 15px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            justify-content: space-between;
        }

        .filter-btn-group {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .btn-filter {
            border-radius: 50px;
            padding: 6px 18px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: var(--text-muted);
            transition: 0.3s;
            white-space: nowrap;
            cursor: pointer;
        }

        .btn-filter.active {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.2);
        }

        .search-box-custom {
            position: relative;
            min-width: 250px;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-box-custom input {
            border-radius: 50px;
            padding: 10px 20px 10px 40px;
            border: 1px solid var(--border-color);
            width: 100%;
            background: #f8fafc;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .search-box-custom input:focus {
            background: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1);
            outline: none;
        }

        .search-box-custom i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .results-count {
            font-weight: 800;
            color: var(--primary-color);
            background: var(--primary-light);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        /* --- Professional Table Styling --- */
        .table-card {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .table-custom {
            margin-bottom: 0;
        }

        .table-custom th {
            padding: 18px 20px;
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            background: #f8fafc;
            border-bottom: 2px solid var(--border-color);
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table-custom td {
            padding: 18px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }

        .table-custom tbody tr {
            transition: background-color 0.2s ease;
        }

        .table-custom tbody tr:hover {
            background-color: #fcfcfc;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 14px;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .parties-connector {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
        }

        .parties-connector i {
            font-size: 1rem;
            color: var(--primary-color);
            opacity: 0.7;
            margin-bottom: 4px;
        }

        .parties-connector .line {
            width: 40px;
            height: 2px;
            background: repeating-linear-gradient(to right, #cbd5e1 0, #cbd5e1 4px, transparent 4px, transparent 8px);
        }

        /* Status Badges */
        .badge-status {
            font-size: 0.75rem;
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-scheduled {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px solid #bfdbfe;
        }

        .badge-completed {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .badge-cancelled {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
        }

        .minutes-badge {
            font-family: 'Monaco', monospace;
            background: #fffbeb;
            color: #b45309;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid #fde68a;
        }

        /* Action Buttons */
        .btn-action-group {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: var(--text-muted);
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            text-decoration: none;
        }

        .action-btn:hover {
            background: var(--bg-light);
            color: var(--text-main);
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .action-btn.delete:hover {
            background: #fef2f2;
            color: #ef4444;
            border-color: #fee2e2;
        }

        .action-btn.record-play {
            background: #eff6ff;
            color: #3b82f6;
            border-color: #bfdbfe;
        }

        .action-btn.record-play:hover {
            background: #3b82f6;
            color: #ffffff;
            border-color: #2563eb;
        }

        .modal-content-pro {
            border-radius: 24px;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .filters-wrapper {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .filter-btn-group {
                overflow-x: auto;
                flex-wrap: nowrap;
                white-space: nowrap;
                padding-bottom: 8px;
            }

            .filters-wrapper>div.d-flex {
                flex-direction: column-reverse;
                align-items: stretch !important;
                max-width: 100% !important;
            }

            .search-box-custom {
                max-width: 100%;
                width: 100%;
            }

            .results-count {
                align-self: flex-start;
            }
        }
    </style>
@endsection

@section('title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            <h5 class="m-0 fw-bold fs-5">سجل المواعيد المحجوزة</h5>
        </div>
    </div>
@endsection

@section('content')

    <div class="fixed-alert-container">
        @if (session('success'))
            <div class="custom-toast success shadow-lg">
                <div class="toast-content">
                    <i class="fa-solid fa-circle-check text-success fs-3"></i>
                    <div>
                        <span class="toast-title">تم بنجاح</span>
                        <p class="toast-message">{{ session('success') }}</p>
                    </div>
                    <button type="button" class="btn-close ms-auto"
                        onclick="this.closest('.custom-toast').remove()"></button>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="custom-toast error shadow-lg">
                <div class="toast-content">
                    <i class="fa-solid fa-circle-exclamation text-danger fs-3"></i>
                    <div>
                        <span class="toast-title">يوجد خطأ بالبيانات</span>
                        @foreach ($errors->all() as $error)
                            <p class="toast-message">- {{ $error }}</p>
                        @endforeach
                    </div>
                    <button type="button" class="btn-close ms-auto"
                        onclick="this.closest('.custom-toast').remove()"></button>
                </div>
            </div>
        @endif
    </div>

    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item small"><a href="#">الرئيسية</a></li>
                        <li class="breadcrumb-item small active text-primary fw-bold">الجلسات والمواعيد</li>
                    </ol>
                </nav>
                <h3 class="fw-800 text-dark m-0">سجل المواعيد المحجوزة <i
                        class="fa-regular fa-calendar-check ms-2 text-muted fs-5"></i></h3>
                <p class="text-muted small mt-1 mb-0">إدارة حجوزات الطلاب للمواعيد المسبقة مع المعلمين ومتابعة التسجيلات.
                </p>
            </div>
        </div>

        {{-- الفلاتر والبحث --}}
        <div class="filters-wrapper">
            <div class="filter-btn-group">
                <button type="button" class="btn-filter active" data-filter="all">الكل</button>
                <button type="button" class="btn-filter" data-filter="scheduled">بانتظار الموعد</button>
                <button type="button" class="btn-filter" data-filter="completed">مكتملة</button>
                <button type="button" class="btn-filter" data-filter="cancelled">ملغاة</button>
            </div>

            <div class="d-flex align-items-center gap-3 w-100 w-md-auto"
                style="flex: 1; max-width: 500px; justify-content: flex-end;">
                <span class="results-count" id="tableCount">{{ $bookings->count() }} حجز</span>
                <div class="search-box-custom">
                    <input type="text" id="searchInput" placeholder="ابحث باسم الطالب، المعلم، أو المعرف...">
                    <i class="fa-solid fa-search"></i>
                </div>
            </div>
        </div>

        {{-- الجدول الاحترافي --}}
        <div class="table-card shadow-sm mb-5">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-custom align-middle mb-0 text-center" style="min-width: 1050px;">
                    <thead>
                        <tr>
                            <th class="text-center">رقم الحجز</th>
                            <th class="text-center">أطراف الحجز (طالب ⬅️ معلم)</th>
                            <th class="text-center">تاريخ ووقت الموعد</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsTableBody">
                        @forelse($bookings as $booking)
                            @php
                                $studentName = optional($booking->user)->name ?? 'طالب محذوف';
                                $teacherName =
                                    optional(optional(optional($booking->slot)->teacher)->user)->name ?? 'معلم محذوف';
                                $searchString = strtolower($studentName . ' ' . $teacherName . ' ' . $booking->id);

                                $date = optional($booking->slot)->date;
                                $startTime = optional($booking->slot)->start_time;
                                $endTime = optional($booking->slot)->end_time;
                            @endphp

                            <tr class="session-row" data-status="{{ $booking->status }}" data-search="{{ $searchString }}">

                                {{-- معرف الحجز --}}
                                <td class="text-center">
                                    <div class="fw-bold text-dark fs-5">#{{ $booking->id }}</div>
                                    <div class="mt-2">
                                        <span class="minutes-badge" title="الدقائق التي تم خصمها">
                                            <i class="fa-solid fa-coins me-1"></i> {{ $booking->deducted_minutes }} دقيقة
                                        </span>
                                    </div>
                                </td>

                                {{-- أطراف الحجز --}}
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-3">
                                        <div class="text-center" style="width: 90px;">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&background=e0e7ff&color=4338ca&bold=true"
                                                class="user-avatar mx-auto mb-2">
                                            <h6 class="m-0 fw-bold text-dark text-truncate" style="font-size: 0.85rem;"
                                                title="{{ $studentName }}">{{ $studentName }}</h6>
                                        </div>

                                        <div class="parties-connector">
                                            <i class="fa-regular fa-calendar-check"></i>
                                            <div class="line"></div>
                                        </div>

                                        <div class="text-center" style="width: 90px;">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($teacherName) }}&background=0d9488&color=fff&bold=true"
                                                class="user-avatar mx-auto mb-2">
                                            <h6 class="m-0 fw-bold text-dark text-truncate" style="font-size: 0.85rem;"
                                                title="{{ $teacherName }}">{{ $teacherName }}</h6>
                                        </div>
                                    </div>
                                </td>

                                {{-- التوقيت --}}
                                <td class="text-center">
                                    @if ($date)
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="badge bg-light border text-dark mb-2 px-3 py-1 shadow-sm">
                                                <i class="fa-regular fa-calendar text-primary me-1"></i>
                                                {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                                            </span>

                                            <div class="fw-bold text-muted small">
                                                {{ \Carbon\Carbon::parse($startTime)->format('h:i A') }}
                                                @if ($endTime)
                                                    <i class="fa-solid fa-arrow-left-long mx-2"
                                                        style="font-size: 0.7rem;"></i>
                                                    {{ \Carbon\Carbon::parse($endTime)->format('h:i A') }}
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small"><i
                                                class="fa-solid fa-triangle-exclamation me-1"></i> الموعد محذوف</span>
                                    @endif
                                </td>

                                {{-- الحالة --}}
                                <td class="text-center">
                                    @if ($booking->status == 'scheduled')
                                        <span class="badge badge-status badge-scheduled"><i
                                                class="fa-regular fa-clock me-1"></i> بانتظار الموعد</span>
                                    @elseif($booking->status == 'completed')
                                        <span class="badge badge-status badge-completed"><i
                                                class="fa-solid fa-check-double me-1"></i> مكتمل</span>
                                    @elseif($booking->status == 'cancelled')
                                        <span class="badge badge-status badge-cancelled"><i
                                                class="fa-solid fa-ban me-1"></i> ملغى</span>
                                    @endif
                                </td>

                                {{-- الإجراءات --}}
                                <td class="text-center">
                                    <div class="btn-action-group">
                                        {{-- 🎥 زر التسجيل: يظهر فقط إذا كان يوجد رابط تسجيل في قاعدة البيانات --}}
                                        @if (!empty($booking->recording_url))
                                            <a href="{{ $booking->recording_url }}" target="_blank"
                                                class="action-btn record-play" title="مشاهدة التسجيل السحابي">
                                                <i class="fa-solid fa-play"></i>
                                            </a>
                                        @endif

                                        <button class="action-btn delete" data-bs-toggle="modal"
                                            data-bs-target="#deleteBookingModal_{{ $booking->id }}" title="حذف الحجز">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal: Delete Booking --}}
                            {{-- <div class="modal fade" id="deleteBookingModal_{{ $booking->id }}" tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content modal-content-pro text-center">
                                        <div class="modal-body p-4">
                                            <i class="fa-regular fa-calendar-xmark text-danger mb-3 opacity-75"
                                                style="font-size: 4rem;"></i>
                                            <h5 class="fw-bold mb-2">تأكيد الحذف</h5>
                                            <p class="text-muted small mb-4">هل أنت متأكد من حذف الحجز رقم
                                                <strong>#{{ $booking->id }}</strong> نهائياً؟</p>

                                            <form action="{{ route('admin.slot_bookings.destroy', $booking->id) }}"
                                                method="POST" class="m-0">
                                                @csrf @method('DELETE')
                                                <div class="d-flex flex-column gap-2">
                                                    <button type="submit"
                                                        class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm">نعم،
                                                        احذف الحجز</button>
                                                    <button type="button"
                                                        class="btn btn-light border rounded-pill fw-bold py-2 shadow-sm"
                                                        data-bs-dismiss="modal">تراجع</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        @empty
                            <tr id="emptyStateRow">
                                <td colspan="5" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="90"
                                        class="mb-3 opacity-25" alt="No Bookings">
                                    <h5 class="text-muted fw-bold m-0">لا توجد مواعيد محجوزة حالياً</h5>
                                </td>
                            </tr>
                        @endforelse

                        {{-- صف فارغ لنتائج البحث --}}
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="5" class="text-center py-5">
                                <i class="fa-solid fa-search fs-1 text-muted opacity-25 mb-3"></i>
                                <h6 class="text-muted fw-bold m-0">لا توجد نتائج مطابقة للبحث أو الفلتر</h6>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.custom-toast').fadeOut('slow');
            }, 5000);
            let currentFilter = 'all';

            function filterTable() {
                let searchTerm = String($('#searchInput').val() || '').toLowerCase();
                let visibleCount = 0;
                $('.session-row').each(function() {
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
                $('#tableCount').text(visibleCount + ' حجز');
                if (visibleCount === 0 && $('.session-row').length > 0) {
                    $('#noResultsRow').show();
                } else {
                    $('#noResultsRow').hide();
                }
            }
            $('.btn-filter').on('click', function(e) {
                e.preventDefault();
                $('.btn-filter').removeClass('active');
                $(this).addClass('active');
                currentFilter = String($(this).attr('data-filter') || 'all').toLowerCase();
                filterTable();
            });
            $('#searchInput').on('keyup', function() {
                filterTable();
            });
        });
    </script>
@endsection
