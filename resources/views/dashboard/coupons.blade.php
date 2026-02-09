@extends('dashboard.layouts.master')

@section('styles')
    <style>
        /* Professional Coupon Card Styles */
        .coupon-ticket {
            background: #fff;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border: 1px solid #f0f2f5;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .coupon-ticket:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
            border-color: #6366f1;
        }

        /* Top Section: Discount Value */
        .coupon-top {
            background: linear-gradient(135deg, #fdfbf7 0%, #fff 100%);
            padding: 25px 20px 15px;
            text-align: center;
            position: relative;
            border-bottom: 2px dashed #e5e7eb;
        }

        .discount-circle {
            width: 80px;
            height: 80px;
            background: #e0e7ff;
            color: #4338ca;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            font-weight: 900;
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.15);
        }

        .discount-circle small {
            font-size: 1rem;
            margin-right: 2px;
            font-weight: 700;
        }

        /* Ticket Cutouts */
        .coupon-ticket::before, .coupon-ticket::after {
            content: "";
            position: absolute;
            top: 130px;
            width: 24px;
            height: 24px;
            background-color: #f4f6f9; /* Dashboard bg color */
            border-radius: 50%;
            z-index: 2;
        }
        .coupon-ticket::before { left: -12px; }
        .coupon-ticket::after { right: -12px; }

        /* Code Box */
        .coupon-code-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 8px 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #334155;
            letter-spacing: 1px;
            font-size: 1.1rem;
            display: inline-block;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .coupon-body {
            padding: 25px 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .usage-label {
            font-size: 0.8rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .coupon-footer {
            padding: 15px 20px;
            background: #fff;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .coupon-ticket.inactive {
            opacity: 0.7;
            filter: grayscale(1);
        }
        .coupon-ticket.inactive:hover {
            filter: grayscale(0);
            opacity: 1;
        }
    </style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100">
    <h5 class="m-0 fw-bold">إدارة الكوبونات (الخصومات)</h5>
</div>
@endsection

@section('content')
<div class="container-fluid p-4">

    {{-- Alert Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Dynamic Stats Bar --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-indigo bg-opacity-10 text-indigo rounded p-3 me-3" style="color: #4338ca; background-color: #e0e7ff;">
                        <i class="fa-solid fa-ticket fs-4"></i>
                    </div>
                    <div>
                        <h4 class="m-0 fw-bold">{{ $coupons->where('status', 'active')->count() }}</h4>
                        <small class="text-muted">كوبونات نشطة</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                        <i class="fa-solid fa-check-double fs-4"></i>
                    </div>
                    <div>
                        <h4 class="m-0 fw-bold">{{ $coupons->sum('used') }}</h4>
                        <small class="text-muted">إجمالي عمليات الاستخدام</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                        <i class="fa-solid fa-percent fs-4"></i>
                    </div>
                    <div>
                        <h4 class="m-0 fw-bold">{{ round($coupons->avg('percent'), 1) }}%</h4>
                        <small class="text-muted">متوسط نسبة الخصم</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach ($coupons as $coupon)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="coupon-ticket {{ $coupon->status == 'inactive' ? 'inactive' : '' }}">

                {{-- Top Section: Value --}}
                <div class="coupon-top">
                    <div class="discount-circle">
                        {{ $coupon->percent }}<small>%</small>
                    </div>
                    {{-- Copy Functionality --}}
                    <div class="coupon-code-box" onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); alert('تم نسخ الكود: {{ $coupon->code }}')" title="انقر للنسخ">
                        {{ $coupon->code }}
                    </div>
                </div>

                {{-- Middle Section: Stats --}}
                <div class="coupon-body">
                    <div>
                        <div class="usage-label">
                            <span>مرات الاستخدام</span>
                            <span class="fw-bold {{ $coupon->used >= $coupon->limit ? 'text-danger' : 'text-dark' }}">
                                {{ $coupon->used }} / {{ $coupon->limit }}
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            @php
                                $percent = $coupon->limit > 0 ? ($coupon->used / $coupon->limit) * 100 : 0;
                                $color = $percent >= 100 ? 'bg-danger' : ($percent > 70 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    <div class="mt-3 small text-muted d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fa-regular fa-clock me-1"></i>
                            ينتهي: <span class="fw-bold text-dark">{{ $coupon->expiry_date->format('Y-m-d') }}</span>
                        </div>
                        @if($coupon->expiry_date->isPast())
                            <span class="badge bg-danger">منتهي</span>
                        @endif
                    </div>
                </div>

                {{-- Footer: Actions --}}
                <div class="coupon-footer">
                    {{-- Edit Trigger --}}
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light border text-muted" data-bs-toggle="modal" data-bs-target="#editCoupon{{ $coupon->id }}">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        {{-- Delete Trigger --}}
                        <button class="btn btn-sm btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#deleteCoupon{{ $coupon->id }}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>

                    {{-- Toggle Status Form --}}
                    <form action="{{ route('coupons.update', $coupon->id) }}" method="POST" class="d-flex align-items-center m-0">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_toggle" value="1">
                        <div class="form-check form-switch m-0" title="تفعيل / تعطيل">
                            <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" {{ $coupon->status == 'active' ? 'checked' : '' }}>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div class="modal fade" id="editCoupon{{ $coupon->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('coupons.update', $coupon->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">تعديل الكوبون</h5>
                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">كود الخصم</label>
                                <input type="text" class="form-control text-uppercase fw-bold" name="code" value="{{ $coupon->code }}" required>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold small text-muted">نسبة الخصم (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control text-center fw-bold" name="percent" value="{{ $coupon->percent }}" min="1" max="100" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold small text-muted">الحد الأقصى</label>
                                    <input type="number" class="form-control" name="limit" value="{{ $coupon->limit }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">تاريخ الانتهاء</label>
                                <input type="date" class="form-control" name="expiry_date" value="{{ $coupon->expiry_date->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary fw-bold px-4">حفظ التغييرات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- DELETE MODAL --}}
        <div class="modal fade" id="deleteCoupon{{ $coupon->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('coupons.destroy', $coupon->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body p-4 text-center">
                            <div class="mb-3 text-danger"><i class="fa-solid fa-trash-can fa-3x"></i></div>
                            <h5 class="fw-bold">حذف الكوبون؟</h5>
                            <p class="text-muted">هل أنت متأكد من حذف الكوبون <strong>{{ $coupon->code }}</strong>؟ لا يمكن التراجع عن هذا الإجراء.</p>
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">تراجع</button>
                                <button type="submit" class="btn btn-danger px-4">نعم، حذف</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Add New Coupon Card Trigger --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="track-card add-card h-100 d-flex flex-column align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#addCouponModal" style="border: 2px dashed #ccc; background: #fafafa; cursor: pointer; min-height: 250px; transition: 0.3s;">
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-plus text-primary fs-3"></i>
                </div>
                <h5 class="fw-bold text-muted">إضافة كوبون جديد</h5>
            </div>
        </div>

    </div>
</div>
@endsection

@section('modals')
{{-- ADD COUPON MODAL --}}
<div class="modal fade" id="addCouponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('coupons.store') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">إنشاء كوبون خصم جديد</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    {{-- Code Input --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">كود الخصم <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-ticket text-muted"></i></span>
                            <input type="text" class="form-control text-uppercase fw-bold" name="code" id="newCodeInput" placeholder="مثال: SALE2024" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="generateCode()"><i class="fa-solid fa-wand-magic-sparkles"></i> توليد</button>
                        </div>
                        <small class="text-muted">الأحرف الإنجليزية والأرقام فقط.</small>
                    </div>

                    {{-- Percentage & Limit --}}
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small text-muted">نسبة الخصم <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control text-center fw-bold" name="percent" placeholder="20" min="1" max="100" required>
                                <span class="input-group-text bg-light fw-bold">%</span>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small text-muted">الحد الأقصى للعدد</label>
                            <input type="number" class="form-control" name="limit" placeholder="مثال: 100" min="1" required>
                        </div>
                    </div>

                    {{-- Expiry Date --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">تاريخ الانتهاء <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="expiry_date" required>
                    </div>

                    <div class="form-check bg-light p-3 rounded border">
                        <input class="form-check-input" type="checkbox" name="is_active" checked id="activeCheck">
                        <label class="form-check-label fw-bold small" for="activeCheck">
                            تفعيل الكوبون فوراً بعد الإنشاء
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">حفظ الكوبون</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function generateCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('newCodeInput').value = result;
    }
</script>

@endsection
