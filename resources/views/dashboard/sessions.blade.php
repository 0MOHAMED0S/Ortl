@extends('dashboard.layouts.master')

@section('styles')
    {{-- Select2 & SweetAlert2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        .fixed-alert-container { position: fixed; top: 25px; right: 25px; z-index: 10000; width: 100%; max-width: 350px; pointer-events: none; }
        .custom-toast { pointer-events: auto; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); border-radius: 14px; margin-bottom: 15px; overflow: hidden; position: relative; border-left: 5px solid transparent; animation: slideInRight 0.5s ease; direction: rtl; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .custom-toast.success { border-left-color: #10b981; }
        .custom-toast.error { border-left-color: #ef4444; }
        .toast-content { padding: 16px 20px; display: flex; align-items: flex-start; gap: 14px; }
        .toast-title { font-weight: 800; font-size: 0.95rem; color: #1e293b; display: block;}
        .toast-message { margin: 0; font-size: 0.85rem; color: #64748b; }
        @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- Filters & Search Bar --- */
        .filters-wrapper { background: #fff; border-radius: 16px; padding: 15px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between; }
        .filter-btn-group { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; }
        .btn-filter { border-radius: 50px; padding: 6px 18px; font-weight: 700; font-size: 0.85rem; border: 1px solid #e2e8f0; background: #fff; color: var(--text-muted); transition: 0.3s; white-space: nowrap; cursor: pointer; }
        .btn-filter.active { background: var(--primary-color); color: #fff; border-color: var(--primary-color); box-shadow: 0 4px 10px rgba(13, 148, 136, 0.2); }

        .search-box-custom { position: relative; min-width: 250px; flex-grow: 1; max-width: 400px; }
        .search-box-custom input { border-radius: 50px; padding: 10px 20px 10px 40px; border: 1px solid var(--border-color); width: 100%; background: #f8fafc; font-size: 0.9rem; transition: 0.3s; }
        .search-box-custom input:focus { background: #fff; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); outline: none; }
        .search-box-custom i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .results-count { font-weight: 800; color: var(--primary-color); background: var(--primary-light); padding: 4px 12px; border-radius: 50px; font-size: 0.85rem; white-space: nowrap; }

        /* --- Professional Table Styling --- */
        .table-card { border-radius: 20px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); background: #fff; }
        .table-custom { margin-bottom: 0; }
        .table-custom th { padding: 18px 20px; font-size: 0.85rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; background: #f8fafc; border-bottom: 2px solid var(--border-color); letter-spacing: 0.5px; white-space: nowrap; }
        .table-custom td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
        .table-custom tbody tr { transition: background-color 0.2s ease; }
        .table-custom tbody tr:hover { background-color: #fcfcfc; }
        .table-custom tr:last-child td { border-bottom: none; }
        .teacher-avatar { width: 45px; height: 45px; object-fit: cover; border-radius: 12px; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }

        /* Status Badges */
        .badge-status { font-size: 0.75rem; padding: 6px 14px; border-radius: 50px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
        .status-live-pulse { width: 8px; height: 8px; background: #ef4444; border-radius: 50%; animation: pulse-dot 1.5s infinite; }
        .channel-badge { font-family: 'Monaco', monospace; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0;}

        @keyframes pulse-dot { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

        /* Action Buttons */
        .btn-action-group { display: flex; justify-content: flex-end; gap: 6px; flex-wrap: wrap; }
        .action-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; color: var(--text-muted); transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); text-decoration: none; }
        .action-btn:hover { background: var(--bg-light); color: var(--text-main); border-color: #cbd5e1; transform: translateY(-2px); }
        .action-btn.delete:hover { background: #fef2f2; color: #ef4444; border-color: #fee2e2; }
        .action-btn.copy:hover { background: var(--primary-light); color: var(--primary-color); border-color: #a7f3d0; }

        /* زر التسجيل */
        .action-btn.record-play { background: #eff6ff; color: #3b82f6; border-color: #bfdbfe; }
        .action-btn.record-play:hover { background: #3b82f6; color: #ffffff; border-color: #2563eb; }

        /* Modal & Select2 Styling */
        .modal-content-pro { border-radius: 24px; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .form-control-pro { border-radius: 12px; padding: 12px 16px; border: 1.5px solid #e2e8f0; background-color: #f8fafc; transition: all 0.2s; font-size: 0.95rem; }
        .form-control-pro:focus { background-color: #fff; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); outline: none;}
        .select2-container--default .select2-selection--single { height: auto !important; min-height: 50px !important; padding: 8px 12px !important; border-radius: 12px !important; border: 1.5px solid #e2e8f0 !important; background-color: #f8fafc !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { top: 12px !important; right: 10px !important; }
        .select2-track-badge { background: #e0e7ff; color: #4338ca; font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; margin-right: 4px; font-weight: bold; }
        .select2-dropdown { z-index: 100000; }

        /* Attendance List */
        .student-list-item { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s; }
        .student-list-item:hover { background: #f8fafc; }
        .student-list-item:last-child { border-bottom: none; }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* --- Media Queries (Responsiveness) --- */
        @media (max-width: 768px) {
            .filters-wrapper { flex-direction: column; align-items: stretch; gap: 10px; }
            .filter-btn-group { overflow-x: auto; flex-wrap: nowrap; white-space: nowrap; padding-bottom: 8px; -webkit-overflow-scrolling: touch; }
            .filters-wrapper > div.d-flex { flex-direction: column-reverse; align-items: stretch !important; max-width: 100% !important; }
            .search-box-custom { max-width: 100%; width: 100%; }
            .results-count { align-self: flex-start; }
            .table-custom th, .table-custom td { padding: 12px 15px; }
        }
    </style>
@endsection

@section('title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            <h5 class="m-0 fw-bold fs-5">المقرأه</h5>
        </div>
    </div>
@endsection

@section('content')

{{-- التنبيهات --}}
<div class="fixed-alert-container">
    @if(session('success'))
        <div class="custom-toast success shadow-lg">
            <div class="toast-content">
                <i class="fa-solid fa-circle-check text-success fs-3"></i>
                <div>
                    <span class="toast-title">تم بنجاح</span>
                    <p class="toast-message">{{ session('success') }}</p>
                </div>
                <button type="button" class="btn-close ms-auto" onclick="this.closest('.custom-toast').remove()"></button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="custom-toast error shadow-lg">
            <div class="toast-content">
                <i class="fa-solid fa-circle-exclamation text-danger fs-3"></i>
                <div>
                    <span class="toast-title">يوجد خطأ بالبيانات</span>
                    @foreach($errors->all() as $error)
                        <p class="toast-message">- {{ $error }}</p>
                    @endforeach
                </div>
                <button type="button" class="btn-close ms-auto" onclick="this.closest('.custom-toast').remove()"></button>
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
                    <li class="breadcrumb-item small active text-primary fw-bold">الفصول الذكية</li>
                </ol>
            </nav>
            <h3 class="fw-800 text-dark m-0">إدارة الجلسات الحية</h3>
            <p class="text-muted small mt-1 mb-0">قم بجدولة ومتابعة الجلسات المباشرة بين المعلمين والطلاب.</p>
        </div>
        <button class="btn btn-dark rounded-pill px-4 py-2 fw-bold shadow-lg transition-all" data-bs-toggle="modal" data-bs-target="#createSessionModal">
            <i class="fa-solid fa-video me-2"></i> جدولة جلسة جديدة
        </button>
    </div>

    {{-- الفلاتر والبحث الاحترافي --}}
    <div class="filters-wrapper">
        <div class="filter-btn-group">
            <button type="button" class="btn-filter active" data-filter="all">الكل</button>
            <button type="button" class="btn-filter" data-filter="live">جاري الآن <span class="status-live-pulse d-inline-block ms-1" style="width:6px;height:6px;"></span></button>
            <button type="button" class="btn-filter" data-filter="upcoming">قادمة</button>
            <button type="button" class="btn-filter" data-filter="ended">مكتملة</button>
        </div>

        <div class="d-flex align-items-center gap-3 w-100 w-md-auto" style="flex: 1; max-width: 500px; justify-content: flex-end;">
            <span class="results-count" id="tableCount">{{ $sessions->count() }} جلسة</span>
            <div class="search-box-custom">
                <input type="text" id="searchInput" placeholder="ابحث باسم الجلسة، المعلم، أو المعرف...">
                <i class="fa-solid fa-search"></i>
            </div>
        </div>
    </div>

    {{-- الجدول الاحترافي --}}
    <div class="table-card shadow-sm mb-5">
        <div class="table-responsive custom-scrollbar">
            <table class="table table-custom align-middle mb-0 text-center" style="min-width: 1100px;">
                <thead>
                    <tr>
                        <th class="text-start">تفاصيل الجلسة</th>
                        <th class="text-start">المعلم</th>
                        <th class="text-start">التوقيت والمدة</th>
                        <th class="text-center">الحضور / السعة</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="sessionsTableBody">
                    @forelse($sessions as $session)
                        @php
                            // ====== حساب الحالة بشكل دقيق ======
                            $now = \Carbon\Carbon::now();
                            $startAt = \Carbon\Carbon::parse($session->start_at);
                            $endAt = \Carbon\Carbon::parse($session->end_at);
                            $dbStatus = strtolower($session->status ?? '');

                            // 1. إذا كانت الحالة في قاعدة البيانات "ended" أو الوقت الحالي تخطى وقت الانتهاء -> مكتملة
                            if ($dbStatus === 'ended' || $now->isAfter($endAt)) {
                                $currentStatus = 'ended';
                            }
                            // 2. إذا كانت الحالة في قاعدة البيانات "live" أو الوقت الحالي يقع بين وقت البدء والانتهاء -> لايف
                            elseif ($dbStatus === 'live' || $now->between($startAt, $endAt)) {
                                $currentStatus = 'live';
                            }
                            // 3. خلاف ذلك (لم تبدأ بعد) -> قادمة
                            else {
                                $currentStatus = 'upcoming';
                            }

                            // حساب المدة
                            $duration = $session->duration_minutes ?? $startAt->diffInMinutes($endAt);

                            // دمج النصوص للبحث
                            $tName = optional(optional($session->teacher)->user)->name ?? '';
                            $searchString = strtolower($session->title . ' ' . $tName . ' ' . $session->channel_name);
                        @endphp

                        <tr class="session-row" data-status="{{ $currentStatus }}" data-search="{{ $searchString }}">
                            {{-- تفاصيل الجلسة --}}
                            <td class="text-start">
                                <h6 class="fw-bold text-dark m-0 mb-2" style="font-size: 1rem;">{{ $session->title }}</h6>
                                <span class="channel-badge"><i class="fa-solid fa-hashtag text-muted me-1"></i>{{ $session->channel_name }}</span>
                            </td>

                            {{-- المعلم --}}
                            <td class="text-start">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($tName ?: 'Teacher') }}&background=0d9488&color=fff&bold=true" class="teacher-avatar">
                                    <div>
                                        <h6 class="m-0 fw-bold text-dark">{{ $tName ?: 'معلم محذوف' }}</h6>
                                        <small class="text-muted">{{ optional(optional($session->teacher)->user)->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- التوقيت --}}
                            <td class="text-start">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark"><i class="fa-regular fa-calendar text-primary me-1"></i> {{ $startAt->translatedFormat('d M Y') }}</span>
                                    <div class="mt-1 small">
                                        <span class="text-muted">من</span> <span class="fw-bold">{{ $startAt->format('h:i A') }}</span>
                                        <span class="text-muted mx-1">إلى</span> <span class="fw-bold">{{ $endAt->format('h:i A') }}</span>
                                        <span class="badge bg-success bg-opacity-10 text-success ms-2 border border-success border-opacity-25">{{ $duration }} دقيقة</span>
                                    </div>
                                </div>
                            </td>

                            {{-- الحضور --}}
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <span class="badge bg-light text-dark border fs-6 px-3 py-2 shadow-sm">
                                        <i class="fa-solid fa-users text-primary me-2"></i> {{ $session->students ? $session->students->count() : 0 }} / {{ $session->max_participants }}
                                    </span>
                                    @if(isset($session->students) && $session->students->count() > 0)
                                        <button class="btn btn-link btn-sm text-decoration-none fw-bold p-0" data-bs-toggle="modal" data-bs-target="#attendeesModal_{{ $session->id }}">عرض السجل</button>
                                    @else
                                        <span class="text-muted small">لا يوجد حضور</span>
                                    @endif
                                </div>
                            </td>

                            {{-- الحالة --}}
                            <td class="text-center">
                                @if($currentStatus == 'live')
                                    <span class="badge bg-danger-subtle text-danger badge-status border border-danger border-opacity-25"><span class="status-live-pulse"></span> جاري الآن</span>
                                @elseif($currentStatus == 'upcoming')
                                    <span class="badge bg-primary-subtle text-primary badge-status border border-primary border-opacity-25"><i class="fa-regular fa-clock me-1"></i> قادمة</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary badge-status border border-secondary border-opacity-25"><i class="fa-solid fa-check-double me-1"></i> مكتملة</span>
                                @endif
                            </td>

                            {{-- الإجراءات --}}
                            <td class="text-end">
                                <div class="btn-action-group">
                                    {{-- 🎥 زر التسجيل: يظهر فقط إذا كان يوجد رابط تسجيل في قاعدة البيانات --}}
                                    @if(!empty($session->recording_url))
                                        <a href="{{ $session->recording_url }}" target="_blank" class="action-btn record-play" title="مشاهدة التسجيل">
                                            <i class="fa-solid fa-play"></i>
                                        </a>
                                    @endif
{{--
                                    <button class="action-btn copy" onclick="copyLink('{{ $session->channel_name }}')" title="نسخ رابط الجلسة">
                                        <i class="fa-solid fa-link"></i>
                                    </button> --}}
                                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editSessionModal_{{ $session->id }}" title="تعديل">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteSessionModal_{{ $session->id }}" title="حذف">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyStateRow">
                            <td colspan="6" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-3 opacity-25">
                                <h5 class="text-muted fw-bold m-0">لا يوجد جلسات مجدولة حالياً</h5>
                            </td>
                        </tr>
                    @endforelse

                    {{-- صف فارغ لنتائج البحث --}}
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
</div>
@endsection

{{-- ========================================== --}}
{{-- 3. Modals Section --}}
{{-- ========================================== --}}
@section('modals')

    {{-- Modal: Create Session --}}
    <div class="modal fade" id="createSessionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <form action="{{ route('admin.recitations.store') }}" method="POST" class="modal-content modal-content-pro">
                @csrf
                <div class="modal-header border-0 p-4 pb-0">
                    <h4 class="fw-800 m-0 text-dark"><i class="fa-solid fa-video text-primary me-2"></i>جدولة حصة ذكية جديدة</h4>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 custom-scrollbar">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small">عنوان الجلسة <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-pro" placeholder="مثلاً: مراجعة أحكام التجويد - سورة البقرة" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold small">اختيار المعلم (ابحث بالاسم أو المسار) <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="teacherSelectCreate" class="form-select select2-teacher-create" style="width: 100%" required>
                                <option></option>
                                @foreach($teachers as $teacher)
                                    @php
                                        $tracksString = '';
                                        if(isset($teacher->tracks) && $teacher->tracks->count() > 0) {
                                            $tracksString = implode(' , ', $teacher->tracks->pluck('name')->toArray());
                                        }
                                        $tName = optional($teacher->user)->name ?? 'معلم';
                                    @endphp
                                    <option value="{{ $teacher->id }}"
                                            data-image="https://ui-avatars.com/api/?name={{ urlencode($tName) }}&background=0d9488&color=fff"
                                            data-tracks="{{ $tracksString }}">
                                        {{ $tName }} {{ $tracksString ? '- ('.$tracksString.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">أقصى عدد للطلاب <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 12px 0 0 12px"><i class="fa-solid fa-user-plus"></i></span>
                                <input type="number" name="max_participants" class="form-control form-control-pro border-start-0 ps-0" value="10" min="1" required style="border-radius: 0 12px 12px 0">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">تاريخ ووقت البدء <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_at" class="form-control form-control-pro" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">تاريخ ووقت الانتهاء <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_at" class="form-control form-control-pro" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 mt-3 border-top pt-3">
                    <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm">حفظ وتفعيل الجلسة</button>
                </div>
            </form>
        </div>
    </div>

    @foreach($sessions as $session)
        {{-- Modal: View Attendees --}}
        <div class="modal fade" id="attendeesModal_{{ $session->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content modal-content-pro">
                    <div class="modal-header border-bottom p-4">
                        <div>
                            <h5 class="fw-800 m-0 text-dark"><i class="fa-solid fa-clipboard-user text-primary me-2"></i>سجل الحضور</h5>
                            <small class="text-muted mt-1 d-block">{{ $session->title }}</small>
                        </div>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0 custom-scrollbar">
                        @if(isset($session->students))
                            @forelse($session->students as $attendee)
                                <div class="student-list-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(optional($attendee->student)->name ?? 'طالب') }}&background=random" class="rounded-circle shadow-sm" width="45" height="45">
                                        <div>
                                            <h6 class="m-0 fw-bold text-dark">{{ optional($attendee->student)->name ?? 'طالب محذوف' }}</h6>
                                            <small class="text-muted">{{ optional($attendee->student)->email ?? '' }}</small>
                                        </div>
                                    </div>
                                    <div class="text-end" style="background: #f8fafc; padding: 8px 12px; border-radius: 10px; border: 1px solid #e2e8f0;">
                                        <small class="d-block text-success fw-bold mb-1"><i class="fa-solid fa-arrow-right-to-bracket"></i> دخول: {{ optional($attendee->joined_at)->format('h:i A') ?? '-' }}</small>
                                        @if($attendee->left_at)
                                            <small class="d-block text-danger fw-bold"><i class="fa-solid fa-arrow-right-from-bracket"></i> خروج: {{ \Carbon\Carbon::parse($attendee->left_at)->format('h:i A') }}</small>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mt-1">متواجد حالياً</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-5 text-center text-muted">
                                    <i class="fa-solid fa-users-slash fs-1 mb-3 opacity-25"></i>
                                    <p class="m-0">لا يوجد حضور مسجل حتى الآن.</p>
                                </div>
                            @endforelse
                        @endif
                    </div>
                    <div class="modal-footer border-0 p-3 justify-content-center">
                        <button type="button" class="btn btn-light rounded-pill px-5 fw-bold border shadow-sm" data-bs-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal: Edit Session --}}
        <div class="modal fade" id="editSessionModal_{{ $session->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <form action="{{ route('admin.recitations.update', $session->id) }}" method="POST" class="modal-content modal-content-pro">
                    @csrf @method('PUT')
                    <div class="modal-header border-0 p-4 pb-0">
                        <h4 class="fw-800 m-0 text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>تعديل بيانات الجلسة</h4>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 custom-scrollbar">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">عنوان الجلسة</label>
                                <input type="text" name="title" class="form-control form-control-pro" value="{{ $session->title }}" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold small">المعلم المسؤول</label>
                                <select name="teacher_id" class="form-select select2-teacher-edit" style="width: 100%" required>
                                    @foreach($teachers as $teacher)
                                        @php
                                            $tracksString = '';
                                            if(isset($teacher->tracks) && $teacher->tracks->count() > 0) {
                                                $tracksString = implode(' , ', $teacher->tracks->pluck('name')->toArray());
                                            }
                                            $tName = optional($teacher->user)->name ?? 'معلم';
                                        @endphp
                                        <option value="{{ $teacher->id }}"
                                                data-image="https://ui-avatars.com/api/?name={{ urlencode($tName) }}&background=0d9488&color=fff"
                                                data-tracks="{{ $tracksString }}"
                                                {{ $session->teacher_id == $teacher->id ? 'selected' : '' }}>
                                            {{ $tName }} {{ $tracksString ? '- ('.$tracksString.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">أقصى عدد للطلاب</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 12px 0 0 12px"><i class="fa-solid fa-user-plus"></i></span>
                                    <input type="number" name="max_participants" class="form-control form-control-pro border-start-0 ps-0" value="{{ $session->max_participants }}" min="1" required style="border-radius: 0 12px 12px 0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">تاريخ ووقت البدء</label>
                                <input type="datetime-local" name="start_at" class="form-control form-control-pro" value="{{ \Carbon\Carbon::parse($session->start_at)->format('Y-m-d\TH:i') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">تاريخ ووقت الانتهاء</label>
                                <input type="datetime-local" name="end_at" class="form-control form-control-pro" value="{{ \Carbon\Carbon::parse($session->end_at)->format('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 mt-3 border-top pt-3">
                        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Delete Session --}}
        <div class="modal fade" id="deleteSessionModal_{{ $session->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content modal-content-pro text-center">
                    <div class="modal-body p-4">
                        <i class="fa-solid fa-circle-exclamation text-danger mb-3 opacity-75" style="font-size: 4rem;"></i>
                        <h5 class="fw-bold mb-2">تأكيد الحذف</h5>
                        <p class="text-muted small mb-4">هل أنت متأكد من حذف الجلسة <strong>"{{ $session->title }}"</strong> نهائياً؟</p>

                        <form action="{{ route('admin.recitations.destroy', $session->id) }}" method="POST" class="m-0">
                            @csrf @method('DELETE')
                            <div class="d-flex flex-column gap-2">
                                <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm">نعم، احذف الجلسة</button>
                                <button type="button" class="btn btn-light border rounded-pill fw-bold py-2 shadow-sm" data-bs-dismiss="modal">تراجع</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // إخفاء التنبيهات تلقائياً
        setTimeout(function() {
            $('.custom-toast').fadeOut('slow');
        }, 5000);

        // حل مشكلة تركيز Select2 داخل Modals Bootstrap 5
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};

        // تنسيق ظهور المعلم بـ Select2
        function formatTeacher(teacher) {
            if (!teacher.id) return teacher.text;

            var $el = $(teacher.element);
            var img = $el.attr('data-image') || '';
            var tracks = $el.attr('data-tracks') || '';
            var name = String(teacher.text || '').split('-')[0].trim();

            var trackBadges = '';
            if(tracks && tracks.trim() !== '') {
                var tracksArray = tracks.split(',');
                tracksArray.forEach(function(track) {
                    trackBadges += '<span class="select2-track-badge">' + track.trim() + '</span>';
                });
            } else {
                trackBadges = '<span class="text-muted small">لا يوجد مسارات</span>';
            }

            return $(
                '<div class="d-flex align-items-center gap-3">' +
                    '<img src="' + img + '" class="rounded-circle shadow-sm" width="35" height="35" style="object-fit:cover;" />' +
                    '<div class="d-flex flex-column">' +
                        '<span class="fw-bold text-dark" style="font-size: 0.95rem; line-height: 1;">' + name + '</span>' +
                        '<div class="mt-1 d-flex flex-wrap gap-1">' + trackBadges + '</div>' +
                    '</div>' +
                '</div>'
            );
        };

        // تفعيل Select2 لمودال الإضافة مع تحديد الـ dropdownParent لكي لا تختفي القائمة
        $('#createSessionModal').on('shown.bs.modal', function () {
            $('.select2-teacher-create').select2({
                placeholder: "ابحث عن معلم أو باسم المسار...",
                templateResult: formatTeacher,
                templateSelection: formatTeacher,
                dropdownParent: $('#createSessionModal')
            });
        });

        // تفعيل Select2 لمودالات التعديل عند فتحها
        $('[id^=editSessionModal_]').on('shown.bs.modal', function () {
            var modalId = $(this).attr('id');
            $(this).find('.select2-teacher-edit').select2({
                placeholder: "ابحث عن معلم أو باسم المسار...",
                templateResult: formatTeacher,
                templateSelection: formatTeacher,
                dropdownParent: $('#' + modalId)
            });
        });

        // ==========================================
        // 🔍 فلترة وبحث الجدول بطريقة آمنة
        // ==========================================
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

            // تحديث العداد
            $('#tableCount').text(visibleCount + ' جلسة');

            // إظهار/إخفاء رسالة "لا يوجد نتائج"
            if (visibleCount === 0 && $('.session-row').length > 0) {
                $('#noResultsRow').show();
            } else {
                $('#noResultsRow').hide();
            }
        }

        // حدث الضغط على أزرار الفلتر
        $('.btn-filter').on('click', function(e) {
            e.preventDefault();
            $('.btn-filter').removeClass('active');
            $(this).addClass('active');

            currentFilter = String($(this).attr('data-filter') || 'all').toLowerCase();
            filterTable();
        });

        // حدث الكتابة في مربع البحث
        $('#searchInput').on('keyup', function() {
            filterTable();
        });
    });

    // ==========================================
    // 🔗 وظيفة نسخ الرابط السريع
    // ==========================================
    function copyLink(channel) {
        const link = window.location.origin + '/live/' + channel;
        navigator.clipboard.writeText(link).then(() => {
             Swal.fire({
                icon: 'success',
                title: 'تم النسخ!',
                text: 'الرابط (' + link + ') جاهز للمشاركة.',
                timer: 2500,
                showConfirmButton: false,
                backdrop: `rgba(0,0,0,0.4)`
            });
        }).catch(() => {
             Swal.fire('خطأ', 'لم نتمكن من نسخ الرابط، الرجاء المحاولة يدوياً.', 'error');
        });
    }
</script>
@endsection
