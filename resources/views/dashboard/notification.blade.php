@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #0d9488;
            --primary-light: #ccfbf1;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        .notification-page {
            max-width: 850px;
            margin: 0 auto;
            padding: 30px 15px;
        }

        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-mark-all {
            background-color: var(--card-bg);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-mark-all:hover {
            background-color: var(--primary-color);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.2);
        }

        /* Notification Card */
        .notif-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .notif-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        /* Unread State */
        .notif-card.is-unread {
            background-color: #f4fdfb;
            border-color: var(--primary-light);
        }

        .unread-dot {
            position: absolute;
            top: 25px;
            right: 20px;
            width: 12px;
            height: 12px;
            background-color: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Icons */
        .notif-icon-wrap {
            width: 55px;
            height: 55px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            background-color: #f1f5f9;
            color: #64748b;
        }

        .icon-student { background-color: #eff6ff; color: #3b82f6; }
        .icon-order { background-color: #f0fdf4; color: #16a34a; }
        .icon-withdrawal { background-color: #fff7ed; color: #ea580c; }
        .icon-teacher { background-color: #faf5ff; color: #9333ea; }
        .icon-cancel { background-color: #fef2f2; color: #ef4444; }

        /* Content */
        .notif-body {
            flex-grow: 1;
            padding-right: 15px; /* Space for unread dot */
        }

        .notif-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0 0 8px;
        }

        .notif-message {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin: 0 0 15px;
        }

        .notif-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #94a3b8;
            border-top: 1px dashed var(--border-color);
            padding-top: 12px;
        }

        .btn-read {
            background: none;
            border: none;
            color: var(--primary-color);
            font-weight: 700;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }
        .btn-read:hover { color: #0f766e; text-decoration: underline; }

        /* Empty State */
        .empty-box {
            text-align: center;
            padding: 80px 20px;
            background-color: var(--card-bg);
            border-radius: 20px;
            border: 2px dashed var(--border-color);
        }
        .empty-box i { font-size: 4rem; color: #cbd5e1; margin-bottom: 20px; }
        .empty-box h4 { font-weight: 800; color: var(--text-main); margin-bottom: 10px; }

        /* Animation for incoming notif */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    </style>
@endsection

@section('title')
    <h4 class="m-0 fw-bold">الإشعارات</h4>
@endsection

@section('content')
<div class="container-fluid">
    <div class="notification-page">

        <div class="header-section">
            <h2 class="header-title">
                <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 d-flex">
                    <i class="fa-regular fa-bell"></i>
                </div>
                سجل الإشعارات
            </h2>

            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('admin.notifications.readAll') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-mark-all">
                        <i class="fa-solid fa-check-double me-2"></i>تحديد الكل كمقروء
                    </button>
                </form>
            @endif
        </div>

        <div id="notificationsWrapper">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? 'default';
                    $isUnread = is_null($notification->read_at);

                    // تعيين الأيقونات والألوان
                    $icon = 'fa-bell';
                    $colorClass = '';

                    if($type === 'new_student') { $icon = 'fa-user-graduate'; $colorClass = 'icon-student'; }
                    elseif($type === 'new_order') { $icon = 'fa-sack-dollar'; $colorClass = 'icon-order'; }
                    elseif($type === 'new_withdrawal') { $icon = 'fa-money-bill-transfer'; $colorClass = 'icon-withdrawal'; }
                    elseif($type === 'withdrawal_cancelled') { $icon = 'fa-ban'; $colorClass = 'icon-cancel'; }
                    elseif($type === 'new_teacher_application') { $icon = 'fa-chalkboard-user'; $colorClass = 'icon-teacher'; }
                @endphp

                <div class="notif-card {{ $isUnread ? 'is-unread' : '' }}">
                    @if($isUnread) <span class="unread-dot" title="إشعار غير مقروء"></span> @endif

                    <div class="notif-icon-wrap {{ $colorClass }}">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>

                    <div class="notif-body">
                        <h4 class="notif-title">{{ $data['title'] ?? 'تنبيه نظام' }}</h4>
                        <p class="notif-message">{{ $data['message'] ?? 'يوجد تحديث جديد في النظام يحتاج إلى مراجعتك.' }}</p>

                        <div class="notif-footer">
                            <span><i class="fa-regular fa-clock me-2"></i>{{ $notification->created_at->diffForHumans() }}</span>

                            @if($isUnread)
                                <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn-read">
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
                    <p class="text-muted m-0">أنت على اطلاع دائم بكل التحديثات. سنقوم بتنبيهك فور وصول إشعار جديد.</p>
                </div>
            @endforelse
        </div>

        {{-- التصفح --}}
        <div class="d-flex justify-content-center mt-5">
            {{ $notifications->links() }}
        </div>

    </div>
</div>

{{-- ملف صوتي للإشعارات اللحظية --}}
<audio id="notifSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

@endsection

@section('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {

        // 1. تفعيل وضع اكتشاف الأخطاء (سيظهر كل شيء في الـ Console)
        Pusher.logToConsole = true;

        // 2. التحقق من وجود توكن الحماية
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        if(!csrfToken) {
            console.error("⚠️ خطأ: وسم meta csrf-token غير موجود في صفحة master.blade.php!");
        }

        // 3. إعداد الـ Echo
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("PUSHER_APP_KEY") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            }
        });

        const adminId = {{ auth()->id() }};
        const alertSound = document.getElementById('notifSound');

        console.log("🚀 جاري الاتصال بقناة: private-admin." + adminId);

        // 4. الاستماع للقناة الخاصة بالمدير
        window.Echo.private(`admin.${adminId}`)
            .subscribed(() => {
                console.log("✅ تم الاتصال بقناة الإشعارات بنجاح!");
            })
            .error((err) => {
                console.error("❌ فشل الاتصال بالقناة (قد تكون مشكلة في routes/channels.php أو CSRF): ", err);
            })
            .listen('.NewStudent', (e) => {
                renderNewNotification('طالب جديد 🎉', `سجل الطالب ${e.student.student_name} للتو في التطبيق.`, 'fa-user-graduate', 'icon-student');
            })
            .listen('.NewOrder', (e) => {
                renderNewNotification('عملية شراء جديدة 💰', `قام الطالب ${e.order.student_name} بشراء باقة بمبلغ ${e.order.amount}$.`, 'fa-sack-dollar', 'icon-order');
            })
            .listen('.WithdrawalRequested', (e) => {
                renderNewNotification('طلب سحب أرباح 💸', `طلب المعلم ${e.request.teacher_name} سحب ${e.request.amount}$.`, 'fa-money-bill-transfer', 'icon-withdrawal');
            })
            .listen('.WithdrawalCancelled', (e) => {
                renderNewNotification('إلغاء طلب سحب 🔄', `تراجع المعلم ${e.cancellation.teacher_name} عن طلب السحب.`, 'fa-ban', 'icon-cancel');
            })
            .listen('.NewTeacherApplication', (e) => {
                // هنا استخدمنا e.application.full_name بناءً على الـ JSON الذي أرسلته لي
                renderNewNotification('طلب انضمام معلم 📝', `قدم ${e.application.full_name} طلب انضمام كمعلم.`, 'fa-chalkboard-user', 'icon-teacher');
            });

        // دالة إنشاء الإشعار
        function renderNewNotification(title, message, icon, colorClass) {
            alertSound.play().catch(error => console.log('الصوت يحتاج تفاعل'));

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: title,
                text: message,
                showConfirmButton: false,
                timer: 6000,
                timerProgressBar: true,
            });

            $('#emptyStateBox').fadeOut();

            let html = `
                <div class="notif-card is-unread animate-in">
                    <span class="unread-dot" title="إشعار غير مقروء"></span>
                    <div class="notif-icon-wrap ${colorClass}">
                        <i class="fa-solid ${icon}"></i>
                    </div>
                    <div class="notif-body">
                        <h4 class="notif-title">${title}</h4>
                        <p class="notif-message">${message}</p>
                        <div class="notif-footer">
                            <span class="text-primary fw-bold"><i class="fa-solid fa-bolt me-2"></i>الآن (لحظي)</span>
                        </div>
                    </div>
                </div>
            `;

            $('#notificationsWrapper').prepend(html);
        }
    });
</script>
@endsection
