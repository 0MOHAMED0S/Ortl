@extends('dashboard.layouts.master')

@section('styles')
    {{-- Select2 CSS for Professional Dropdowns --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-grad: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            --danger-grad: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
        }

        /* Glassmorphism Session Cards */
        .session-card {
            background: var(--glass-bg);
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            backdrop-filter: blur(10px);
        }

        .session-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            border-color: #3b82f6;
        }

        /* Status Badges */
        .badge-status {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 700;
        }

        .status-live-pulse {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            display: inline-block;
            margin-left: 5px;
            animation: pulse-dot 1.5s infinite;
        }

        /* Professional Date Design */
        .date-widget {
            background: #f1f5f9;
            border-radius: 15px;
            padding: 12px;
            min-width: 75px;
            border: 1px solid #e2e8f0;
        }

        /* Modern Teacher Avatar Stack */
        .teacher-profile-img {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            object-fit: cover;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        /* Professional Modal Styling */
        .modal-content-pro {
            border-radius: 24px;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .form-control-pro {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.2s;
        }

        .form-control-pro:focus {
            background-color: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Customizing Select2 to look modern */
        .select2-container--default .select2-selection--single {
            height: 50px !important;
            padding: 10px !important;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            background-color: #f8fafc !important;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid py-4">
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
@endif
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item small"><a href="#">الرئيسية</a></li>
                    <li class="breadcrumb-item small active">الفصول الذكية</li>
                </ol>
            </nav>
            <h3 class="fw-800 text-dark m-0">إدارة الجلسات الحية</h3>
        </div>
        <button class="btn btn-dark rounded-pill px-4 py-2 fw-bold shadow-lg transition-all" data-bs-toggle="modal" data-bs-target="#createSessionModal">
            <i class="fa-solid fa-video me-2"></i> جدولة جلسة جديدة
        </button>
    </div>

    <div class="row g-4">
        @forelse($sessions as $session)
            @php
                $now = now();
                $isLive = $now->between($session->start_at, $session->end_at);
                $isUpcoming = $now->lt($session->start_at);
            @endphp

            <div class="col-xl-6">
                <div class="session-card p-4">
                    <div class="d-flex align-items-start gap-4">
                        {{-- Date Widget --}}
                        <div class="date-widget text-center">
                            <h4 class="fw-800 m-0 color-primary">{{ $session->start_at->format('d') }}</h4>
                            <span class="small fw-bold text-muted text-uppercase">{{ $session->start_at->translatedFormat('M') }}</span>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    @if($isLive)
                                        <span class="badge bg-danger-subtle text-danger badge-status mb-2">
                                            <span class="status-live-pulse"></span> جاري الآن
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="badge bg-primary-subtle text-primary badge-status mb-2">قادمة قريباً</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary badge-status mb-2">مكتملة</span>
                                    @endif
                                    <h5 class="fw-bold text-dark m-0">{{ $session->title }}</h5>
                                </div>

                                {{-- Quick Actions Dropdown --}}
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item small" href="#"><i class="fa-solid fa-pen-to-square me-2"></i> تعديل</a></li>
                                        <li><a class="dropdown-item small text-danger" href="#"><i class="fa-solid fa-trash me-2"></i> حذف</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 mt-4">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($session->teacher->user->name) }}&background=6366f1&color=fff&bold=true" class="teacher-profile-img">
                                <div>
                                    <p class="text-muted small mb-0">المعلم المسؤول</p>
                                    <h6 class="fw-bold mb-0">{{ $session->teacher->user->name }}</h6>
                                </div>
                                <div class="ms-auto text-end">
                                    <p class="text-muted small mb-0"><i class="fa-regular fa-clock"></i> التوقيت</p>
                                    <h6 class="fw-bold mb-0 small">{{ $session->start_at->format('h:i A') }}</h6>
                                </div>
                            </div>

                            <hr class="my-4 opacity-50">

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-group d-flex">
                                        <span class="text-muted small"><i class="fa-solid fa-users me-1"></i> السعة: <strong>{{ $session->max_participants }}</strong></span>
                                    </div>
                                </div>

                                @if($isLive)
                                    <a href="#" class="btn btn-danger rounded-pill px-4 fw-bold">دخول الآن</a>
                                @else
                                    <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold small" onclick="copyLink('{{ $session->channel_name }}')">
                                        <i class="fa-solid fa-link me-1"></i> الرابط
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-3 opacity-25">
                <h5 class="text-muted">لا يوجد جلسات مجدولة حالياً</h5>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-pro">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="fw-800 m-0">جدولة حصة ذكية</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.recitations.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small">عنوان الجلسة</label>
                            <input type="text" name="title" class="form-control form-control-pro" placeholder="مثلاً: مراجعة أحكام التجويد - سورة البقرة" required>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold small">اختيار المعلم</label>
                            <select name="teacher_id" class="form-select select2-teacher" style="width: 100%" required>
                                <option></option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" data-image="https://ui-avatars.com/api/?name={{ urlencode($teacher->user->name) }}">
                                        {{ $teacher->user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold small">أقصى عدد للطلاب</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0" style="border-radius: 12px 0 0 12px"><i class="fa-solid fa-user-plus"></i></span>
                                <input type="number" name="max_participants" class="form-control form-control-pro border-start-0" value="10" required style="border-radius: 0 12px 12px 0">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">تاريخ ووقت البدء</label>
                            <input type="datetime-local" name="start_at" class="form-control form-control-pro" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">تاريخ ووقت الانتهاء</label>
                            <input type="datetime-local" name="end_at" class="form-control form-control-pro" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 mt-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">حفظ وتفعيل الجلسة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Professional Teacher Selector with Images
        function formatTeacher(teacher) {
            if (!teacher.id) return teacher.text;
            var img = $(teacher.element).data('image');
            var $teacher = $(
                '<span class="d-flex align-items-center gap-2"><img src="' + img + '" class="rounded-circle" width="25" /> ' + teacher.text + '</span>'
            );
            return $teacher;
        };

        $('.select2-teacher').select2({
            placeholder: "ابحث عن معلم...",
            templateResult: formatTeacher,
            templateSelection: formatTeacher,
            dropdownParent: $('#createSessionModal')
        });
    });

    function copyLink(channel) {
        const link = window.location.origin + '/live/' + channel;
        navigator.clipboard.writeText(link).then(() => {
             Swal.fire({
                icon: 'success',
                title: 'تم النسخ!',
                text: 'رابط الحصة أصبح جاهزاً للمشاركة',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
</script>
@endsection
