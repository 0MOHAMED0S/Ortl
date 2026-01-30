<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقديم مغلق - ورتل</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-dark: #1a4d2e;
            --gold-main: #c49a46;
            --text-main: #2d3436;
            --text-muted: #636e72;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            color: var(--text-main);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        /* --- 1. Dynamic Liquid Background --- */
        .animated-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: linear-gradient(120deg, #fdfbf7, #e8f5e9, #fff8e1);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
        }
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- 2. Floating Particles (Decoration) --- */
        .particle {
            position: absolute;
            opacity: 0.2;
            animation: floatUp 20s linear infinite;
            z-index: -1;
        }
        .p1 { top: 10%; left: 10%; font-size: 2rem; color: var(--primary-dark); animation-duration: 25s; }
        .p2 { top: 80%; left: 20%; font-size: 1.5rem; color: var(--gold-main); animation-duration: 18s; animation-delay: -5s; }
        .p3 { top: 40%; right: 10%; font-size: 3rem; color: var(--primary-dark); animation-duration: 30s; animation-delay: -10s; }
        .p4 { top: 70%; right: 30%; font-size: 1rem; color: var(--gold-main); animation-duration: 15s; }

        @keyframes floatUp {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            20% { opacity: 0.3; }
            80% { opacity: 0.3; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        /* --- Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .navbar-brand img { height: 45px; }
        .back-link {
            text-decoration: none; color: var(--primary-dark); font-weight: 700;
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        .back-link:hover { color: var(--gold-main); transform: translateX(5px); }

        /* --- Main Content --- */
        .main-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            perspective: 1000px;
        }

        /* --- 3. The Animated Card --- */
        .status-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(25px);
            border-radius: 40px;
            padding: 60px 40px;
            text-align: center;
            max-width: 650px;
            width: 100%;
            box-shadow: 0 40px 100px rgba(0,0,0,0.1);
            border: 2px solid #fff;
            position: relative;
            /* Entrance Animation */
            animation: popIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            opacity: 0; transform: scale(0.8);
        }

        @keyframes popIn {
            to { opacity: 1; transform: scale(1); }
        }

        /* Icon Animation */
        .icon-wrapper {
            position: relative;
            width: 100px; height: 100px;
            margin: 0 auto 30px;
        }
        .icon-bg {
            position: absolute; width: 100%; height: 100%;
            background: #fff8e1; border-radius: 50%;
            animation: pulseRipple 2s infinite;
        }
        .main-icon {
            position: relative; font-size: 3rem; color: var(--gold-main);
            line-height: 100px;
            animation: sandGlass 3s infinite ease-in-out;
        }

        @keyframes pulseRipple {
            0% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        @keyframes sandGlass {
            0% { transform: rotate(0); }
            25% { transform: rotate(180deg); }
            50% { transform: rotate(180deg); }
            75% { transform: rotate(360deg); }
            100% { transform: rotate(360deg); }
        }

        .status-title {
            font-weight: 900; font-size: 2.5rem; color: var(--primary-dark);
            margin-bottom: 15px; position: relative;
            background: linear-gradient(45deg, var(--primary-dark), #145c32);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .status-desc {
            color: var(--text-muted); font-size: 1.1rem; line-height: 1.7; margin-bottom: 40px;
        }

        /* --- Animated Form --- */
        .notify-form {
            background: white; padding: 8px; border-radius: 16px;
            display: flex; gap: 10px; border: 1px solid #eee;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .notify-form:hover, .notify-form:focus-within {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(196, 154, 70, 0.15);
            border-color: var(--gold-main);
        }
        .notify-input {
            border: none; outline: none; padding: 10px 15px; flex-grow: 1;
            font-weight: 600; color: var(--primary-dark);
        }
        .btn-notify {
            background: var(--primary-dark); color: white; border: none;
            padding: 12px 30px; border-radius: 12px; font-weight: 700;
            transition: 0.3s; white-space: nowrap; cursor: pointer;
            position: relative; overflow: hidden;
        }
        /* Button Hover Animation */
        .btn-notify::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
        }
        .btn-notify:hover::before { left: 100%; }
        .btn-notify:hover { background: var(--gold-main); box-shadow: 0 5px 15px rgba(196, 154, 70, 0.4); }

        /* Social Icons Entrance */
        .social-links { margin-top: 30px; }
        .social-links a {
            color: #ccc; margin: 0 10px; font-size: 1.3rem; transition: 0.3s;
            display: inline-block;
            animation: bounceIn 0.5s backwards;
        }
        .social-links a:nth-child(1) { animation-delay: 0.8s; }
        .social-links a:nth-child(2) { animation-delay: 0.9s; }
        .social-links a:nth-child(3) { animation-delay: 1.0s; }
        .social-links a:hover { color: var(--primary-dark); transform: scale(1.2); }

        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.3); }
            50% { transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }

        footer { text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.9rem; z-index: 2; }

        @media (max-width: 768px) {
            .status-card { padding: 40px 20px; border-radius: 30px; }
            .status-title { font-size: 1.8rem; }
            .notify-form { flex-direction: column; background: transparent; border: none; box-shadow: none; padding: 0; }
            .notify-input { background: white; border: 1px solid #eee; border-radius: 12px; margin-bottom: 10px; height: 50px; text-align: center; }
            .btn-notify { width: 100%; height: 50px; }
        }
    </style>
</head>
<body>

    <div class="animated-bg"></div>

    <div class="particle p1"><i class="fa-regular fa-star"></i></div>
    <div class="particle p2"><i class="fa-solid fa-circle-notch"></i></div>
    <div class="particle p3"><i class="fa-brands fa-envira"></i></div>
    <div class="particle p4"><i class="fa-solid fa-play"></i></div>

    <nav class="navbar fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><img src="{{ asset('images/wrtlv3.png') }}" alt="ورتل"></a>
            <a href="index.html" class="back-link">
                العودة للرئيسية <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    </nav>

    <div class="main-wrapper" id="parallax-container">
        <div class="status-card">

            <div class="icon-wrapper">
                <div class="icon-bg"></div>
                <i class="fa-solid fa-hourglass-half main-icon"></i>
            </div>

            <h1 class="status-title">باب التقديم مغلق</h1>

            <p class="status-desc">
                اكتمل عدد المقاعد حالياً، ولكننا نقدر حماسك!<br>
                لا تفوت الفرصة القادمة، سجل بريدك لنرسل لك دعوة خاصة فور فتح الباب.
            </p>

            <form class="notify-form">
                <input type="email" class="notify-input" placeholder="اكتب بريدك الإلكتروني..." required>
                <button type="submit" class="btn-notify">تنبيهي عند الفتح <i class="fa-solid fa-bell ms-2"></i></button>
            </form>

            <div class="social-links">
                <p class="small mb-2 text-muted fw-bold">كن بالقرب منا</p>
                <div class="d-flex justify-content-center">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        جميع الحقوق محفوظة © ورتل 2026
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

    <script>
        $(document).ready(function(){

            // Mouse Parallax Effect (Moves background slightly with mouse)
            document.addEventListener('mousemove', function(e) {
                const x = (window.innerWidth - e.pageX * 2) / 100;
                const y = (window.innerHeight - e.pageY * 2) / 100;

                // Move decorations
                $('.particle').css('transform', `translateX(${x}px) translateY(${y}px)`);
            });

            // Form Logic (Same visual feedback)
            $('.notify-form').on('submit', function(e){
                e.preventDefault();
                const btn = $('.btn-notify');
                const originalText = btn.html();

                btn.html('<i class="fa-solid fa-spinner fa-spin"></i> جاري التسجيل...');
                btn.css('opacity', '0.8');

                setTimeout(() => {
                    $('.notify-form').html('<div class="text-success fw-bold w-100 py-2"><i class="fa-solid fa-check-circle me-2"></i> تم التسجيل! ستكون أول من يعلم.</div>');
                    $('.notify-form').css({'background': '#e8f5e9', 'border-color': '#c8e6c9', 'justify-content': 'center'});
                }, 1500);
            });
        });
    </script>
</body>
</html>
