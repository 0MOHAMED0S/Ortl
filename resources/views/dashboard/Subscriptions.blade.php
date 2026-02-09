@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* --- Existing Styles (Kept as is) --- */
        .main-card {
            border: 1px solid #f0f0f0;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.02);
            overflow: hidden;
        }
        .student-profile { display: flex; align-items: center; gap: 12px; }
        .avatar-box {
            width: 45px; height: 45px;
            border-radius: 12px;
            background: #f8f9fa;
            object-fit: cover;
            border: 1px solid #eee;
            flex-shrink: 0;
        }
        .sub-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-gold { background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%); color: #f59e0b; border: 1px solid #ffe082; }
        .badge-silver { background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%); color: #757575; border: 1px solid #bdbdbd; }
        .badge-free {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4338ca;
            border: 1px solid #a5b4fc;
            box-shadow: 0 2px 5px rgba(99, 102, 241, 0.15);
        }
        .badge-none { background: #f8f9fa; color: #9ca3af; border: 1px solid #e5e7eb; }
        .btn-action {
            width: 32px; height: 32px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 8px;
            color: #64748b;
            transition: all 0.2s;
        }
        .btn-action:hover { background: #f1f5f9; color: #0f172a; }
        .waiver-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 15px;
            transition: all 0.3s;
        }
        .waiver-box.active { background: #eff6ff; border-color: #3b82f6; }
        .waiver-box.active .form-check-label { color: #1e40af; }

        /* --- NEW PROFESSIONAL FILTER STYLES --- */
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-tabs {
            display: flex;
            background: #f8fafc;
            padding: 5px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            color: #64748b;
            cursor: pointer;
            border: none;
            background: transparent;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .filter-tab.active {
            background: #fff;
            color: #2d8a74;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .search-wrapper {
            position: relative;
            min-width: 280px;
        }

        .search-input {
            padding-right: 40px; /* Space for icon */
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
        .search-input:focus {
            border-color: #2d8a74;
            box-shadow: 0 0 0 3px rgba(45, 138, 116, 0.1);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        /* Responsive Fixes */
        @media (max-width: 768px) {
            .filter-container { flex-direction: column-reverse; align-items: stretch; }
            .search-wrapper { width: 100%; }
            .filter-tabs { width: 100%; justify-content: flex-start; }

            .col-md-3 { flex: 0 0 50%; max-width: 50%; margin-bottom: 15px; }
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .table th, .table td { white-space: nowrap; }
        }
        @media (max-width: 576px) { .col-md-3 { flex: 0 0 100%; max-width: 100%; } }
    </style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100">
    <div>
        <h5 class="m-0 fw-bold">الطلاب والاشتراكات</h5>
        <small class="text-muted">إدارة بيانات الطلاب وباقاتهم (المدفوعة والمجانية)</small>
    </div>
</div>
@endsection

@section('content')

{{-- FAKE DATA --}}
@php
    $students = [
        (object)['id' => 101, 'name' => 'أحمد محمد علي', 'email' => 'ahmed@example.com', 'phone' => '010xxxxxxx', 'avatar' => 'https://ui-avatars.com/api/?name=Ahmed+Ali&background=random', 'has_subscription' => true, 'plan_name' => 'الباقة الذهبية', 'plan_type' => 'gold', 'is_free' => false, 'end_date' => '2025-12-31', 'days_left' => 300],
        (object)['id' => 102, 'name' => 'يوسف محمود', 'email' => 'youssef@example.com', 'phone' => '011xxxxxxx', 'avatar' => 'https://ui-avatars.com/api/?name=Youssef+M&background=random', 'has_subscription' => true, 'plan_name' => 'منحة تفوق (مجانية)', 'plan_type' => 'free', 'is_free' => true, 'end_date' => '2024-06-30', 'days_left' => 120],
        (object)['id' => 103, 'name' => 'كريم عبد الله', 'email' => 'kareem@example.com', 'phone' => '012xxxxxxx', 'avatar' => 'https://ui-avatars.com/api/?name=Kareem+A&background=random', 'has_subscription' => false, 'plan_name' => null, 'plan_type' => null, 'is_free' => false, 'end_date' => null, 'days_left' => 0],
    ];
@endphp

<div class="container-fluid p-3 p-md-4">
    <div class="text-center mb-4">
        <button class="btn btn-primary fw-bold px-4 shadow-sm w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fa-solid fa-user-plus me-2"></i> تسجيل طالب جديد
        </button>
    </div>

    {{-- Stats Bar --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3 flex-shrink-0"><i class="fa-solid fa-users fs-4"></i></div>
                    <div><h5 class="fw-bold m-0">1,250</h5><small class="text-muted">إجمالي الطلاب</small></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-3 flex-shrink-0"><i class="fa-solid fa-crown fs-4"></i></div>
                    <div><h5 class="fw-bold m-0">850</h5><small class="text-muted">اشتراك مدفوع</small></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-indigo bg-opacity-10 text-indigo rounded p-3 flex-shrink-0" style="color: #4338ca; background: #e0e7ff;"><i class="fa-solid fa-gift fs-4"></i></div>
                    <div><h5 class="fw-bold m-0">45</h5><small class="text-muted">منحة مجانية</small></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded p-3 flex-shrink-0"><i class="fa-solid fa-user-slash fs-4"></i></div>
                    <div><h5 class="fw-bold m-0">355</h5><small class="text-muted">بدون اشتراك</small></div>
                </div>
            </div>
        </div>
    </div>

    {{-- PROFESSIONAL FILTER BAR --}}
    <div class="filter-container">
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterTable('all', this)">الكل</button>
            <button class="filter-tab" onclick="filterTable('paid', this)">مدفوع</button>
            <button class="filter-tab" onclick="filterTable('free', this)">منحة مجانية</button>
            <button class="filter-tab" onclick="filterTable('none', this)">غير مشترك</button>
        </div>

        <div class="d-flex gap-2 w-100 w-md-auto">
            <select class="form-select w-auto border-0 bg-white shadow-sm" id="planFilter" onchange="filterTableByPlan()">
                <option value="all">كل الباقات</option>
                <option value="gold">الباقة الذهبية</option>
                <option value="silver">الباقة الفضية</option>
            </select>

            <div class="search-wrapper flex-grow-1">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="searchInput" class="form-control search-input" placeholder="بحث باسم الطالب..." onkeyup="searchTable()">
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card main-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="p-3 text-muted small fw-bold border-0">الطالب</th>
                            <th class="p-3 text-muted small fw-bold border-0">معلومات الاتصال</th>
                            <th class="p-3 text-muted small fw-bold border-0">حالة الاشتراك</th>
                            <th class="p-3 text-muted small fw-bold border-0 text-center">الصلاحية</th>
                            <th class="p-3 text-muted small fw-bold border-0 text-end">إدارة</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        @foreach($students as $student)
                        {{-- Add data attributes for filtering --}}
                        <tr class="student-row"
                            data-status="{{ $student->is_free ? 'free' : ($student->has_subscription ? 'paid' : 'none') }}"
                            data-plan="{{ $student->plan_type ?? 'none' }}"
                            data-name="{{ $student->name }}">

                            {{-- Student Profile --}}
                            <td class="p-3">
                                <div class="student-profile">
                                    <img src="{{ $student->avatar }}" class="avatar-box">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $student->name }}</div>
                                        <small class="text-muted">ID: #{{ $student->id }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="p-3">
                                <div class="d-flex flex-column text-muted small">
                                    <span><i class="fa-solid fa-envelope me-2"></i>{{ $student->email }}</span>
                                    <span><i class="fa-solid fa-phone me-2"></i>{{ $student->phone }}</span>
                                </div>
                            </td>

                            {{-- Subscription Badge --}}
                            <td class="p-3">
                                @if(!$student->has_subscription)
                                    <span class="sub-badge badge-none">
                                        <i class="fa-solid fa-circle-xmark text-secondary"></i> غير مشترك
                                    </span>
                                @elseif($student->is_free)
                                    <span class="sub-badge badge-free">
                                        <i class="fa-solid fa-gift"></i> منحة مجانية
                                    </span>
                                @else
                                    @php $badgeClass = ($student->plan_type == 'gold') ? 'badge-gold' : 'badge-silver'; @endphp
                                    <span class="sub-badge {{ $badgeClass }}">
                                        <i class="fa-solid fa-crown"></i> {{ $student->plan_name }}
                                    </span>
                                @endif
                            </td>

                            {{-- Validity Date --}}
                            <td class="p-3 text-center">
                                @if($student->has_subscription)
                                    <div class="fw-bold text-dark">{{ $student->end_date }}</div>
                                    <small class="text-success fw-bold">{{ $student->days_left }} يوم متبقي</small>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="p-3 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle border-0" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                                        <li>
                                            <button class="dropdown-item rounded small py-2" data-bs-toggle="modal" data-bs-target="#manageSubModal{{ $student->id }}">
                                                @if($student->has_subscription)
                                                    <i class="fa-solid fa-rotate text-success me-2"></i>تجديد / تعديل الباقة
                                                @else
                                                    <i class="fa-solid fa-cart-plus text-primary me-2"></i>إضافة اشتراك جديد
                                                @endif
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item rounded small py-2">
                                                <i class="fa-solid fa-pen text-muted me-2"></i>تعديل بيانات الطالب
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        {{-- MODAL INCLUDE (Kept inside loop as requested) --}}
                        <div class="modal fade" id="manageSubModal{{ $student->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="#" method="POST">
                                        @csrf
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">إدارة اشتراك الطالب</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                                <img src="{{ $student->avatar }}" class="rounded-circle me-3" width="40" height="40">
                                                <div>
                                                    <div class="fw-bold">{{ $student->name }}</div>
                                                    <div class="text-muted small">
                                                        الحالة الحالية:
                                                        @if($student->is_free) <span class="text-primary fw-bold">منحة مجانية</span>
                                                        @elseif($student->has_subscription) <span class="text-warning fw-bold">{{ $student->plan_name }}</span>
                                                        @else <span class="text-secondary">غير مشترك</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="waiver-box mb-4" id="waiverBox{{ $student->id }}">
                                                <div class="form-check form-switch d-flex align-items-center justify-content-between p-0 m-0">
                                                    <div>
                                                        <label class="form-check-label fw-bold text-dark" for="freeToggle{{ $student->id }}">منح اشتراك مجاني (Scholarship)</label>
                                                        <div class="text-muted small" style="font-size: 11px;">تفعيل هذا الخيار يلغي الرسوم تماماً</div>
                                                    </div>
                                                    <input class="form-check-input ms-0 toggle-free" type="checkbox" id="freeToggle{{ $student->id }}"
                                                           data-target="{{ $student->id }}"
                                                           {{ $student->is_free ? 'checked' : '' }} style="cursor: pointer;">
                                                </div>
                                            </div>
                                            <div id="paidPlanSection{{ $student->id }}" class="{{ $student->is_free ? 'd-none' : '' }}">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-muted">اختر الباقة</label>
                                                    <select class="form-select">
                                                        <option value="gold" {{ $student->plan_type == 'gold' ? 'selected' : '' }}>الباقة الذهبية (1200 EGP)</option>
                                                        <option value="silver" {{ $student->plan_type == 'silver' ? 'selected' : '' }}>الباقة الفضية (600 EGP)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-muted">طريقة الدفع</label>
                                                    <select class="form-select">
                                                        <option value="cash">نقدي</option>
                                                        <option value="bank">تحويل بنكي</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-bold small text-muted">تاريخ البدء</label>
                                                    <input type="date" class="form-control" name="start_date" value="{{ date('Y-m-d') }}">
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label fw-bold small text-muted">تاريخ الانتهاء</label>
                                                    <input type="date" class="form-control" name="end_date" value="{{ $student->end_date ?? date('Y-m-d', strtotime('+1 month')) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 bg-light">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn {{ $student->is_free ? 'btn-indigo text-white' : 'btn-primary' }} fw-bold px-4" id="submitBtn{{ $student->id }}"
                                                    style="{{ $student->is_free ? 'background-color: #4338ca;' : '' }}">
                                                @if($student->is_free) <i class="fa-solid fa-gift me-2"></i>تأكيد المنحة @else <i class="fa-solid fa-check me-2"></i>حفظ الاشتراك @endif
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: ADD NEW STUDENT --}}
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="#" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">تسجيل طالب جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">اسم الطالب</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">البريد الإلكتروني</label>
                        <input type="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">كلمة المرور</label>
                        <input type="password" class="form-control" placeholder="********">
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success fw-bold">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Toggle Switches
        const toggles = document.querySelectorAll('.toggle-free');
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const id = this.getAttribute('data-target');
                const waiverBox = document.getElementById('waiverBox' + id);
                const paidSection = document.getElementById('paidPlanSection' + id);
                const btn = document.getElementById('submitBtn' + id);

                if(this.checked) {
                    waiverBox.classList.add('active');
                    paidSection.classList.add('d-none');
                    btn.classList.remove('btn-primary');
                    btn.style.backgroundColor = '#4338ca';
                    btn.style.color = '#fff';
                    btn.innerHTML = '<i class="fa-solid fa-gift me-2"></i>تأكيد المنحة';
                } else {
                    waiverBox.classList.remove('active');
                    paidSection.classList.remove('d-none');
                    btn.style.backgroundColor = '';
                    btn.style.color = '';
                    btn.classList.add('btn-primary');
                    btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>حفظ الاشتراك';
                }
            });
        });
    });

    // --- FILTER FUNCTIONS ---
    function filterTable(status, btn) {
        document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const rows = document.querySelectorAll('.student-row');
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.student-row');
        rows.forEach(row => {
            const text = row.dataset.name.toLowerCase();
            // Check if visible based on other filters (optional, here simplified)
            if (text.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function filterTableByPlan() {
        const plan = document.getElementById('planFilter').value;
        const rows = document.querySelectorAll('.student-row');
        rows.forEach(row => {
            if (plan === 'all' || row.dataset.plan === plan) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection
