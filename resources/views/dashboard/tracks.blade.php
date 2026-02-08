@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">

@endsection
@section('title')
<h5 class="m-0 fw-bold">مسارات التلاوة  </h5>
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
    <div class="stats-bar mb-4">
        <div class="mini-stat active">
            <div class="stat-icon bg-white text-success"><i class="fa-solid fa-check-circle"></i></div>
            <div><h4 class="m-0 fw-bold">{{ $tracks->where('status', 'active')->count() }}</h4><small class="text-muted">مسارات نشطة</small></div>
        </div>
        <div class="mini-stat">
            <div class="stat-icon bg-white text-primary"><i class="fa-solid fa-users"></i></div>
            <div><h4 class="m-0 fw-bold">1,250</h4><small class="text-muted">طالب مسجل</small></div>
        </div>
    </div>

    <div class="row g-4">

        @foreach ($tracks as $track)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="track-card {{ $track->color_class ?? 'color-green' }}">

                {{-- Marketing Value Badge --}}
                @if($track->marketing_value)
                <div class="marketing-badge">
                    <i class="fa-solid fa-bolt me-1"></i> {{ $track->marketing_value }}
                </div>
                @endif

                <div class="track-header">
                    <div class="track-icon-lg">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>

                    {{-- Only Edit Button (No Dropdown) --}}
                    <button class="btn btn-sm btn-light border rounded-circle" data-bs-toggle="modal" data-bs-target="#editTrack{{ $track->id }}">
                        <i class="fa-solid fa-pen text-muted"></i>
                    </button>
                </div>

                <h5 class="track-title">{{ $track->name }}</h5>

                @if($track->target_group)
                <div class="audience-info">
                    <i class="fa-solid fa-users-viewfinder"></i> {{ $track->target_group }}
                </div>
                @endif

                <p class="track-desc">{{ Str::limit($track->description, 80) }}</p>

                <div class="track-stats">
                    <div class="ts-item"><span class="ts-num">0</span><span class="ts-label">طالب</span></div>
                    <div class="ts-item"><span class="ts-num">0</span><span class="ts-label">معلم</span></div>
                </div>

                <div class="track-footer">
                    {{-- Status Toggle Form --}}
                    <form action="{{ route('tracks.update', $track->id) }}" method="POST" class="m-0 w-100 d-flex justify-content-between align-items-center">
                        @csrf
                        @method('PUT')

                        {{-- Hidden inputs to preserve data --}}
                        <input type="hidden" name="name" value="{{ $track->name }}">
                        <input type="hidden" name="target_group" value="{{ $track->target_group }}">
                        <input type="hidden" name="marketing_value" value="{{ $track->marketing_value }}">
                        <input type="hidden" name="description" value="{{ $track->description }}">
                        <input type="hidden" name="color_class" value="{{ $track->color_class }}">

                        {{-- Status Badge --}}
                        @if($track->status == 'active')
                            <span class="badge bg-success bg-opacity-10 text-success status-badge">نشط</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary status-badge">متوقف</span>
                        @endif

                        {{-- The Switch --}}
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
                <div class="modal-content">
                    <form method="POST" action="{{ route('tracks.update', $track->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold">تعديل المسار</h5>
                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
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
                                <label class="form-label fw-bold small text-muted">وصف مختصر</label>
                                <textarea class="form-control" name="description" rows="3" required>{{ $track->description }}</textarea>
                            </div>

                            {{-- Hidden Status (Handled by switch in card) --}}
                            <input type="hidden" name="status" value="{{ $track->status }}">
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">حفظ التعديلات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ADD NEW TRACK CARD --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="track-card add-card" data-bs-toggle="modal" data-bs-target="#addTrackModal">
                <i class="fa-solid fa-circle-plus add-icon"></i>
                <h5 class="fw-bold text-muted">إضافة مسار جديد</h5>
            </div>
        </div>

    </div>
</div>
@endsection

@section('modals')
{{-- ADD TRACK MODAL --}}
<div class="modal fade" id="addTrackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('tracks.store') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">إضافة مسار جديد</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">اسم المسار <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="مثال: التجويد العملي" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">الفئة المستهدفة</label>
                            <input type="text" class="form-control" name="target_group" placeholder="مثال: المبتدئين">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">القيمة التسويقية</label>
                            <input type="text" class="form-control" name="marketing_value" placeholder="مثال: حصري">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">وصف مختصر <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3" placeholder="وصف يظهر للطلاب..." required></textarea>
                    </div>

                    <input type="hidden" name="status" value="active">
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">حفظ المسار</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')

@endsection
