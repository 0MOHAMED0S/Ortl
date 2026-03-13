@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #ccfbf1;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        .notification-page { max-width: 900px; margin: 0 auto; padding: 30px 15px; }

        /* Header Section */
        .header-section {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid var(--border-color);
            flex-wrap: wrap; gap: 15px;
        }

        .header-title {
            font-size: 1.5rem; font-weight: 800; color: var(--text-main);
            margin: 0; display: flex; align-items: center; gap: 12px;
        }

        .action-buttons { display: flex; gap: 10px; }

        .btn-custom {
            border-radius: 50px; padding: 10px 22px; font-weight: 700; font-size: 0.85rem;
            transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; border: none;
        }

        .btn-send-notif { background-color: var(--primary-color); color: #fff; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); }
        .btn-send-notif:hover { background-color: var(--primary-dark); transform: translateY(-2px); color: #fff; box-shadow: 0 6px 15px rgba(13, 148, 136, 0.3); }

        .btn-mark-all { background-color: #fff; color: var(--text-secondary); border: 1px solid var(--border-color); }
        .btn-mark-all:hover { border-color: var(--primary-color); color: var(--primary-color); }

        /* Notification Card */
        .notif-card {
            background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 20px;
            padding: 20px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 20px;
            transition: all 0.3s ease; position: relative;
        }
        .notif-card:hover { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); border-color: #cbd5e1; }
        .notif-card.is-unread { background-color: #f4fdfb; border-color: var(--primary-light); }

        .unread-dot {
            position: absolute; top: 25px; right: 20px; width: 10px; height: 10px;
            background-color: #ef4444; border-radius: 50%; animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .notif-icon-wrap {
            width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center;
            justify-content: center; font-size: 1.3rem; flex-shrink: 0;
        }
        .icon-student { background-color: #eff6ff; color: #3b82f6; }
        .icon-order { background-color: #f0fdf4; color: #16a34a; }
        .icon-withdrawal { background-color: #fff7ed; color: #ea580c; }
        .icon-teacher { background-color: #faf5ff; color: #9333ea; }
        .icon-cancel { background-color: #fef2f2; color: #ef4444; }
        .icon-broadcast { background-color: #f0fdfa; color: #0d9488; }

        .notif-body { flex-grow: 1; padding-right: 10px; }
        .notif-title { font-size: 1rem; font-weight: 800; color: var(--text-main); margin: 0 0 5px; }
        .notif-message { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; margin: 0 0 12px; }

        .notif-footer {
            display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;
            color: #94a3b8; padding-top: 10px; border-top: 1px solid #f1f5f9;
        }
        .btn-read { background: none; border: none; color: var(--primary-color); font-weight: 700; display: flex; align-items: center; gap: 4px; padding: 0;}
        .btn-read:hover { color: var(--primary-dark); text-decoration: underline; }

        /* Pagination Styling */
        .pagination-wrapper { margin-top: 40px; display: flex; justify-content: center; }
        .pagination { gap: 5px; }
        .page-link { border-radius: 10px !important; border: none; color: var(--text-secondary); padding: 10px 16px; font-weight: 600; }
        .page-item.active .page-link { background-color: var(--primary-color); color: #fff; box-shadow: 0 4px 10px rgba(13, 148, 136, 0.3); }

        .empty-box { text-align: center; padding: 60px; background: #fff; border-radius: 24px; border: 2px dashed #e2e8f0; }

        /* Professional Modal Design */
        .premium-modal .modal-content { border-radius: 28px; border: none; overflow: hidden; }
        .premium-modal .modal-header {
            background: linear-gradient(to left, #f8fafc, #ffffff);
            border-bottom: 1px solid #f1f5f9; padding: 25px 30px;
        }
        .premium-modal .modal-title { font-weight: 800; color: var(--text-main); font-size: 1.25rem; }
        .premium-modal .modal-body { padding: 30px; background-color: #fcfcfd; }

        .custom-form-group { margin-bottom: 20px; }
        .custom-label { font-weight: 700; color: #334155; font-size: 0.9rem; margin-bottom: 8px; display: block; }
        .custom-input {
            border-radius: 14px; border: 1px solid #cbd5e1; padding: 14px 18px; font-size: 0.95rem;
            transition: all 0.3s; background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .custom-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); outline: none; }

        .target-selector { display: flex; gap: 10px; }
        .target-radio { display: none; }
        .target-label {
            flex: 1; text-align: center; padding: 12px 5px; border: 2px solid #e2e8f0; border-radius: 14px;
            cursor: pointer; font-weight: 700; color: var(--text-secondary); transition: 0.3s; background: #fff;
            font-size: 0.85rem; display: flex; flex-direction: column; align-items: center; gap: 8px;
        }
        .target-label i { font-size: 1.4rem; }
        .target-radio:checked + .target-label { border-color: var(--primary-color); color: var(--primary-color); background: var(--primary-light); }

        .submit-btn-wrapper { padding: 20px 30px 30px; background-color: #fcfcfd; }
        .btn-submit-premium {
            background: var(--primary-color); color: white; border: none; border-radius: 16px;
            padding: 16px; font-weight: 800; font-size: 1rem; width: 100%; transition: 0.3s;
            display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        .btn-submit-premium:hover:not(:disabled) { background: var(--primary-dark); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(13, 148, 136, 0.3); }
        .btn-submit-premium:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }

        .spinner-border { display: none; width: 1.2rem; height: 1.2rem; border-width: 0.15em; }
        .btn-submit-premium.is-loading .spinner-border { display: inline-block; }
        .btn-submit-premium.is-loading .btn-text { display: none; }
        .btn-submit-premium.is-loading .fa-paper-plane { display: none; }

        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: slideIn 0.4s ease-out forwards; }
    </style>
@endsection

@section('title')
    <h4 class="m-0 fw-bold">مركز الإشعارات</h4>
@endsection

@section('content')
<div class="container-fluid">
    <div class="notification-page">

        {{-- SweetAlert Flash Messages --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'تم بنجاح', text: "{{ session('success') }}", showConfirmButton: false, timer: 4000 });
                });
            </script>
        @endif
        @if(session('error') || $errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'خطأ', text: "{{ session('error') ?? $errors->first() }}", showConfirmButton: false, timer: 5000 });
                });
            </script>
        @endif

        <div class="header-section">
            <h2 class="header-title">
                <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 d-flex shadow-sm">
                    <i class="fa-solid fa-tower-broadcast"></i>
                </div>
                إدارة التنبيهات
            </h2>

            <div class="action-buttons">
                <button class="btn-custom btn-send-notif" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                    <i class="fa-solid fa-paper-plane"></i> إرسال إشعار جديد
                </button>

                @if(auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('admin.notifications.readAll') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn-custom btn-mark-all" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> جاري التحديث...'; this.style.pointerEvents='none';">
                            <i class="fa-solid fa-check-double text-success"></i> تحديد الكل كمقروء
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div id="notificationsWrapper">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? 'default';
                    $isUnread = is_null($notification->read_at);

                    $icons = [
                        'new_student' => ['icon' => 'fa-user-graduate', 'class' => 'icon-student'],
                        'new_order' => ['icon' => 'fa-sack-dollar', 'class' => 'icon-order'],
                        'new_withdrawal' => ['icon' => 'fa-money-bill-transfer', 'class' => 'icon-withdrawal'],
                        'withdrawal_cancelled' => ['icon' => 'fa-ban', 'class' => 'icon-cancel'],
                        'new_teacher_application' => ['icon' => 'fa-chalkboard-user', 'class' => 'icon-teacher'],
                        'admin_broadcast' => ['icon' => 'fa-bullhorn', 'class' => 'icon-broadcast'],
                    ];
                    $iconData = $icons[$type] ?? ['icon' => 'fa-bell', 'class' => 'bg-light text-secondary'];
                @endphp

                <div class="notif-card {{ $isUnread ? 'is-unread' : '' }} animate-in">
                    @if($isUnread) <span class="unread-dot"></span> @endif

                    <div class="notif-icon-wrap {{ $iconData['class'] }}">
                        <i class="fa-solid {{ $iconData['icon'] }}"></i>
                    </div>

                    <div class="notif-body">
                        <h4 class="notif-title">{{ $data['title'] ?? 'تنبيه نظام' }}</h4>
                        <p class="notif-message">{{ $data['message'] ?? '' }}</p>

                        <div class="notif-footer">
                            <span><i class="fa-regular fa-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}</span>

                            @if($isUnread)
                                <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn-read" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i>'; this.style.pointerEvents='none';">
                                        تحديد كمقروء <i class="fa-solid fa-check ms-1"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-success fw-bold"><i class="fa-solid fa-check-double me-1"></i> مقروء</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-box" id="emptyStateBox">
                    <i class="fa-regular fa-bell-slash"></i>
                    <h4>لا توجد إشعارات حالياً</h4>
                    <p class="text-muted m-0">سجل الإشعارات فارغ، سيتم عرض التنبيهات هنا فور وصولها.</p>
                </div>
            @endforelse
        </div>

        <div class="pagination-wrapper">
            {!! $notifications->links('pagination::bootstrap-5') !!}
        </div>
    </div>
</div>

<div class="modal fade premium-modal" id="sendNotificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">
                    <div class="d-inline-block bg-primary bg-opacity-10 p-2 rounded-3 me-2">
                        <i class="fa-solid fa-paper-plane text-primary"></i>
                    </div>
                    إرسال تنبيه جديد
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.notifications.broadcast') }}" method="POST" id="broadcastForm">
                @csrf
                <div class="modal-body">

                    <div class="custom-form-group">
                        <label class="custom-label">الفئة المستهدفة <span class="text-danger">*</span></label>
                        <div class="target-selector">
                            <input type="radio" name="target" id="target_all" value="all" class="target-radio" checked>
                            <label for="target_all" class="target-label">
                                <i class="fa-solid fa-users"></i> الكل
                            </label>

                            <input type="radio" name="target" id="target_students" value="students" class="target-radio">
                            <label for="target_students" class="target-label">
                                <i class="fa-solid fa-user-graduate"></i> الطلاب
                            </label>

                            <input type="radio" name="target" id="target_teachers" value="teachers" class="target-radio">
                            <label for="target_teachers" class="target-label">
                                <i class="fa-solid fa-chalkboard-user"></i> المعلمين
                            </label>
                        </div>
                    </div>

                    <div class="custom-form-group">
                        <label class="custom-label">عنوان الإشعار <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control custom-input" placeholder="مثال: خصم جديد بمناسبة شهر رمضان" required maxlength="100">
                    </div>

                    <div class="custom-form-group mb-0">
                        <label class="custom-label">نص الرسالة <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control custom-input" rows="4" placeholder="اكتب التفاصيل هنا بوضوح..." required maxlength="500"></textarea>
                    </div>
                </div>

                <div class="submit-btn-wrapper">
                    <button type="submit" class="btn-submit-premium" id="submitBroadcastBtn">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span class="btn-text">إرسال التنبيه الآن</span>
                        <span class="spinner-border" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<audio id="notifSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>
@endsection

@section('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // --- منع النقر المزدوج وعرض حالة التحميل (Loading State) ---
    document.getElementById('broadcastForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBroadcastBtn');

        // إذا كان الزر في حالة تحميل أصلاً، امنع الإرسال مرة أخرى
        if(btn.classList.contains('is-loading')) {
            e.preventDefault();
            return;
        }

        // تحويل الزر لحالة التحميل
        btn.classList.add('is-loading');
        btn.disabled = true;
    });


    // --- إعدادات الإشعارات اللحظية (Pusher & Echo) ---
    $(document).ready(function () {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("PUSHER_APP_KEY") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } }
        });

        const adminId = {{ auth()->id() }};
        const alertSound = document.getElementById('notifSound');

        window.Echo.private(`admin.${adminId}`)
            .listen('.NewStudent', (e) => { renderNewNotification('طالب جديد 🎉', `سجل الطالب ${e.student.student_name} للتو.`, 'fa-user-graduate', 'icon-student'); })
            .listen('.NewOrder', (e) => { renderNewNotification('عملية شراء 💰', `قام الطالب ${e.order.student_name} بشراء باقة.`, 'fa-sack-dollar', 'icon-order'); })
            .listen('.NewTeacherApplication', (e) => { renderNewNotification('طلب انضمام معلم 📝', `قدم ${e.application.full_name} طلب جديد.`, 'fa-chalkboard-user', 'icon-teacher'); });

        function renderNewNotification(title, message, icon, colorClass) {
            alertSound.play().catch(e => console.log('Interactions needed for sound'));
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: title, text: message, showConfirmButton: false, timer: 4000 });
            $('#emptyStateBox').fadeOut();

            let html = `
                <div class="notif-card is-unread animate-in">
                    <span class="unread-dot"></span>
                    <div class="notif-icon-wrap ${colorClass}"> <i class="fa-solid ${icon}"></i> </div>
                    <div class="notif-body">
                        <h4 class="notif-title">${title}</h4>
                        <p class="notif-message">${message}</p>
                        <div class="notif-footer"> <span class="text-primary fw-bold"><i class="fa-solid fa-bolt me-1"></i> الآن</span> </div>
                    </div>
                </div>`;
            $('#notificationsWrapper').prepend(html);
        }
    });
</script>
@endsection
