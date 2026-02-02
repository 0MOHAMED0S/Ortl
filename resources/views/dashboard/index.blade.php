@extends('dashboard.layouts.master')
@section('styles')
<link rel="stylesheet" href="{{ asset('dashboard/css/index.css') }}">
<style>
    
</style>
@endsection
@section('content')
    <div class="container-fluid p-4">

            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card c-primary">
                        <div>
                            <h3 class="fw-bold m-0">1,250</h3><small class="text-muted">طالب نشط</small>
                        </div>
                        <div class="icon-box bg-green"><i class="fa-solid fa-users"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card c-gold">
                        <div>
                            <h3 class="fw-bold m-0">45</h3><small class="text-muted">طلب معلم</small>
                        </div>
                        <div class="icon-box bg-gold"><i class="fa-solid fa-chalkboard-user"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card c-primary">
                        <div>
                            <h3 class="fw-bold m-0">320</h3><small class="text-muted">اشتراك جديد</small>
                        </div>
                        <div class="icon-box bg-green"><i class="fa-solid fa-cart-shopping"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card c-gold">
                        <div>
                            <h3 class="fw-bold m-0">12K</h3><small class="text-muted">أرباح الشهر</small>
                        </div>
                        <div class="icon-box bg-gold"><i class="fa-solid fa-sack-dollar"></i></div>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3 text-dark">ملخص الباقات النشطة</h6>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="pkg-summary-card pkg-basic">
                        <div class="pkg-top">
                            <div class="pkg-icon-sm"><i class="fa-solid fa-book-open"></i></div>
                            <span class="pkg-price-sm">$15</span>
                        </div>
                        <h5 class="pkg-name-sm">باقة التلاوة</h5>
                        <div class="progress" style="height: 4px; margin-bottom: 8px;">
                            <div class="progress-bar bg-success" style="width: 45%"></div>
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
                        <div class="progress" style="height: 4px; margin-bottom: 8px;">
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
                        <div class="progress" style="height: 4px; margin-bottom: 8px;">
                            <div class="progress-bar bg-primary" style="width: 25%"></div>
                        </div>
                        <div class="pkg-subscribers"><strong>80</strong> مشترك نشط</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card-box">
                        <h6 class="fw-bold mb-3 text-dark">تحليل الإيرادات</h6>
                        <canvas id="mainChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-box">
                        <h6 class="fw-bold mb-3 text-dark">نسبة المسارات</h6>
                        <canvas id="doughnutChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold m-0 text-dark">آخر طلبات الانضمام</h6>
                            <a href="teachers.html" class="btn btn-sm btn-outline-success">عرض الكل</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table custom-table">
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
                                        <td class="fw-bold">محمد أحمد</td>
                                        <td>تصحيح التلاوة</td>
                                        <td>مصر</td>
                                        <td><span class="status-badge bg-pending">قيد المراجعة</span></td>
                                        <td><button class="btn btn-sm btn-light"
                                                onclick="openRequestModal('محمد أحمد', 'تصحيح التلاوة', 'مصر')"><i
                                                    class="fa-solid fa-eye text-primary"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">سارة علي</td>
                                        <td>تحفيظ أطفال</td>
                                        <td>السعودية</td>
                                        <td><span class="status-badge bg-success-light">مقبول</span></td>
                                        <td><button class="btn btn-sm btn-light"
                                                onclick="openRequestModal('سارة علي', 'تحفيظ أطفال', 'السعودية')"><i
                                                    class="fa-solid fa-eye text-primary"></i></button></td>
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
    <div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">تفاصيل الطلب</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <img src="https://placehold.co/80/eee/999?text=U" class="rounded-circle mb-2" id="reqImg">
                        <h5 class="fw-bold" id="reqName">اسم المعلم</h5>
                        <p class="text-muted small">متقدم جديد</p>
                    </div>
                    <div class="detail-row"><span class="detail-label">المسار المختار:</span> <span class="detail-val"
                            id="reqTrack">---</span></div>
                    <div class="detail-row"><span class="detail-label">الدولة:</span> <span class="detail-val"
                            id="reqCountry">---</span></div>
                    <div class="detail-row"><span class="detail-label">الخبرة:</span> <span class="detail-val">5 سنوات
                            (أونلاين)</span></div>
                    <div class="detail-row"><span class="detail-label">المرفقات:</span> <a href="#"
                            class="text-primary text-decoration-none">السيرة الذاتية.pdf</a></div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-danger me-auto">رفض</button>
                    <button type="button" class="btn btn-success">قبول الطلب</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        // 4. Charts Config
        const ctx1 = document.getElementById('mainChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'الطلاب',
                    data: [12, 19, 3, 5, 2, 30],
                    borderColor: '#1a4d2e',
                    backgroundColor: 'rgba(26, 77, 46, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        const ctx2 = document.getElementById('doughnutChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['تصحيح', 'حفظ', 'إجازة'],
                datasets: [{
                    data: [300, 50, 100],
                    backgroundColor: ['#1a4d2e', '#c49a46', '#e8f5e9'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%'
            }
        });
    </script>
@endsection
