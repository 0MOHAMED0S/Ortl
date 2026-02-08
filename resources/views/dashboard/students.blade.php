@extends('dashboard.layouts.master')

@section('styles')
    {{-- reusing the same css or adding specific student styles --}}
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* specific tweaks for table view */
        .student-avatar {
            width: 40px;
            height: 40px;
            object-fit: cover;
            background-color: #f0f2f5;
            color: #555;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .table-card {
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #eee;
        }
        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .action-btn:hover {
            background-color: #f0f2f5;
            transform: translateY(-2px);
        }
    </style>
@endsection

@section('title')
<h5 class="m-0 fw-bold">إدارة الطلاب</h5>
@endsection

@section('content')

{{-- FAKE DATA GENERATION (For Display Purposes) --}}
@php
    $students = [
        (object)[
            'id' => 1,
            'name' => 'أحمد محمد علي',
            'email' => 'ahmed@example.com',
            'phone' => '01012345678',
            'track' => 'التجويد العملي',
            'minutes' => 120,
            'status' => 'active',
            'avatar_initials' => 'أ'
        ],
        (object)[
            'id' => 2,
            'name' => 'سارة محمود حسن',
            'email' => 'sara@example.com',
            'phone' => '01123456789',
            'track' => 'حفظ القرآن',
            'minutes' => 450,
            'status' => 'active',
            'avatar_initials' => 'س'
        ],
        (object)[
            'id' => 3,
            'name' => 'خالد عبد الرحمن',
            'email' => 'khaled@example.com',
            'phone' => '01234567890',
            'track' => 'القراءات',
            'minutes' => 0,
            'status' => 'inactive',
            'avatar_initials' => 'خ'
        ],
        (object)[
            'id' => 4,
            'name' => 'منى السيد',
            'email' => 'mona@example.com',
            'phone' => '01555555555',
            'track' => 'التجويد النظري',
            'minutes' => 60,
            'status' => 'active',
            'avatar_initials' => 'م'
        ],
    ];
@endphp

<div class="container-fluid p-4">

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Bar --}}
    <div class="stats-bar mb-4">
        <div class="mini-stat active">
            <div class="stat-icon bg-white text-primary"><i class="fa-solid fa-users"></i></div>
            <div><h4 class="m-0 fw-bold">1,250</h4><small class="text-muted">إجمالي الطلاب</small></div>
        </div>
        <div class="mini-stat">
            <div class="stat-icon bg-white text-success"><i class="fa-solid fa-user-check"></i></div>
            <div><h4 class="m-0 fw-bold">980</h4><small class="text-muted">نشط حالياً</small></div>
        </div>
        <div class="mini-stat">
            <div class="stat-icon bg-white text-warning"><i class="fa-solid fa-clock"></i></div>
            <div><h4 class="m-0 fw-bold">5,400</h4><small class="text-muted">دقيقة تعلم</small></div>
        </div>
    </div>

    {{-- Toolbar & Search --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0 text-muted">قائمة الطلاب</h5>
        <button class="btn btn-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fa-solid fa-plus me-2"></i> إضافة طالب
        </button>
    </div>

    {{-- Students Table --}}
    <div class="card table-card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="p-3 text-muted small fw-bold border-0">الطالب</th>
                            <th class="p-3 text-muted small fw-bold border-0">معلومات الاتصال</th>
                            <th class="p-3 text-muted small fw-bold border-0">المسار الحالي</th>
                            <th class="p-3 text-muted small fw-bold border-0 text-center">رصيد الدقائق</th>
                            <th class="p-3 text-muted small fw-bold border-0 text-center">الحالة</th>
                            <th class="p-3 text-muted small fw-bold border-0 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            {{-- Name & Avatar --}}
                            <td class="p-3 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <div class="student-avatar rounded-circle me-3">
                                        {{ $student->avatar_initials }}
                                    </div>
                                    <div>
                                        <h6 class="m-0 fw-bold text-dark">{{ $student->name }}</h6>
                                        <small class="text-muted">ID: #{{ 202400 + $student->id }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact Info --}}
                            <td class="p-3 border-bottom-0">
                                <div class="d-flex flex-column">
                                    <small class="mb-1"><i class="fa-solid fa-envelope text-muted me-2"></i>{{ $student->email }}</small>
                                    <small><i class="fa-solid fa-phone text-muted me-2"></i>{{ $student->phone }}</small>
                                </div>
                            </td>

                            {{-- Track --}}
                            <td class="p-3 border-bottom-0">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                    {{ $student->track }}
                                </span>
                            </td>

                            {{-- Minutes --}}
                            <td class="p-3 border-bottom-0 text-center">
                                <span class="fw-bold {{ $student->minutes > 0 ? 'text-dark' : 'text-danger' }}">
                                    {{ $student->minutes }}
                                </span>
                                <small class="text-muted d-block" style="font-size: 10px">دقيقة</small>
                            </td>

                            {{-- Status --}}
                            <td class="p-3 border-bottom-0 text-center">
                                @if($student->status == 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-circle small me-1"></i> نشط</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="fa-solid fa-circle small me-1"></i> متوقف</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="p-3 border-bottom-0 text-end">
                                {{-- Edit Button --}}
                                <button class="action-btn text-muted" data-bs-toggle="modal" data-bs-target="#editStudent{{ $student->id }}" title="تعديل">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                {{-- Add Minutes Button --}}
                                <button class="action-btn text-warning" data-bs-toggle="modal" data-bs-target="#addMinutes{{ $student->id }}" title="إضافة رصيد">
                                    <i class="fa-solid fa-clock"></i>
                                </button>

                                {{-- Delete Button --}}
                                <button class="action-btn text-danger" data-bs-toggle="modal" data-bs-target="#deleteStudent{{ $student->id }}" title="حذف">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- ================= MODALS INSIDE LOOP ================= --}}

                        {{-- 1. EDIT MODAL --}}
                        <div class="modal fade" id="editStudent{{ $student->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="#" method="POST"> @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">تعديل بيانات الطالب</h5>
                                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label small text-muted fw-bold">الاسم</label>
                                                <input type="text" class="form-control" name="name" value="{{ $student->name }}">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small text-muted fw-bold">البريد الإلكتروني</label>
                                                    <input type="email" class="form-control" name="email" value="{{ $student->email }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small text-muted fw-bold">رقم الهاتف</label>
                                                    <input type="text" class="form-control" name="phone" value="{{ $student->phone }}">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted fw-bold">حالة الحساب</label>
                                                <select class="form-select" name="status">
                                                    <option value="active" {{ $student->status == 'active' ? 'selected' : '' }}>نشط</option>
                                                    <option value="inactive" {{ $student->status == 'inactive' ? 'selected' : '' }}>متوقف</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- 2. ADD MINUTES MODAL --}}
                        <div class="modal fade" id="addMinutes{{ $student->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-sm modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="#" method="POST">
                                        @csrf
                                        <div class="modal-body p-4 text-center">
                                            <div class="mb-3">
                                                <i class="fa-solid fa-clock text-warning display-4"></i>
                                            </div>
                                            <h5 class="fw-bold mb-3">إضافة رصيد دقائق</h5>
                                            <p class="text-muted small">الطالب: {{ $student->name }}</p>
                                            <div class="input-group mb-3">
                                                <input type="number" class="form-control text-center" name="minutes" placeholder="0" min="1" required>
                                                <span class="input-group-text">دقيقة</span>
                                            </div>
                                            <button type="submit" class="btn btn-warning w-100 fw-bold">إضافة الرصيد</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- 3. DELETE MODAL --}}
                        <div class="modal fade" id="deleteStudent{{ $student->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="#" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-body p-4 text-center">
                                            <div class="mb-3">
                                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                    <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                                                </div>
                                            </div>
                                            <h5 class="fw-bold">حذف الطالب؟</h5>
                                            <p class="text-muted">هل أنت متأكد من حذف الطالب <strong>{{ $student->name }}</strong>؟ لا يمكن التراجع عن هذا الإجراء.</p>
                                            <div class="d-flex justify-content-center gap-2 mt-4">
                                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">تراجع</button>
                                                <button type="submit" class="btn btn-danger px-4">نعم، احذف</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination (Static for view) --}}
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">عرض 4 من أصل 1250 طالب</small>
                <nav>
                    <ul class="pagination pagination-sm m-0">
                        <li class="page-item disabled"><a class="page-link" href="#">السابق</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">التالي</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
{{-- MAIN ADD STUDENT MODAL --}}
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="#">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">تسجيل طالب جديد</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">اسم الطالب <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="الاسم رباعي" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">رقم الهاتف</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">تحديد المسار</label>
                        <select class="form-select" name="track_id">
                            <option selected disabled>اختر المسار...</option>
                            <option value="1">التجويد العملي</option>
                            <option value="2">حفظ القرآن</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">كلمة المرور</label>
                        <input type="password" class="form-control" name="password" placeholder="********">
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
