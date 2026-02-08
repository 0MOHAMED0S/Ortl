<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - ورتل</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-dark: #1a4d2e;
            --primary-light: #e8f5e9;
            --gold-main: #c49a46;
            --gold-light: #fff8e1;
            --bg-body: #ffffff;
            --text-main: #2d3436;
            --text-muted: #636e72;
        }

        /* --- Global Reset & Loader Lock --- */
        html,
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            height: 100%;
            overflow: hidden;
            /* Locked initially for loader */
        }

        /* =========================================
           1. CINEMATIC LOADER (Identical to Home)
           ========================================= */
        #loader-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 9999999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.9s cubic-bezier(0.77, 0, 0.175, 1);
        }

        .loader-brand {
            font-size: clamp(3rem, 10vw, 5rem);
            font-weight: 900;
            line-height: 1;
            position: relative;
            color: #f3f3f3;
            margin: 0;
            letter-spacing: -2px;
        }

        .loader-brand::before {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            color: var(--primary-dark);
            border-right: 4px solid var(--gold-main);
            overflow: hidden;
            animation: fillText 2s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            white-space: nowrap;
        }

        .loader-sub {
            margin-top: 10px;
            font-size: clamp(0.9rem, 3vw, 1.2rem);
            color: var(--gold-main);
            font-weight: 700;
            opacity: 0;
            transform: translateY(15px);
            animation: fadeUp 0.8s ease forwards 1.5s;
            text-align: center;
        }

        @keyframes fillText {
            0% {
                width: 0;
            }

            100% {
                width: 100%;
            }
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up-exit {
            transform: translateY(-100%);
        }

        /* =========================================
           2. LOGIN LAYOUT (Split Screen)
           ========================================= */
        .login-wrapper {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* Left Side: Form */
        .login-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: white;
            position: relative;
            z-index: 2;
        }

        .login-box {
            width: 100%;
            max-width: 450px;
            opacity: 0;
            /* Hidden initially for animation */
            transform: translateY(20px);
            transition: 0.8s ease-out;
        }

        /* Animation Class */
        .login-box.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .brand-logo {
            height: 60px;
            margin-bottom: 30px;
        }

        .welcome-title {
            font-weight: 800;
            font-size: 2rem;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .welcome-sub {
            color: var(--text-muted);
            margin-bottom: 40px;
        }

        /* Form Inputs */
        .form-floating>.form-control {
            border-radius: 12px;
            border: 1px solid #eee;
            background: #fcfcfc;
            height: 55px;
        }

        .form-floating>.form-control:focus {
            border-color: var(--gold-main);
            box-shadow: 0 0 0 4px rgba(196, 154, 70, 0.1);
            background: white;
        }

        .form-floating>label {
            color: #999;
        }

        .password-group {
            position: relative;
        }

        .toggle-pass {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #aaa;
            cursor: pointer;
            transition: 0.3s;
        }

        .toggle-pass:hover {
            color: var(--primary-dark);
        }

        /* Actions */
        .forgot-link {
            color: var(--gold-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            border: none;
            background: var(--primary-dark);
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(26, 77, 46, 0.2);
            transition: 0.3s;
            margin-top: 20px;
        }

        .btn-login:hover {
            background: #143d24;
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(2px);
            box-shadow: none;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
            background: #eee;
            z-index: -1;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            color: #aaa;
            font-size: 0.9rem;
        }

        .social-btn {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #eee;
            background: white;
            color: var(--text-main);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: 0.3s;
            text-decoration: none;
            margin-bottom: 15px;
        }

        .social-btn:hover {
            background: #f9f9f9;
            border-color: #ddd;
        }

        /* Right Side: Visual */
        .visual-side {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-dark) 0%, #0f2e1b 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        /* Pattern Overlay */
        .visual-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(var(--gold-main) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.1;
        }

        /* Glass Card */
        .visual-content {
            position: relative;
            z-index: 2;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 400px;
            transform: translateY(20px);
            opacity: 0;
            transition: 1s ease-out 0.3s;
        }

        .visual-content.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .visual-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--gold-main);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .visual-side {
                display: none;
            }

            .login-side {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div id="loader-screen">
        <div class="loader-container">
            <h1 class="loader-brand" data-text="ورتل">ورتل</h1>
            <div class="loader-sub">مرحباً بعودتك</div>
        </div>
    </div>

    <div class="login-wrapper">
        <div class="login-side">
            <div class="login-box" id="loginBox">
                <div class="text-center">
                    <img src="{{ asset('images/mainlogo.png') }}" alt="Logo" class="brand-logo">
                    <h1 class="welcome-title">مرحباً بعودتك!</h1>
                    <p class="welcome-sub">سجل الدخول للمتابعة إلى لوحة التحكم</p>
                </div>

                <form action="{{ route('admin.login') }}" method="POST" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="emailInput" placeholder="name@example.com" value="{{ old('email') }}" required>
                        <label for="emailInput">البريد الإلكتروني</label>

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="password-group mb-3">
                        <div class="form-floating">
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" id="passwordInput"
                                placeholder="Password" required>
                            <label for="passwordInput">كلمة المرور</label>

                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="button" class="toggle-pass" onclick="togglePassword()">
                            <i class="fa-regular fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-login w-100">تسجيل الدخول</button>
                </form>

            </div>
        </div>

        <div class="visual-side">
            <div class="visual-pattern"></div>
            <div class="visual-content" id="visualContent">
                <div class="visual-icon"><i class="fa-solid fa-quran"></i></div>
                <h2 class="fw-bold mb-3">ورتل القرآن ترتيلاً</h2>
                <p class="opacity-75">منصتك الأولى لإدارة تعليم القرآن الكريم ومتابعة الطلاب والمعلمين بأحدث التقنيات.
                </p>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Loader Logic
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');
                document.body.style.overflow = 'auto'; // Enable Scroll if needed

                // Trigger Entrance Animations
                setTimeout(() => {
                    document.getElementById('loginBox').classList.add('visible');
                    document.getElementById('visualContent').classList.add('visible');
                }, 500);

            }, 2500);
        });

        // 2. Password Toggle
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>
