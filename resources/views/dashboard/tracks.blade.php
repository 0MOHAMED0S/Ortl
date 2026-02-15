@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* --- Professional Styles --- */
        .track-icon-lg img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
        }
        .current-icon-preview {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: #f8f9fa;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 15px;
            margin-bottom: 10px;
        }

        /* Filter Styling */
        .filter-btn {
            border: none;
            color: #64748b;
            background: transparent;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .filter-btn.active {
            background-color: #1cc6a4 !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(28, 198, 164, 0.25);
        }

        .track-item {
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        .track-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .track-footer {
            margin-top: auto;
        }
    </style>
@endsection

@section('title')
<div class="d-flex justify-content-between align-items-center w-100">
    <h5 class="m-0 fw-bold">مسارات التلاوة</h5>
</div>
@endsection

@section('content')
<div class="container-fluid p-4">

    {{-- Alert Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
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

    {{-- Stats & Filter Bar --}}
    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-6">
            <div class="stats-bar d-flex gap-3">
                <div class="mini-stat active mb-0 flex-grow-1">
                    <div class="stat-icon bg-white text-success"><i class="fa-solid fa-check-circle"></i></div>
                    <div>
                        <h4 class="m-0 fw-bold">{{ $tracks->where('status', 'active')->count() }}</h4>
                        <small class="text-muted">مسارات نشطة</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="btn-group p-1 bg-white rounded-pill shadow-sm" role="group">
                <button type="button" class="btn btn-sm rounded-pill px-4 filter-btn active" data-filter="all">الكل</button>
                <button type="button" class="btn btn-sm rounded-pill px-4 filter-btn" data-filter="active">نشط</button>
                <button type="button" class="btn btn-sm rounded-pill px-4 filter-btn" data-filter="stopped">متوقف</button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach ($tracks as $track)
        <div class="col-xl-3 col-lg-4 col-md-6 track-item" data-status="{{ $track->status }}">
            <div class="track-card {{ $track->color_class ?? 'color-green' }} shadow-sm">

                {{-- Marketing Value Badge --}}
                @if($track->marketing_value)
                <div class="marketing-badge">
                    <i class="fa-solid fa-bolt me-1"></i> {{ $track->marketing_value }}
                </div>
                @endif

                <div class="track-header">
                    <div class="track-icon-lg">
                        @if($track->icon)
                            <img src="{{ asset('storage/' . $track->icon) }}" alt="{{ $track->name }}">
                        @else
                            <i class="fa-solid fa-book-open-reader"></i>
                        @endif
                    </div>

                    <button class="btn btn-sm btn-light border rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#editTrack{{ $track->id }}">
                        <i class="fa-solid fa-pen text-muted"></i>
                    </button>
                </div>

                <h5 class="track-title mt-3">{{ $track->name }}</h5>

                @if($track->target_group)
                <div class="audience-info">
                    <i class="fa-solid fa-users-viewfinder"></i> {{ $track->target_group }}
                </div>
                @endif

                <p class="track-desc">{{ Str::limit($track->description, 80) }}</p>

                <div class="track-stats">
                    <div class="ts-item"><span class="ts-num">{{ $track->teachers_count ?? 0 }}</span><span class="ts-label">معلم</span></div>
                </div>

                <div class="track-footer">
                    <form action="{{ route('tracks.update', $track->id) }}" method="POST" class="m-0 w-100 d-flex justify-content-between align-items-center">
                        @csrf @method('PUT')

                        {{-- Hidden inputs to maintain data integrity --}}
                        <input type="hidden" name="name" value="{{ $track->name }}">
                        <input type="hidden" name="description" value="{{ $track->description }}">

                        @if($track->status == 'active')
                            <span class="badge bg-success bg-opacity-10 text-success status-badge">نشط</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary status-badge">متوقف</span>
                        @endif

                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="status" value="stopped">
                            <input class="form-check-input track-status-switch" type="checkbox" name="status" value="active"
                            onchange="this.form.submit()" {{ $track->status == 'active' ? 'checked' : '' }}>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div class="modal fade" id="editTrack{{ $track->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="{{ route('tracks.update', $track->id) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="modal-header border-0 bg-light">
                            <h5 class="modal-title fw-bold">تعديل المسار</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="text-center mb-4">
                                <label class="form-label d-block fw-bold small text-muted">الأيقونة</label>
                                @if($track->icon)
                                    <img src="{{ asset('storage/' . $track->icon) }}" class="current-icon-preview">
                                @endif
                                <input type="file" class="form-control shadow-none" name="icon" accept="image/*">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">اسم المسار</label>
                                <input type="text" class="form-control" name="name" value="{{ $track->name }}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-muted">الفئة المستهدفة</label>
                                    <input type="text" class="form-control" name="target_group" value="{{ $track->target_group }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-muted">القيمة التسويقية</label>
                                    <input type="text" class="form-control" name="marketing_value" value="{{ $track->marketing_value }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">الوصف المختصر</label>
                                <textarea class="form-control" name="description" rows="3" required>{{ $track->description }}</textarea>
                            </div>
                            <input type="hidden" name="status" value="{{ $track->status }}">
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">حفظ التغييرات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ADD PLACEHOLDER --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="track-card add-card h-100 d-flex flex-column align-items-center justify-content-center py-5"
                 data-bs-toggle="modal" data-bs-target="#addTrackModal" style="cursor: pointer; border: 2px dashed #ddd; background: #fafafa;">
                <i class="fa-solid fa-circle-plus mb-3 text-muted" style="font-size: 3rem;"></i>
                <h6 class="fw-bold text-muted">إضافة مسار جديد</h6>
            </div>
        </div>

    </div>
</div>
@endsection

@section('modals')
    {{-- ADD TRACK MODAL --}}
    <div class="modal fade" id="addTrackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('tracks.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-0 bg-light">
                        <h5 class="modal-title fw-bold">مسار تلاوة جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">اسم المسار <span class="text-danger">*</span></label>
                            <input type="text" class="form-control py-2" name="name" placeholder="مثال: التجويد العملي" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">أيقونة المسار</label>
                            <input type="file" class="form-control" name="icon" accept="image/*">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">الفئة المستهدفة</label>
                                <input type="text" class="form-control" name="target_group" placeholder="مثال: المبتدئين">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">القيمة التسويقية</label>
                                <input type="text" class="form-control" name="marketing_value" placeholder="مثال: خصم 20%">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">وصف المسار</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="اكتب وصفاً جذاباً للطلاب..." required></textarea>
                        </div>
                        <input type="hidden" name="status" value="active">
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold rounded-pill">تأكيد الإضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const trackItems = document.querySelectorAll('.track-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // UI Toggle
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const filterValue = button.getAttribute('data-filter');

            trackItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-status') === filterValue) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
});
</script>
@endsection
