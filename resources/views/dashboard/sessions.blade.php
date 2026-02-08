@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* Professional Card Styling */
        .session-card-row {
            background: #fff;
            border: 1px solid #eef2f6;
            border-radius: 16px;
            margin-bottom: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: visible; /* For dropdowns */
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        .session-card-row:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
            border-color: #dee2e6;
        }

        /* Status Indicators */
        .status-line {
            position: absolute;
            left: 0;
            top: 20px;
            bottom: 20px;
            width: 4px;
            border-radius: 0 4px 4px 0;
        }
        .status-active .status-line { background-color: #10b981; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }
        .status-upcoming .status-line { background-color: #3b82f6; }
        .status-completed .status-line { background-color: #9ca3af; }

        /* Date Widget */
        .date-widget {
            background: #f8fafc;
            border-radius: 12px;
            padding: 8px 12px;
            text-align: center;
            min-width: 70px;
            border: 1px solid #f1f5f9;
        }
        .date-day { font-size: 20px; font-weight: 800; color: #1e293b; line-height: 1; }
        .date-month { font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b; margin-top: 2px; }

        /* Teacher Avatar */
        .teacher-info { display: flex; align-items: center; gap: 10px; }
        .teacher-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* Student Stack */
        .student-stack { display: flex; align-items: center; }
        .student-stack img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #fff;
            margin-left: -10px;
            object-fit: cover;
            background: #e2e8f0;
        }
        .student-stack .more-count {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 2px solid #fff;
            margin-left: -10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
        }

        /* Action Buttons */
        .btn-join-agora {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.3s;
        }
        .btn-join-agora:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4);
            color: white;
        }
        .live-indicator {
            display: inline-flex;
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            margin-left: 6px;
            animation: blink 1.5s infinite;
        }

        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
    </style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h5 class="m-0 fw-bold text-dark">الفصول الافتراضية</h5>
        <small class="text-muted">إدارة جلسات الفيديو المباشرة (Agora)</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-white border shadow-sm fw-bold">
            <i class="fa-solid fa-file-export me-1 text-muted"></i> تقرير الحضور
        </button>
        <button class="btn btn-primary fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createSessionModal">
            <i class="fa-solid fa-plus me-2"></i> جدولة حصة جديدة
        </button>
    </div>
</div>
@endsection

@section('content')

{{-- FAKE DATA GENERATION --}}
@php
    $sessions = [
        (object)[
            'id' => 101,
            'title' => 'تصحيح تلاوة - سورة الكهف',
            'teacher' => 'الشيخ أحمد عامر',
            'teacher_img' => 'https://ui-avatars.com/api/?name=Ahmed+Amer&background=0D8ABC&color=fff',
            'date_day' => '25',
            'date_month' => 'أكتوبر',
            'time_range' => '06:00 م - 07:30 م',
            'students_count' => 8,
            'limit' => 10,
            'status' => 'live', // live, upcoming, completed
            'agora_channel' => 'class_101_xf32'
        ],
        (object)[
            'id' => 102,
            'title' => 'أحكام النون الساكنة والتنوين',
            'teacher' => 'أ. سارة محمد',
            'teacher_img' => 'https://ui-avatars.com/api/?name=Sara+Mohamed&background=E91E63&color=fff',
            'date_day' => '26',
            'date_month' => 'أكتوبر',
            'time_range' => '04:00 م - 05:00 م',
            'students_count' => 3,
            'limit' => 15,
            'status' => 'upcoming',
            'agora_channel' => 'class_102_yy99'
        ],
        (object)[
            'id' => 103,
            'title' => 'مراجعة الحفظ - جزء عم',
            'teacher' => 'الشيخ محمد محمود',
            'teacher_img' => 'https://ui-avatars.com/api/?name=Mohamed+Mahmoud&background=FF9800&color=fff',
            'date_day' => '24',
            'date_month' => 'أكتوبر',
            'time_range' => '10:00 ص - 11:00 ص',
            'students_count' => 12,
            'limit' => 12,
            'status' => 'completed',
            'agora_channel' => 'class_103_zz11'
        ],
    ];
@endphp

<div class="container-fluid p-4">

    {{-- Stats Cards (Top) --}}
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 py-2">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-video text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">جلسات هذا الشهر</h6>
                        <h4 class="fw-bold m-0 text-dark">128</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 py-2">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-users-viewfinder text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">إجمالي الحضور</h6>
                        <h4 class="fw-bold m-0 text-dark">1,450</h4>
                    </div>
                </div>
            </div>
        </div>
        {{-- Add more stats here --}}
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white p-3 rounded-3 shadow-sm border mb-4 d-flex gap-3 align-items-center">
        <div class="text-muted fw-bold small"><i class="fa-solid fa-filter me-1"></i> تصفية:</div>
        <select class="form-select form-select-sm border-0 bg-light" style="width: 150px;">
            <option>جميع الحالات</option>
            <option>جارية الآن</option>
            <option>القادمة</option>
        </select>
        <select class="form-select form-select-sm border-0 bg-light" style="width: 180px;">
            <option>كل المعلمين</option>
            {{-- Loop Teachers --}}
        </select>
        <div class="vr mx-2"></div>
        <input type="text" class="form-control form-control-sm border-0 bg-light" placeholder="بحث باسم الحلقة..." style="max-width: 250px;">
    </div>

    {{-- SESSIONS LIST --}}
    <div class="sessions-wrapper">
        @foreach($sessions as $session)
            @php
                $statusClass = 'status-' . $session->status;
                $isLive = $session->status == 'live';

                // Capacity Logic
                $percent = ($session->students_count / $session->limit) * 100;
                $progressColor = $percent >= 90 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success');
            @endphp

            <div class="session-card-row p-3 ps-4 {{ $statusClass }}">
                <div class="status-line"></div>

                <div class="row align-items-center">

                    {{-- 1. Date Widget --}}
                    <div class="col-auto">
                        <div class="date-widget">
                            <div class="date-day">{{ $session->date_day }}</div>
                            <div class="date-month">{{ $session->date_month }}</div>
                        </div>
                    </div>

                    {{-- 2. Main Info --}}
                    <div class="col-lg-3">
                        <h6 class="fw-bold text-dark mb-1">{{ $session->title }}</h6>
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <span><i class="fa-regular fa-clock me-1"></i> {{ $session->time_range }}</span>
                        </div>
                        @if($isLive)
                            <div class="mt-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2">
                                    <i class="fa-solid fa-circle fa-xs me-1 animate-pulse"></i> مباشر
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- 3. Teacher --}}
                    <div class="col-lg-3">
                        <div class="teacher-info">
                            <img src="{{ $session->teacher_img }}" class="teacher-avatar" alt="">
                            <div style="line-height: 1.3;">
                                <div class="small text-muted">المعلم</div>
                                <div class="fw-bold text-dark fs-6">{{ $session->teacher }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Students Stack & Progress --}}
                    <div class="col-lg-2">
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <small class="text-muted fw-bold">الطلاب</small>
                            <small class="fw-bold">{{ $session->students_count }}/{{ $session->limit }}</small>
                        </div>
                        {{-- Avatars Stack --}}
                        <div class="student-stack mb-2">
                            <img src="https://ui-avatars.com/api/?name=User+1&background=random" title="User 1">
                            <img src="https://ui-avatars.com/api/?name=User+2&background=random" title="User 2">
                            <img src="https://ui-avatars.com/api/?name=User+3&background=random" title="User 3">
                            @if($session->students_count > 3)
                            <div class="more-count">+{{ $session->students_count - 3 }}</div>
                            @endif
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar {{ $progressColor }}" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    {{-- 5. Actions (Agora Logic) --}}
                    <div class="col-lg-3 text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2">

                            @if($isLive)
                                <a href="#" class="btn btn-join-agora btn-sm fw-bold px-3 py-2 rounded-pill">
                                    <span class="live-indicator"></span> دخول الفصل
                                </a>
                            @elseif($session->status == 'upcoming')
                                <button class="btn btn-light btn-sm text-primary fw-bold" onclick="copyLink('{{ $session->agora_channel }}')">
                                    <i class="fa-solid fa-link"></i> نسخ الرابط
                                </button>
                            @else
                                <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">منتهية</span>
                            @endif

                            {{-- Menu --}}
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle border-0" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 12px;">
                                    <li><button class="dropdown-item rounded small py-2" data-bs-toggle="modal" data-bs-target="#editSession{{ $session->id }}">تعديل التفاصيل</button></li>
                                    <li><button class="dropdown-item rounded small py-2" data-bs-toggle="modal" data-bs-target="#viewStudents{{ $session->id }}">قائمة الحضور</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button class="dropdown-item rounded small py-2 text-danger" data-bs-toggle="modal" data-bs-target="#deleteSession{{ $session->id }}">إلغاء الحصة</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- INCLUDE MODALS (Edit/Delete/View) SAME AS BEFORE --}}
            {{-- Note: Keeping structure similar to previous response for brevity,
                 but in a real app, include the specific modals here --}}

        @endforeach
    </div>
</div>
@endsection

@section('modals')
{{-- CREATE NEW SESSION MODAL (Professional Agora Version) --}}
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <form action="#" method="POST">
                @csrf

                {{-- Modal Header --}}
                <div class="modal-header bg-white border-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">جدولة حصة افتراضية جديدة</h5>
                        <p class="text-muted small m-0">سيتم إنشاء غرفة Video Call تلقائياً عبر Agora</p>
                    </div>
                    <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- Title Section --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary text-uppercase">تفاصيل الحصة</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0" name="title" placeholder="عنوان الحصة (مثال: تصحيح تلاوة الجزء الأول)" required>
                    </div>

                    <div class="row g-3">
                        {{-- Teacher --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">المعلم</label>
                            <select class="form-select border-light bg-light py-2" name="teacher_id">
                                <option>الشيخ أحمد عامر</option>
                                <option>أ. سارة محمد</option>
                            </select>
                        </div>

                        {{-- Limit --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">سعة الفصل</label>
                            <input type="number" class="form-control border-light bg-light py-2" name="limit" value="10">
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        {{-- Date --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">التاريخ</label>
                            <input type="date" class="form-control border-light bg-light py-2" name="date" required>
                        </div>
                        {{-- Time Start --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">البداية</label>
                            <input type="time" class="form-control border-light bg-light py-2" name="start_time" required>
                        </div>
                        {{-- Time End --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">النهاية</label>
                            <input type="time" class="form-control border-light bg-light py-2" name="end_time" required>
                        </div>
                    </div>

                    {{-- Agora Auto-Gen Note --}}
                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 d-flex align-items-center mt-4 mb-0 rounded-3">
                        <i class="fa-solid fa-bolt text-primary me-3 fs-5"></i>
                        <div class="small text-primary">
                            <strong>نظام Agora:</strong> سيتم إنشاء رابط الحصة وتفعيله للمعلم والطلاب تلقائياً قبل الموعد بـ 10 دقائق.
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-dark fw-bold px-5 rounded-pill">
                        <i class="fa-regular fa-calendar-check me-2"></i> تأكيد الحجز
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Simple Toast for Copy Link
    function copyLink(channelId) {
        // In real app, build the full URL
        const fullLink = window.location.origin + '/classroom/join/' + channelId;

        navigator.clipboard.writeText(fullLink).then(() => {
            // SweetAlert or standard alert
            alert('تم نسخ رابط الدعوة: ' + fullLink);
        });
    }
</script>
@endsection
