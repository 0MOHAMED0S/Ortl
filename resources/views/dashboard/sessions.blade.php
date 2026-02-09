@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* --- Session Card Styling --- */
        .session-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .session-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
            border-color: var(--primary-main);
        }

        /* Status Border Indicator */
        .status-strip {
            position: absolute;
            top: 0; bottom: 0; right: 0; /* RTL */
            width: 5px;
        }
        .status-live .status-strip { background: #ef4444; box-shadow: 0 0 10px rgba(239, 68, 68, 0.5); }
        .status-upcoming .status-strip { background: #3b82f6; }
        .status-completed .status-strip { background: #cbd5e1; }

        /* Date Widget */
        .date-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px;
            text-align: center;
            min-width: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .day-num { font-size: 1.8rem; font-weight: 800; line-height: 1; color: #1e293b; }
        .month-name { font-size: 0.8rem; font-weight: 700; color: #64748b; margin-top: 2px; }

        /* Teacher Avatar */
        .teacher-info { display: flex; align-items: center; gap: 12px; }
        .teacher-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        /* Students Stack */
        .student-stack { display: flex; align-items: center; }
        .student-stack img {
            width: 30px; height: 30px; border-radius: 50%; border: 2px solid #fff;
            margin-left: -10px; object-fit: cover; background: #e2e8f0; transition: 0.2s;
        }
        .student-stack img:hover { transform: translateY(-3px); z-index: 5; }
        .more-badge {
            width: 30px; height: 30px; border-radius: 50%; background: #f1f5f9;
            color: #64748b; font-size: 10px; font-weight: 700; border: 2px solid #fff;
            display: flex; align-items: center; justify-content: center; margin-left: -10px;
        }

        /* Action Buttons */
        .btn-live {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white; border: none; font-weight: 700; padding: 8px 20px;
            border-radius: 50px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            animation: pulse-red 2s infinite;
        }
        .btn-live:hover { color: white; transform: scale(1.05); }

        .btn-copy {
            background: #e0f2fe; color: #0284c7; border: none; font-weight: 700;
            padding: 8px 15px; border-radius: 50px; transition: 0.2s;
        }
        .btn-copy:hover { background: #0284c7; color: white; }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Responsive */
        @media (max-width: 991px) {
            .session-card .row > div {
                margin-bottom: 15px;
                text-align: center;
            }
            .session-card .row > div:last-child { margin-bottom: 0; }

            .teacher-info, .student-stack { justify-content: center; }
            .date-box { width: 100%; flex-direction: row; align-items: baseline; gap: 10px; padding: 5px; }
            .day-num { font-size: 1.2rem; }
            .status-strip { width: 100%; height: 4px; bottom: auto; } /* Top border on mobile */
            .session-card { padding-top: 10px; }
        }
    </style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h5 class="m-0 fw-bold">الفصول الافتراضية</h5>
        <p class="text-muted small m-0 mt-1">إدارة جلسات الفيديو المباشرة (Agora)</p>
    </div>

</div>
@endsection

@section('content')

{{-- FAKE DATA --}}
@php
    $sessions = [
        (object)[
            'id' => 101,
            'title' => 'تصحيح تلاوة - سورة الكهف',
            'teacher' => 'الشيخ أحمد عامر',
            'teacher_img' => 'https://ui-avatars.com/api/?name=Ahmed+Amer&background=0D8ABC&color=fff',
            'date_day' => '25',
            'date_month' => 'أكتوبر',
            'time' => '06:00 م - 07:30 م',
            'students_count' => 8,
            'limit' => 10,
            'status' => 'live',
            'channel' => 'class_101_xf32'
        ],
        (object)[
            'id' => 102,
            'title' => 'أحكام النون الساكنة',
            'teacher' => 'أ. سارة محمد',
            'teacher_img' => 'https://ui-avatars.com/api/?name=Sara+Mohamed&background=E91E63&color=fff',
            'date_day' => '26',
            'date_month' => 'أكتوبر',
            'time' => '04:00 م - 05:00 م',
            'students_count' => 3,
            'limit' => 15,
            'status' => 'upcoming',
            'channel' => 'class_102_yy99'
        ],
        (object)[
            'id' => 103,
            'title' => 'مراجعة الحفظ - جزء عم',
            'teacher' => 'الشيخ محمد محمود',
            'teacher_img' => 'https://ui-avatars.com/api/?name=Mohamed+Mahmoud&background=FF9800&color=fff',
            'date_day' => '24',
            'date_month' => 'أكتوبر',
            'time' => '10:00 ص - 11:00 ص',
            'students_count' => 12,
            'limit' => 12,
            'status' => 'completed',
            'channel' => 'class_103_zz11'
        ],
    ];
@endphp

<div class="container-fluid p-3 p-md-4">
<center>
    <button class="btn btn-primary px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createSessionModal">
        <i class="fa-solid fa-video me-2"></i> حصة جديدة
    </button>
    <br>
    <br>
</center>
    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 text-danger rounded p-3 me-3">
                        <i class="fa-solid fa-tower-broadcast fs-4"></i>
                    </div>
                    <div>
                        <h4 class="m-0 fw-bold">1</h4>
                        <small class="text-muted">جلسات جارية الآن</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                        <i class="fa-solid fa-calendar-days fs-4"></i>
                    </div>
                    <div>
                        <h4 class="m-0 fw-bold">15</h4>
                        <small class="text-muted">جلسات مجدولة</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                        <i class="fa-solid fa-users-viewfinder fs-4"></i>
                    </div>
                    <div>
                        <h4 class="m-0 fw-bold">1,250</h4>
                        <small class="text-muted">إجمالي الحضور هذا الشهر</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sessions List --}}
    <div class="sessions-wrapper">
        @foreach($sessions as $session)
            @php
                $statusClass = 'status-' . $session->status;
                $isLive = $session->status == 'live';
                $progressPercent = ($session->students_count / $session->limit) * 100;
                $progressColor = $progressPercent >= 90 ? 'bg-danger' : ($progressPercent >= 50 ? 'bg-warning' : 'bg-success');
            @endphp

            <div class="session-card {{ $statusClass }}">
                <div class="status-strip"></div>
                <div class="card-body p-4">
                    <div class="row align-items-center">

                        {{-- 1. Date --}}
                        <div class="col-lg-1 col-md-2 mb-3 mb-md-0">
                            <div class="date-box">
                                <span class="day-num">{{ $session->date_day }}</span>
                                <span class="month-name">{{ $session->date_month }}</span>
                            </div>
                        </div>

                        {{-- 2. Title & Teacher --}}
                        <div class="col-lg-4 col-md-5 mb-3 mb-md-0 text-center text-md-end">
                            <h5 class="fw-bold text-dark mb-1">{{ $session->title }}</h5>
                            <div class="teacher-info mt-2 justify-content-center justify-content-md-start">
                                <img src="{{ $session->teacher_img }}" class="teacher-img" alt="Teacher">
                                <div class="text-end">
                                    <small class="text-muted d-block" style="font-size: 10px;">المعلم</small>
                                    <span class="fw-bold text-dark small">{{ $session->teacher }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Time & Students --}}
                        <div class="col-lg-3 col-md-5 mb-3 mb-md-0 text-center">
                            <div class="mb-2 text-muted small fw-bold">
                                <i class="fa-regular fa-clock me-1"></i> {{ $session->time }}
                            </div>

                            {{-- Capacity Bar --}}
                            <div class="d-flex justify-content-between small mb-1 px-3">
                                <span class="text-muted">الحضور</span>
                                <span class="fw-bold">{{ $session->students_count }}/{{ $session->limit }}</span>
                            </div>
                            <div class="progress mx-3" style="height: 6px;">
                                <div class="progress-bar {{ $progressColor }}" style="width: {{ $progressPercent }}%"></div>
                            </div>

                            {{-- Avatars --}}
                            <div class="student-stack mt-2 justify-content-center">
                                <img src="https://ui-avatars.com/api/?name=User+1&background=random" title="User 1">
                                <img src="https://ui-avatars.com/api/?name=User+2&background=random" title="User 2">
                                <img src="https://ui-avatars.com/api/?name=User+3&background=random" title="User 3">
                                @if($session->students_count > 3)
                                    <div class="more-badge">+{{ $session->students_count - 3 }}</div>
                                @endif
                                <button class="btn btn-sm btn-link text-decoration-none py-0 pe-0" data-bs-toggle="modal" data-bs-target="#viewStudentsModal{{ $session->id }}"><small>عرض</small></button>
                            </div>
                        </div>

                        {{-- 4. Actions --}}
                        <div class="col-lg-4 col-md-12 text-center text-lg-end mt-3 mt-lg-0">
                            <div class="d-flex justify-content-center justify-content-lg-end gap-2 align-items-center">
                                @if($isLive)
                                    <a href="#" class="btn-live">
                                        <i class="fa-solid fa-video me-2"></i> دخول الفصل
                                    </a>
                                @elseif($session->status == 'upcoming')
                                    <button class="btn-copy" onclick="copyLink('{{ $session->channel }}')">
                                        <i class="fa-solid fa-link me-1"></i> رابط
                                    </button>
                                @else
                                    <span class="badge bg-light text-muted border px-3 py-2">منتهية</span>
                                @endif

                                {{-- Dropdown --}}
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle border shadow-sm" style="width: 35px; height: 35px;" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 text-end">
                                        <li><button class="dropdown-item rounded small py-2" data-bs-toggle="modal" data-bs-target="#editSession{{ $session->id }}"><i class="fa-solid fa-pen text-muted me-2"></i> تعديل</button></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button class="dropdown-item rounded small py-2 text-danger" data-bs-toggle="modal" data-bs-target="#deleteSession{{ $session->id }}"><i class="fa-solid fa-trash me-2"></i> إلغاء</button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- === MODALS PER SESSION === --}}

            {{-- 1. Edit Modal --}}
            <div class="modal fade" id="editSession{{ $session->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <form action="#" method="POST"> @csrf @method('PUT')
                            <div class="modal-header bg-light border-0">
                                <h5 class="modal-title fw-bold">تعديل الحصة</h5>
                                <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">عنوان الحصة</label>
                                    <input type="text" class="form-control" name="title" value="{{ $session->title }}">
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold text-muted">التاريخ</label>
                                        <input type="date" class="form-control" name="date">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold text-muted">الوقت</label>
                                        <input type="time" class="form-control" name="time">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-light">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2. View Students Modal --}}
            <div class="modal fade" id="viewStudentsModal{{ $session->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0">
                        <div class="modal-header border-bottom">
                            <h6 class="modal-title fw-bold">الطلاب المسجلين ({{ $session->students_count }})</h6>
                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <ul class="list-group list-group-flush">
                                @for($i=1; $i<=$session->students_count; $i++)
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-4">
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=User+{{ $i }}&background=random" class="rounded-circle me-3" width="35" height="35">
                                        <span class="fw-bold text-dark">اسم الطالب {{ $i }}</span>
                                    </div>
                                    <span class="badge bg-light text-muted border">حاضر</span>
                                </li>
                                @endfor
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Delete Modal --}}
            <div class="modal fade" id="deleteSession{{ $session->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0">
                        <div class="modal-body text-center p-4">
                            <div class="text-danger mb-3"><i class="fa-solid fa-triangle-exclamation fs-1"></i></div>
                            <h5 class="fw-bold mb-2">إلغاء الحصة؟</h5>
                            <p class="text-muted small">سيتم إشعار الطلاب بإلغاء الموعد.</p>
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button class="btn btn-light" data-bs-dismiss="modal">تراجع</button>
                                <button class="btn btn-danger">نعم، إلغاء</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @endforeach
    </div>

</div>
@endsection

@section('modals')
{{-- CREATE SESSION MODAL --}}
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="#" method="POST">
                @csrf
                <div class="modal-header bg-white border-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">جدولة حصة افتراضية جديدة</h5>
                        <p class="text-muted small m-0">سيتم إنشاء غرفة Video Call تلقائياً عبر Agora</p>
                    </div>
                    <button type="button" class="btn-close bg-light rounded-circle" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">عنوان الحصة</label>
                        <input type="text" class="form-control bg-light border-0" name="title" placeholder="مثال: تصحيح تلاوة الجزء الأول" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">المعلم</label>
                            <select class="form-select border-light bg-light" name="teacher_id">
                                <option>الشيخ أحمد عامر</option>
                                <option>أ. سارة محمد</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">سعة الفصل</label>
                            <input type="number" class="form-control border-light bg-light" name="limit" value="10">
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">التاريخ</label>
                            <input type="date" class="form-control border-light bg-light" name="date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">البداية</label>
                            <input type="time" class="form-control border-light bg-light" name="start_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">النهاية</label>
                            <input type="time" class="form-control border-light bg-light" name="end_time" required>
                        </div>
                    </div>

                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 d-flex align-items-center mt-4 mb-0 rounded-3">
                        <i class="fa-solid fa-bolt text-primary me-3 fs-5"></i>
                        <div class="small text-primary">
                            <strong>نظام Agora:</strong> سيتم إنشاء رابط الحصة وتفعيله للمعلم والطلاب تلقائياً.
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
    function copyLink(channelId) {
        // Mock link generation
        const fullLink = window.location.origin + '/classroom/join/' + channelId;
        navigator.clipboard.writeText(fullLink).then(() => {
            alert('تم نسخ رابط الدعوة: ' + fullLink);
        });
    }
</script>
@endsection
