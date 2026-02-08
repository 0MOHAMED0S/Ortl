<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - ورتل</title>
<link rel="icon" href="{{ asset('images/mainlogo.png') }}" type="image/png">

    {{-- Bootstrap & Fonts --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    {{-- Custom Styles --}}
    <link rel="stylesheet" href="{{ asset('dashboard/css/loader.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/css/sidebar.css') }}">

    <style>
        :root {
            /* --- Brand Colors --- */
    --primary-dark: #2d8a74;      /* الأخضر الأساسي في كلمة ورتل */
            --primary-light: #e8f5e9;
            --gold-main: #000100;
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
            /* FIX 1: Removed 'overflow-y: hidden' from here so Bootstrap doesn't get confused */
        }

        /* FIX 2: Added specific class to lock scroll only during loader */
        body.loader-locked {
            overflow: hidden !important;
        }

        /* Base Layout */
        #content-wrapper {
            width: 100%;
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .menu-toggle-btn {
            background: transparent;
            border: none;
            font-size: 1.4rem;
            color: var(--primary-dark);
            cursor: pointer;
            display: none;
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

        /* Modals Generic Styling */
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

        /* --- RESPONSIVE SIDEBAR FIXES --- */
        @media (max-width: 992px) {
            #sidebar {
                position: fixed; /* Must be fixed to overlay content */
                top: 0;
                right: 0;
                height: 100vh;
                width: 260px;
                min-width: 260px;
                z-index: 1000; /* Higher than overlay */
                transform: translateX(100%); /* Hidden by default (RTL) */
                transition: transform 0.3s ease-in-out;
            }

            #content-wrapper {
                margin-right: 0;
            }

            .menu-toggle-btn {
                display: block;
            }

            body.sidebar-active #sidebar {
                transform: translateX(0); /* Show sidebar */
            }

            body.sidebar-active #overlay {
                display: block;
                opacity: 1;
            }

            body.sidebar-active {
                overflow: hidden; /* Prevent body scroll when sidebar is open */
            }
        }
    </style>

    @yield('styles')
</head>

<body class="loader-locked">

    @include('dashboard.layouts.combonents.loader')

    <div id="overlay"></div>

    <div id="wrapper">

        @include('dashboard.layouts.combonents.sidebar')

        <div id="content-wrapper">
            <nav class="top-navbar">
                <div class="d-flex align-items-center">
                    <button class="menu-toggle-btn me-3"><i class="fa-solid fa-bars"></i></button>
                        @yield('title')
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <div class="position-relative" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                            <i class="fa-regular fa-bell fs-5 text-secondary"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
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
                                    <div class="notif-icon" style="background:#e0f2fe; color:#0284c7;"><i class="fa-solid fa-cart-shopping"></i></div>
                                    <div class="notif-content">
                                        <p>اشتراك طالب جديد</p>
                                        <span>منذ ساعة</span>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider m-0"></li>
                            <li><a class="dropdown-item text-center p-2 small text-primary" href="#">عرض كل الإشعارات</a></li>
                        </ul>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="admin-details text-end">
                            <span class="d-block fw-bold small text-dark">{{ Auth::user()->name }}</span>
                            <span class="d-block small text-muted" style="font-size: 0.75rem;">{{ Auth::user()->email }}</span>
                        </div>
                        <img src="https://placehold.co/40/1a4d2e/fff?text={{ substr(Auth::user()->name, 0, 1) }}" class="rounded-circle border ms-2" width="40" alt="Admin">
                    </div>
                </div>
            </nav>

            @yield('content')

        </div>
    </div>

    @yield('modals')

    {{-- Scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // 1. Loader Logic (Corrected for Modal Scroll Bug)
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');

                // FIX 4: Remove the class instead of setting inline style
                document.body.classList.remove('loader-locked');
                // This allows Bootstrap to manage 'overflow' when modals open/close
            }, 2500);
        });

        // 2. Sidebar Logic
        const toggleBtn = document.querySelector('.menu-toggle-btn');
        const overlay = document.getElementById('overlay');
        const body = document.body;

        if(toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                body.classList.add('sidebar-active');
            });
        }

        if(overlay) {
            overlay.addEventListener('click', () => {
                body.classList.remove('sidebar-active');
            });
        }

        // Close Sidebar on link click (Mobile)
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) body.classList.remove('sidebar-active');
            });
        });

        // 3. Optional: Global Modal Helpers if needed
        // (You can keep your existing modal logic here if required)
    </script>
    @yield('scripts')
</body>
</html>
