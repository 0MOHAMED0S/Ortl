@extends('dashboard.layouts.master')

@section('styles')
<style>
    :root {
        --brand-primary: #0d9488;
        --brand-secondary: #1a4d2e;
        --bg-color: #f3f4f6;
        --card-bg: #ffffff;
    }

    .dashboard-container {
        padding: 20px;
        background-color: var(--bg-color);
        min-height: 100vh;
    }

    .section-title {
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.25rem;
    }

    .section-title i {
        color: var(--brand-primary);
        background: #ccfbf1;
        padding: 8px;
        border-radius: 10px;
        font-size: 1rem;
    }

    /* --- CSS Grid for Cards --- */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    /* --- Card Design --- */
    .stat-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        border: 1px solid #f3f4f6;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-7px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Subtle background accent */
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0; width: 100px; height: 100px;
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(255,255,255,0) 100%);
        border-bottom-left-radius: 100px;
        z-index: 0;
    }

    .stat-info {
        position: relative;
        z-index: 1;
    }

    .stat-title {
        color: #6b7280;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .stat-value {
        color: #111827;
        font-size: 2rem;
        font-weight: 900;
        line-height: 1;
        font-family: 'Courier New', Courier, monospace; /* للأرقام */
        direction: ltr;
        display: inline-block;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        position: relative;
        z-index: 1;
    }

    /* --- Custom Colors for Cards --- */
    .icon-blue { background: #eff6ff; color: #3b82f6; }
    .icon-green { background: #f0fdf4; color: #16a34a; }
    .icon-teal { background: #f0fdfa; color: #0d9488; }
    .icon-orange { background: #fff7ed; color: #d97706; }
    .icon-purple { background: #faf5ff; color: #9333ea; }
    .icon-red { background: #fef2f2; color: #ef4444; }

    /* --- Special Highlight Card (Revenue) --- */
    .card-highlight {
        background: linear-gradient(135deg, var(--brand-secondary), var(--brand-primary));
        color: white;
    }
    .card-highlight .stat-title, .card-highlight .stat-value {
        color: white;
    }
    .card-highlight .stat-icon {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    .card-highlight::before { display: none; }

</style>
@endsection

@section('title')
    <h4 class="m-0 fw-bold">نظرة عامة</h4>
@endsection

@section('content')
<div class="dashboard-container">

    <h3 class="section-title"><i class="fa-solid fa-wallet"></i> الأداء المالي والمبيعات</h3>
    <div class="dashboard-grid">
        <div class="stat-card card-highlight">
            <div class="stat-info">
                <div class="stat-title text-white-50">إجمالي الإيرادات</div>
                <div class="stat-value">{{ number_format($stats['total_revenue'], 2) }} ج.م</div>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">الباقات المباعة</div>
                <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
            </div>
            <div class="stat-icon icon-teal">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">طلبات السحب المعلقة</div>
                <div class="stat-value">{{ number_format($stats['pending_withdrawals']) }}</div>
            </div>
            <div class="stat-icon icon-orange">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">بطاقات الهدايا المدفوعة</div>
                <div class="stat-value">{{ number_format($stats['total_gifts']) }}</div>
            </div>
            <div class="stat-icon icon-purple">
                <i class="fa-solid fa-gift"></i>
            </div>
        </div>
    </div>

    <h3 class="section-title"><i class="fa-solid fa-headset"></i> الجلسات والمكالمات</h3>
    <div class="dashboard-grid">
        <div class="stat-card border border-danger border-opacity-25">
            <div class="stat-info">
                <div class="stat-title">مكالمات جارية الآن</div>
                <div class="stat-value text-danger">{{ number_format($stats['live_calls']) }}</div>
            </div>
            <div class="stat-icon icon-red shadow-sm">
                <i class="fa-solid fa-phone-volume fa-beat"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">إجمالي المكالمات (فوري)</div>
                <div class="stat-value">{{ number_format($stats['total_calls']) }}</div>
            </div>
            <div class="stat-icon icon-blue">
                <i class="fa-solid fa-phone"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">المواعيد المجدولة (القادمة)</div>
                <div class="stat-value">{{ number_format($stats['scheduled_bookings']) }}</div>
            </div>
            <div class="stat-icon icon-green">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <h3 class="section-title"><i class="fa-solid fa-users"></i> المستخدمين والمحتوى</h3>
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">إجمالي الطلاب</div>
                <div class="stat-value">{{ number_format($stats['total_students']) }}</div>
            </div>
            <div class="stat-icon icon-blue">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">إجمالي المعلمين</div>
                <div class="stat-value">{{ number_format($stats['total_teachers']) }}</div>
            </div>
            <div class="stat-icon icon-teal">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">طلبات التوظيف (بانتظار المراجعة)</div>
                <div class="stat-value">{{ number_format($stats['pending_applications']) }}</div>
            </div>
            <div class="stat-icon icon-orange">
                <i class="fa-solid fa-file-signature"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-title">الباقات المعروضة للبيع</div>
                <div class="stat-value">{{ number_format($stats['active_packages']) }}</div>
            </div>
            <div class="stat-icon icon-purple">
                <i class="fa-solid fa-box-open"></i>
            </div>
        </div>
    </div>

</div>
@endsection
