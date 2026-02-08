@extends('dashboard.layouts.master')

@section('styles')
    {{-- Reusing the same CSS style for consistency, plus specific Ad styles --}}
    {{-- <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}"> --}}
    <style>
        .ad-card {
            background: #fff;
            border-radius: 16px;
            padding: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid transparent;
            display: flex;
            flex-direction: column;
        }
        .ad-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .ad-image-container {
            width: 100%;
            height: 160px;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
        }
        .ad-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ad-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #333;
        }
        .ad-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f0f0f0;
            padding-top: 12px;
        }
        .action-btn {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; border: 1px solid #eee;
            background: #fff; color: #666; transition: 0.2s;
        }
        .action-btn:hover { background: #f8f9fa; color: #333; }
        .action-btn.delete:hover { background: #fee2e2; color: #ef4444; border-color: #fee2e2; }

        /* Add Card Styling */
        .add-card {
            border: 2px dashed #ddd;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            min-height: 280px;
        }
        .add-card:hover { border-color: var(--primary-dark); background: #f0fdf4; }
        .add-icon { font-size: 3rem; color: #ccc; margin-bottom: 15px; transition: 0.3s; }
        .add-card:hover .add-icon { color: var(--primary-dark); }
    </style>
@endsection

@section('title')
<h5 class="m-0 fw-bold">إدارة الإعلانات</h5>
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

    {{-- Stats Bar --}}
    <div class="stats-bar mb-4 d-flex gap-3">
        <div class="mini-stat active p-3 bg-white rounded-3 shadow-sm d-flex align-items-center gap-3">
            <div class="stat-icon bg-success bg-opacity-10 text-success p-3 rounded-circle"><i class="fa-solid fa-check-circle fs-4"></i></div>
            <div><h4 class="m-0 fw-bold">{{ $ads->where('status', 'active')->count() }}</h4><small class="text-muted">إعلانات نشطة</small></div>
        </div>
        <div class="mini-stat p-3 bg-white rounded-3 shadow-sm d-flex align-items-center gap-3">
            <div class="stat-icon bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle"><i class="fa-solid fa-pause-circle fs-4"></i></div>
            <div><h4 class="m-0 fw-bold">{{ $ads->where('status', 'inactive')->count() }}</h4><small class="text-muted">إعلانات متوقفة</small></div>
        </div>
    </div>

    <div class="row g-4">

        @foreach ($ads as $ad)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="ad-card">

                {{-- Image Display --}}
                <div class="ad-image-container">
                    <img src="{{ asset('storage/' . $ad->image) }}" alt="{{ $ad->title }}" class="ad-image">

                    {{-- Status Badge Overlay --}}
                    <div class="position-absolute top-0 end-0 m-2">
                        @if($ad->status == 'active')
                            <span class="badge bg-success shadow-sm">نشط</span>
                        @else
                            <span class="badge bg-secondary shadow-sm">متوقف</span>
                        @endif
                    </div>
                </div>

                <h5 class="ad-title text-truncate" title="{{ $ad->title }}">{{ $ad->title }}</h5>

                <div class="ad-footer">
                    {{-- Status Toggle Switch --}}
                    <form action="{{ route('ads.update', $ad->id) }}" method="POST" class="m-0">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="title" value="{{ $ad->title }}">
                        {{-- Keep existing image --}}

                        <input type="hidden" name="status" value="inactive">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="status" value="active"
                                   onchange="this.form.submit()" {{ $ad->status == 'active' ? 'checked' : '' }}
                                   style="cursor: pointer;">
                        </div>
                    </form>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editAd{{ $ad->id }}">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteAd{{ $ad->id }}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div class="modal fade" id="editAd{{ $ad->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('ads.update', $ad->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">تعديل الإعلان</h5>
                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">عنوان الإعلان</label>
                                <input type="text" class="form-control" name="title" value="{{ $ad->title }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">الصورة الحالية</label>
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $ad->image) }}" class="rounded w-100" style="height: 150px; object-fit: cover;">
                                </div>
                                <label class="form-label fw-bold small text-muted">تغيير الصورة (اختياري)</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>

                            <input type="hidden" name="status" value="{{ $ad->status }}">
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">حفظ التعديلات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- DELETE MODAL --}}
        <div class="modal fade" id="deleteAd{{ $ad->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('ads.destroy', $ad->id) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body text-center p-5">
                            <div class="mb-3 text-danger fs-1"><i class="fa-solid fa-trash-can"></i></div>
                            <h4 class="fw-bold mb-3">حذف الإعلان؟</h4>
                            <p class="text-muted">سيتم حذف الإعلان نهائياً ولا يمكن التراجع عن ذلك.</p>
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger px-4">نعم، حذف</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ADD NEW AD CARD --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="ad-card add-card" data-bs-toggle="modal" data-bs-target="#addAdModal">
                <i class="fa-solid fa-circle-plus add-icon"></i>
                <h5 class="fw-bold text-muted">إضافة إعلان جديد</h5>
            </div>
        </div>

    </div>
</div>
@endsection

@section('modals')
{{-- ADD AD MODAL --}}
<div class="modal fade" id="addAdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('ads.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">إضافة إعلان جديد</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">عنوان الإعلان <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" placeholder="مثال: خصم 50% على الاشتراكات" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">صورة الإعلان <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="image" accept="image/*" required>
                        <div class="form-text small">يفضل أن تكون الصورة بأبعاد 800x400 بيكسل</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">نشر الإعلان</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
@endsection
