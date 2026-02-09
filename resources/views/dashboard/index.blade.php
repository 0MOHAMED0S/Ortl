@extends('dashboard.layouts.master')
@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/css/loader.css') }}">

    <style>

        /* --- Stats Cards --- */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid transparent;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        }
        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .c-primary { border-left: 4px solid var(--primary-dark); }
        .c-gold { border-left: 4px solid var(--gold-main); }

        .bg-green { background: #e8f5e9; color: var(--primary-dark); }
        .bg-gold { background: #fff8e1; color: var(--gold-main); }


        /* --- Package Cards --- */
        .pkg-summary-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
            height: 100%;
        }
        .pkg-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .pkg-icon-sm { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

        .pkg-basic .pkg-icon-sm { background: #e3f2fd; color: #0288d1; }
        .pkg-gold .pkg-icon-sm { background: #fff8e1; color: #ffa000; }
        .pkg-vip .pkg-icon-sm { background: #f3e5f5; color: #7b1fa2; }

        .pkg-price-sm { font-weight: 800; font-size: 1.1rem; color: #333; }
        .pkg-name-sm { font-weight: 700; font-size: 0.95rem; margin-bottom: 10px; color: #555; }
        .pkg-subscribers { font-size: 0.8rem; color: #888; margin-top: 5px; }

        /* --- Chart & Table Cards --- */
        .card-box {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
            height: 100%;
            position: relative; /* Important for Chart.js responsiveness */
        }

        /* --- Table Styling --- */
        .custom-table th { background-color: #f8f9fa; color: #666; font-weight: 700; font-size: 0.85rem; border-bottom: 1px solid #eee; }
        .custom-table td { vertical-align: middle; font-size: 0.9rem; color: #333; }
        .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
        .bg-pending { background: #fff3cd; color: #ffc107; }
        .bg-success-light { background: #d1e7dd; color: #198754; }

        /* --- Detail Rows in Modal --- */
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #eee; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; font-size: 0.9rem; }
        .detail-val { font-weight: 700; color: #333; font-size: 0.95rem; }
    </style>
@endsection

@section('title')
<h5 class="m-0 fw-bold">لوحة القيادة</h5>
@endsection

@section('content')

    <div class="container-fluid p-4">

        {{-- 1. Stats Row --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card c-primary">
                    <div>
                        <h3 class="fw-bold m-0 text-dark">1,250</h3>
                        <small class="text-muted">طالب نشط</small>
                    </div>
                    <div class="icon-box bg-green"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card c-gold">
                    <div>
                        <h3 class="fw-bold m-0 text-dark">45</h3>
                        <small class="text-muted">طلب معلم</small>
                    </div>
                    <div class="icon-box bg-gold"><i class="fa-solid fa-chalkboard-user"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card c-primary">
                    <div>
                        <h3 class="fw-bold m-0 text-dark">320</h3>
                        <small class="text-muted">اشتراك جديد</small>
                    </div>
                    <div class="icon-box bg-green"><i class="fa-solid fa-cart-shopping"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card c-gold">
                    <div>
                        <h3 class="fw-bold m-0 text-dark">12K</h3>
                        <small class="text-muted">أرباح الشهر</small>
                    </div>
                    <div class="icon-box bg-gold"><i class="fa-solid fa-sack-dollar"></i></div>
                </div>
            </div>
        </div>

        {{-- 2. Packages Summary --}}
        <h6 class="fw-bold mb-3 text-dark">ملخص الباقات النشطة</h6>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="pkg-summary-card pkg-basic">
                    <div class="pkg-top">
                        <div class="pkg-icon-sm"><i class="fa-solid fa-book-open"></i></div>
                        <span class="pkg-price-sm">$15</span>
                    </div>
                    <h5 class="pkg-name-sm">باقة التلاوة</h5>
                    <div class="progress" style="height: 6px; margin-bottom: 8px;">
                        <div class="progress-bar bg-info" style="width: 45%"></div>
                    </div>
                    <div class="pkg-subscribers"><strong>120</strong> مشترك نشط</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pkg-summary-card pkg-gold">
                    <div class="pkg-top">
                        <div class="pkg-icon-sm"><i class="fa-solid fa-crown"></i></div>
                        <span class="pkg-price-sm">$40</span>
                    </div>
                    <h5 class="pkg-name-sm">الباقة الذهبية</h5>
                    <div class="progress" style="height: 6px; margin-bottom: 8px;">
                        <div class="progress-bar bg-warning" style="width: 70%"></div>
                    </div>
                    <div class="pkg-subscribers"><strong>530</strong> مشترك نشط</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pkg-summary-card pkg-vip">
                    <div class="pkg-top">
                        <div class="pkg-icon-sm"><i class="fa-solid fa-gem"></i></div>
                        <span class="pkg-price-sm">$85</span>
                    </div>
                    <h5 class="pkg-name-sm">باقة الإتقان VIP</h5>
                    <div class="progress" style="height: 6px; margin-bottom: 8px;">
                        <div class="progress-bar bg-primary" style="width: 25%"></div>
                    </div>
                    <div class="pkg-subscribers"><strong>80</strong> مشترك نشط</div>
                </div>
            </div>
        </div>

        {{-- 3. Charts Row --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0 text-dark">تحليل نمو الطلاب</h6>
                        <select class="form-select form-select-sm w-auto border-0 bg-light">
                            <option>آخر 6 أشهر</option>
                            <option>آخر سنة</option>
                        </select>
                    </div>
                    {{-- Set a fixed height container for Chart.js responsiveness --}}
                    <div style="height: 300px; width: 100%;">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-box">
                    <h6 class="fw-bold mb-3 text-dark">توزيع المسارات</h6>
                    <div style="height: 250px; width: 100%; display: flex; justify-content: center;">
                         <canvas id="doughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Recent Requests Table --}}
        <div class="row">
            <div class="col-12">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0 text-dark">آخر طلبات الانضمام (معلمين)</h6>
                        <a href="{{ route('teachers.index') }}" class="btn btn-sm btn-outline-success">عرض الكل</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>المسار</th>
                                    <th>الدولة</th>
                                    <th>الحالة</th>
                                    <th>الإجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold"><img src="https://ui-avatars.com/api/?name=Mohamed&background=random" class="rounded-circle me-2" width="30"> محمد أحمد</td>
                                    <td>تصحيح التلاوة</td>
                                    <td>مصر</td>
                                    <td><span class="status-badge bg-pending">قيد المراجعة</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-light border" onclick="openRequestModal('محمد أحمد', 'تصحيح التلاوة', 'مصر')">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><img src="https://ui-avatars.com/api/?name=Sara&background=random" class="rounded-circle me-2" width="30"> سارة علي</td>
                                    <td>تحفيظ أطفال</td>
                                    <td>السعودية</td>
                                    <td><span class="status-badge bg-success-light">مقبول</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-light border" onclick="openRequestModal('سارة علي', 'تحفيظ أطفال', 'السعودية')">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('modals')
    {{-- Request Details Modal --}}
    <div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold">تفاصيل الطلب</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <img src="https://placehold.co/80/eee/999?text=U" class="rounded-circle mb-2 border p-1" id="reqImg" width="80" height="80">
                        <h5 class="fw-bold mb-1" id="reqName">اسم المعلم</h5>
                        <p class="text-muted small">متقدم جديد</p>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <div class="detail-row"><span class="detail-label">المسار المختار:</span> <span class="detail-val" id="reqTrack">---</span></div>
                        <div class="detail-row"><span class="detail-label">الدولة:</span> <span class="detail-val" id="reqCountry">---</span></div>
                        <div class="detail-row"><span class="detail-label">الخبرة:</span> <span class="detail-val">5 سنوات (أونلاين)</span></div>
                        <div class="detail-row border-0"><span class="detail-label">المرفقات:</span> <a href="#" class="text-primary text-decoration-none fw-bold"><i class="fa-solid fa-file-pdf me-1"></i> السيرة الذاتية.pdf</a></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 justify-content-between">
                    <button type="button" class="btn btn-outline-danger fw-bold px-4">رفض</button>
                    <button type="button" class="btn btn-success fw-bold px-4">قبول الطلب</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- 1. Load Chart.js from CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Modal Logic
        function openRequestModal(name, track, country) {
            document.getElementById('reqName').innerText = name;
            document.getElementById('reqTrack').innerText = track;
            document.getElementById('reqCountry').innerText = country;
            document.getElementById('reqImg').src = `https://ui-avatars.com/api/?name=${name}&background=random&size=128`;

            var myModal = new bootstrap.Modal(document.getElementById('requestModal'));
            myModal.show();
        }

        // Charts Configuration
        document.addEventListener("DOMContentLoaded", function() {

            // --- Line Chart (Main) ---
            const ctx1 = document.getElementById('mainChart');
            if (ctx1) {
                new Chart(ctx1.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                        datasets: [{
                            label: 'الطلاب الجدد',
                            data: [12, 19, 15, 25, 22, 30],
                            borderColor: '#2d8a74',
                            backgroundColor: 'rgba(45, 138, 116, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#2d8a74',
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // Vital for responsiveness
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f0f0f0' },
                                ticks: { font: { family: 'Cairo' } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Cairo' } }
                            }
                        }
                    }
                });
            }

            // --- Doughnut Chart ---
            const ctx2 = document.getElementById('doughnutChart');
            if (ctx2) {
                new Chart(ctx2.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['تصحيح تلاوة', 'حفظ', 'إجازة'],
                        datasets: [{
                            data: [300, 150, 100],
                            backgroundColor: ['#2d8a74', '#c49a46', '#e0f2f1'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: { family: 'Cairo', size: 12 }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
