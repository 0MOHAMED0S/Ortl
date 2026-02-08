@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/packages.css') }}">
    <style>
        .ribbon { font-size: 0.8rem; font-weight: bold; }

        /* --- NEW STATS CARD STYLES --- */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border-bottom: 4px solid transparent;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); }

        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .stat-info h3 { font-weight: 800; margin: 0; color: #333; }
        .stat-info p { margin: 0; color: #777; font-size: 0.85rem; font-weight: 600; }

        /* Color Variants */
        .stat-purple { border-color: #6f42c1; }
        .stat-purple .stat-icon { background-color: #f3e8ff; color: #6f42c1; }

        .stat-green { border-color: #198754; }
        .stat-green .stat-icon { background-color: #d1e7dd; color: #198754; }

        .stat-red { border-color: #dc3545; }
        .stat-red .stat-icon { background-color: #f8d7da; color: #dc3545; }

        .stat-blue { border-color: #0d6efd; }
        .stat-blue .stat-icon { background-color: #cfe2ff; color: #0d6efd; }

        .subscriber-count { font-size: 0.85rem; color: #666; font-weight: 600; display: block; margin-top: 5px;}
    </style>
@endsection
@section('title')
<h5 class="m-0 fw-bold">باقات التلاوة  </h5>
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

    {{-- ================= STATS CARDS SECTION ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card stat-purple">
                <div class="stat-info">
                    <h3>{{ $totalPackages }}</h3>
                    <p>إجمالي الباقات</p>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card stat-green">
                <div class="stat-info">
                    <h3>{{ $activePackages }}</h3>
                    <p>باقات نشطة</p>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-check-circle"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card stat-red">
                <div class="stat-info">
                    <h3>{{ $inactivePackages }}</h3>
                    <p>باقات متوقفة</p>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-ban"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card stat-blue">
                <div class="stat-info">
                    <h3>{{ $totalSubscribers }}</h3>
                    <p>مشتركين حاليين</p>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
    </div>
    {{-- ================= END STATS ================= --}}


    <div class="row g-4">

        {{-- Loop through Packages --}}
        @foreach($packages as $package)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="pkg-card" style="{{ $package->discount > 0 ? 'border-color: var(--gold-main);' : '' }}">

                {{-- Dynamic Ribbon --}}
                @if($package->discount > 0)
                    <div class="ribbon">خصم {{ $package->discount }}%</div>
                @endif

                <div class="pkg-header">
                    <h5 class="pkg-name {{ $package->discount > 0 ? 'text-warning' : '' }}">{{ $package->name }}</h5>
                    <div class="pkg-price">
                        <span class="pkg-currency">$</span>{{ $package->price }}

                        @if($package->discount > 0)
                            @php
                                $originalPrice = $package->price / (1 - ($package->discount / 100));
                            @endphp
                            <small class="text-decoration-line-through text-muted ms-2 fs-6">${{ number_format($originalPrice, 0) }}</small>
                        @endif
                    </div>
                    <span class="pkg-duration">صلاحية {{ $package->validity_days }} يوم</span>
                </div>

                <p class="pkg-desc">{{ Str::limit($package->description, 60) }}</p>

                <ul class="pkg-features">
                    <li>
                        <i class="fa-regular fa-clock"></i>
                        <span><span class="feature-value">{{ $package->base_minutes }}</span> دقيقة أساسية</span>
                    </li>
                    <li>
                        @if($package->bonus_minutes > 0)
                            <i class="fa-solid fa-gift text-success"></i>
                            <span><span class="feature-value text-success">+{{ $package->bonus_minutes }}</span> دقيقة <span class="bonus-text">هدية</span></span>
                        @else
                            <i class="fa-solid fa-plus-circle text-muted"></i>
                            <span class="text-muted">لا يوجد دقائق إضافية</span>
                        @endif
                    </li>
                    {{-- NEW: FAKE SUBSCRIBERS IN CARD --}}
                    <li>
                        <i class="fa-solid fa-users text-primary"></i>
                        {{-- Generating a random fake number based on ID to act like persistent data --}}
                        <span><span class="feature-value">{{ ($package->id * 35) + 12 }}</span> طالب مشترك</span>
                    </li>
                </ul>

                <div class="pkg-footer">
                    {{-- Quick Status Toggle Form --}}
                    <form action="{{ route('packages.update', $package->id) }}" method="POST" class="m-0">
                        @csrf
                        @method('PUT')

                        {{-- Hidden inputs to keep other data safe --}}
                        <input type="hidden" name="name" value="{{ $package->name }}">
                        <input type="hidden" name="price" value="{{ $package->price }}">
                        <input type="hidden" name="discount" value="{{ $package->discount }}">
                        <input type="hidden" name="base_minutes" value="{{ $package->base_minutes }}">
                        <input type="hidden" name="bonus_minutes" value="{{ $package->bonus_minutes }}">
                        <input type="hidden" name="validity_days" value="{{ $package->validity_days }}">
                        <input type="hidden" name="description" value="{{ $package->description }}">

                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="inactive">
                            <input class="form-check-input status-switch" type="checkbox" name="status" value="active"
                                   onchange="this.form.submit()" {{ $package->status == 'active' ? 'checked' : '' }}>

                            <label class="form-check-label small fw-bold {{ $package->status == 'inactive' ? 'text-muted' : '' }}">
                                {{ $package->status == 'active' ? 'نشط' : 'غير نشط' }}
                            </label>
                        </div>
                    </form>

                    <div class="d-flex gap-2">
                        <div class="action-icon" data-bs-toggle="modal" data-bs-target="#editPackageModal{{ $package->id }}">
                            <i class="fa-solid fa-pen"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- EDIT MODAL FOR THIS PACKAGE --}}
        <div class="modal fade" id="editPackageModal{{ $package->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('packages.update', $package->id) }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">تعديل الباقة: {{ $package->name }}</h5>
                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">اسم الباقة</label>
                                    <input type="text" name="name" class="form-control" value="{{ $package->name }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">السعر (USD)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="price" class="form-control" value="{{ $package->price }}" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">نسبة الخصم</label>
                                    <div class="input-group">
                                        <input type="number" name="discount" class="form-control" value="{{ $package->discount }}" min="0" max="100">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">عدد الدقائق الأساسية</label>
                                    <div class="input-group">
                                        <input type="number" name="base_minutes" class="form-control" value="{{ $package->base_minutes }}" required>
                                        <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">دقائق إضافية (Bonus)</label>
                                    <div class="input-group">
                                        <input type="number" name="bonus_minutes" class="form-control" value="{{ $package->bonus_minutes }}">
                                        <span class="input-group-text"><i class="fa-solid fa-gift"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">مدة الصلاحية (بالأيام)</label>
                                    <div class="input-group">
                                        <input type="number" name="validity_days" class="form-control" value="{{ $package->validity_days }}" required>
                                        <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">وصف الباقة</label>
                                    <textarea name="description" class="form-control" rows="3">{{ $package->description }}</textarea>
                                </div>
                                <input type="hidden" name="status" value="{{ $package->status }}">
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">حفظ التعديلات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ADD NEW PACKAGE CARD --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="pkg-card add-card" data-bs-toggle="modal" data-bs-target="#createPackageModal">
                <i class="fa-solid fa-circle-plus add-icon"></i>
                <h5 class="fw-bold text-muted">إضافة باقة جديدة</h5>
            </div>
        </div>

    </div>
</div>
@endsection

@section('modals')
    {{-- CREATE MODAL (Same as before) --}}
    <div class="modal fade" id="createPackageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('packages.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold">إضافة باقة جديدة</h5>
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        {{-- Form Fields (Same as before) --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم الباقة <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="مثال: الباقة الذهبية" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">السعر (USD) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="price" class="form-control" placeholder="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">نسبة الخصم</label>
                                <div class="input-group">
                                    <input type="number" name="discount" class="form-control" value="0" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">عدد الدقائق الأساسية <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="base_minutes" class="form-control" placeholder="100" required>
                                    <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">دقائق إضافية (Bonus)</label>
                                <div class="input-group">
                                    <input type="number" name="bonus_minutes" class="form-control" value="0">
                                    <span class="input-group-text"><i class="fa-solid fa-gift"></i></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">مدة الصلاحية (بالأيام) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="validity_days" class="form-control" value="30" required>
                                    <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">وصف الباقة</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="وصف يظهر للطالب أسفل الباقة..."></textarea>
                            </div>
                            <input type="hidden" name="status" value="active">
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">حفظ الباقة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Logic
        const toggleBtn = document.querySelector('.menu-toggle-btn');
        const overlay = document.getElementById('overlay');
        const body = document.body;

        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => { body.classList.add('sidebar-active'); });
            overlay.addEventListener('click', () => { body.classList.remove('sidebar-active'); });
        }
    </script> --}}
@endsection
