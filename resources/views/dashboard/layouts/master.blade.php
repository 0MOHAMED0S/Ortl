<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - ورتل</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('dashboard/css/loader.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/css/sidebar.css') }}">
        @yield('styles')

    <style>
        :root {
            /* --- Brand Colors --- */
            --primary-dark: #1a4d2e;
            --primary-light: #e8f5e9;
            --gold-main: #c49a46;
            --gold-light: #fff8e1;
            --bg-body: #f3f4f6;
            --text-main: #2d3436;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
            overflow-y: hidden;
            /* Locked for loader initially */
        }

        /* Dropdown Notifications */
        .notification-menu {
            width: 300px;
            padding: 0;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .notif-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .notif-item {
            padding: 15px;
            border-bottom: 1px solid #f9f9f9;
            display: flex;
            align-items: start;
            gap: 10px;
            transition: 0.2s;
        }

        .notif-item:hover {
            background: #f8f9fa;
        }

        .notif-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e8f5e9;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notif-content p {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .notif-content span {
            font-size: 0.75rem;
            color: #999;
        }

        /* Modals */
        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            background: #fcfcfc;
            border-radius: 20px 20px 0 0;
            padding: 20px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .detail-label {
            width: 120px;
            color: #777;
        }

        .detail-val {
            font-weight: 700;
            color: #333;
        }

        /* Mobile Overlay */
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        @media (max-width: 992px) {
            #sidebar {
                transform: translateX(100%);
                width: 260px;
                min-width: 260px;
            }

            #content-wrapper {
                margin-right: 0;
            }

            .menu-toggle-btn {
                display: block;
            }

            body.sidebar-active #sidebar {
                transform: translateX(0);
            }

            body.sidebar-active #overlay {
                display: block;
                opacity: 1;
            }

            body.sidebar-active {
                overflow: hidden;
            }
        }
    </style>
</head>

<body>


    @include('dashboard.layouts.combonents.loader')
    <div id="overlay"></div>

    <div id="wrapper">

        @include('dashboard.layouts.combonents.sidebar')
        <div id="content-wrapper">

            <nav class="top-navbar">
                <div class="d-flex align-items-center">
                    <button class="menu-toggle-btn me-3"><i class="fa-solid fa-bars"></i></button>
                    <h5 class="m-0 fw-bold">لوحة القيادة</h5>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <div class="position-relative" data-bs-toggle="dropdown" aria-expanded="false"
                            style="cursor: pointer;">
                            <i class="fa-regular fa-bell fs-5 text-secondary"></i>
                            <span
                                class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end notification-menu">
                            <li class="notif-header">الإشعارات (3)</li>
                            <li>
                                <a class="dropdown-item notif-item" href="#">
                                    <div class="notif-icon"><i class="fa-solid fa-user-plus"></i></div>
                                    <div class="notif-content">
                                        <p>طلب انضمام معلم جديد</p>
                                        <span>منذ 5 دقائق</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item notif-item" href="#">
                                    <div class="notif-icon" style="background:#e0f2fe; color:#0284c7;"><i
                                            class="fa-solid fa-cart-shopping"></i></div>
                                    <div class="notif-content">
                                        <p>اشتراك طالب جديد</p>
                                        <span>منذ ساعة</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider m-0">
                            </li>
                            <li><a class="dropdown-item text-center p-2 small text-primary" href="#">عرض كل
                                    الإشعارات</a>
                            </li>
                        </ul>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="admin-details">
                            <span class="admin-name">محمد السيد</span>
                            <span class="admin-email">admin@wrtal.com</span>
                        </div>
                        <img src="https://placehold.co/40/1a4d2e/fff?text=AD" class="rounded-circle border ms-2"
                            width="40" alt="Admin">
                    </div>
                </div>
            </nav>

            @yield('content')

        </div>
    </div>

    @yield('modals')


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // 1. Loader Logic
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');
                document.body.style.overflowY = 'auto'; // Unlock scroll
            }, 2500);
        });

        // 2. Sidebar Logic
        const toggleBtn = document.querySelector('.menu-toggle-btn');
        const overlay = document.getElementById('overlay');
        const body = document.body;

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            body.classList.add('sidebar-active');
        });

        overlay.addEventListener('click', () => {
            body.classList.remove('sidebar-active');
        });

        // Close on link click (Mobile)
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) body.classList.remove('sidebar-active');
            });
        });

        // 3. Modal Functionality
        var requestModal = new bootstrap.Modal(document.getElementById('requestModal'));

        function openRequestModal(name, track, country) {
            document.getElementById('reqName').innerText = name;
            document.getElementById('reqTrack').innerText = track;
            document.getElementById('reqCountry').innerText = country;
            document.getElementById('reqImg').src = `https://placehold.co/80/1a4d2e/fff?text=${name.charAt(0)}`;
            requestModal.show();
        }
    </script>
    @yield('scripts')
</body>

</html>
