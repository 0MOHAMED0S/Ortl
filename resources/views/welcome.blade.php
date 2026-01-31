<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورتل - رتل القرآن ترتيلاً</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <style>
        :root {
            /* Brand Identity */
            --primary-dark: #1a4d2e;
            --primary-light: #e8f5e9;
            --gold-main: #c49a46;
            --gold-light: #fff8e1;

            /* UI Colors */
            --bg-body: #ffffff;
            --text-main: #2d3436;
            --text-muted: #636e72;

            /* Specific Components */
            --card-cream: #ffeeda;
            --btn-orange: #ffab40;
            --btn-orange-shadow: #e69123;
        }

        /* --- Global Reset & Scroll Locking --- */
        html, body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden !important;
            width: 100%;
            -webkit-font-smoothing: antialiased;
        }

        body.loading-locked {
            overflow: hidden !important;
            height: 100vh;
        }

        /* =========================================
           1. CINEMATIC LOADER
           ========================================= */
        #loader-screen {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #ffffff;
            z-index: 9999999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.9s cubic-bezier(0.77, 0, 0.175, 1);
        }

        .loader-container { position: relative; overflow: hidden; padding: 20px; }

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
            top: 0; left: 0; width: 0; height: 100%;
            color: var(--primary-dark);
            border-right: 4px solid var(--gold-main);
            overflow: hidden;
            animation: fillText 2.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            white-space: nowrap;
        }

        .loader-sub {
            margin-top: 10px;
            font-size: clamp(0.9rem, 3vw, 1.2rem);
            color: var(--gold-main);
            font-weight: 700;
            opacity: 0;
            transform: translateY(15px);
            animation: fadeUp 0.8s ease forwards 1.8s;
            text-align: center;
            letter-spacing: 1px;
        }

        @keyframes fillText { 0% { width: 0; } 100% { width: 100%; } }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .slide-up-exit { transform: translateY(-100%); }


        /* =========================================
           2. NAVBAR
           ========================================= */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.04);
            transition: all 0.4s ease;
            z-index: 1000;
        }

        .nav-link {
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0 10px;
            font-size: 1rem;
            position: relative;
        }
        @media (min-width: 992px) {
            .nav-link::before {
                content: '';
                position: absolute;
                bottom: 0; right: 0; width: 0; height: 3px;
                background: var(--gold-main);
                transition: 0.3s;
                border-radius: 2px;
            }
            .nav-link:hover::before, .nav-link.active::before { width: 100%; }
        }

        .nav-link-teacher {
            color: var(--gold-main) !important;
            border: 1px solid var(--gold-main);
            border-radius: 50px;
            padding: 6px 20px !important;
            margin-left: 10px;
            transition: 0.3s;
            font-weight: 700;
            display: inline-block;
            text-decoration: none;
        }
        .nav-link-teacher:hover { background: var(--gold-main); color: white !important; }

        .btn-nav-cta {
            background: var(--primary-dark); color: white; padding: 8px 25px; border-radius: 50px; font-weight: 700;
            box-shadow: 0 5px 15px rgba(26, 77, 46, 0.2); transition: 0.3s; border: none; font-size: 0.95rem; white-space: nowrap;
        }
        .btn-nav-cta:hover { transform: translateY(-2px); background: var(--gold-main); color: white; }

        .navbar-toggler { border: none; padding: 0; color: var(--primary-dark); font-size: 1.5rem; }
        .navbar-toggler:focus { box-shadow: none; }

        /* =========================================
           3. HERO SECTION
           ========================================= */
        .hero-section {
            padding-top: 140px; padding-bottom: 80px; position: relative; overflow: hidden; width: 100%;
        }
        .hero-bg-anim {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;
            background: radial-gradient(circle at 0% 0%, #f1fcf5 0%, transparent 60%), radial-gradient(circle at 100% 100%, #fffbf0 0%, transparent 60%);
        }
        .floating-shape {
            position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.5; animation: floatBubble 10s infinite alternate;
        }
        .shape-1 { width: 350px; height: 350px; background: #e0f2f1; top: -50px; left: -50px; }
        .shape-2 { width: 250px; height: 250px; background: #fff8e1; bottom: 0; right: -20px; animation-delay: -5s; }
        @keyframes floatBubble { 0% { transform: translate(0, 0); } 100% { transform: translate(30px, 30px); } }

        .hero-title {
            font-size: 3.5rem; font-weight: 900; line-height: 1.2; color: var(--primary-dark); margin-bottom: 20px;
            background: linear-gradient(to right, var(--primary-dark) 0%, var(--gold-main) 50%, var(--primary-dark) 100%);
            background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text;
            animation: shineText 5s linear infinite;
        }
        @keyframes shineText { to { background-position: 200% center; } }

        .hero-subtitle { font-size: 1.15rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 30px; max-width: 550px; }
        .hero-image-wrapper { position: relative; perspective: 1000px; margin-top: 10px; }
        .hero-phone {
            width: 100%; max-width: 360px; border-radius: 40px; box-shadow: 0 40px 80px rgba(26, 77, 46, 0.15);
            border: 8px solid white; animation: floatPhone 6s ease-in-out infinite; transform: rotateY(-10deg) rotateX(5deg);
        }
        @keyframes floatPhone { 0%, 100% { transform: translateY(0) rotateY(-10deg); } 50% { transform: translateY(-15px) rotateY(-5deg); } }

        .store-btn {
            display: flex; align-items: center; justify-content: center; padding: 10px 18px; border-radius: 14px; text-decoration: none; transition: 0.3s;
            border: 1px solid rgba(0,0,0,0.1); min-width: 150px;
        }
        .store-btn-dark { background: #1f2937; color: white; }
        .store-btn-dark:hover { background: black; transform: translateY(-4px); color: white; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
        .store-btn-light { background: white; color: #1f2937; }
        .store-btn-light:hover { background: #f9fafb; transform: translateY(-4px); color: #1f2937; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .store-btn-icon { font-size: 24px; margin-left: 10px; }
        .store-btn-text { display: flex; flex-direction: column; line-height: 1.1; text-align: left; }
        .store-btn-small { font-size: 10px; opacity: 0.8; }
        .store-btn-big { font-size: 15px; font-weight: 700; }

        .teacher-separator {
            display: flex; align-items: center; margin: 30px 0 15px 0; color: var(--gold-main); font-weight: 700; font-size: 0.9rem;
        }
        .teacher-separator::after { content: ''; flex: 1; height: 1px; background: rgba(196, 154, 70, 0.3); margin-right: 15px; }

        /* --- 4. NEW SECTIONS (Why & About) --- */

        /* Why Choose Section */
        .why-box {
            text-align: center;
            padding: 30px;
            height: 100%;
            transition: 0.3s;
        }
        .why-icon {
            width: 80px; height: 80px;
            background: var(--gold-light);
            color: var(--gold-main);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
            transition: 0.3s;
        }
        .why-box:hover .why-icon {
            background: var(--gold-main);
            color: white;
            transform: rotateY(180deg);
        }

        /* About Section */
        .about-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border-right: 4px solid var(--gold-main);
            height: 100%;
        }
        .about-icon-small {
            color: var(--primary-dark);
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        /* --- 5. Features, Packages, Teachers --- */
        .section-header { text-center: center; margin-bottom: 50px; }
        .section-tag { color: var(--gold-main); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 8px; font-size: 0.9rem; }
        .section-title { font-weight: 800; color: var(--primary-dark); font-size: 2.2rem; }

        .feature-box { background: white; padding: 35px 25px; border-radius: 24px; transition: 0.4s; border: 1px solid #f0f0f0; height: 100%; }
        .feature-box:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); border-color: var(--gold-main); }
        .icon-wrapper { width: 60px; height: 60px; border-radius: 18px; background: var(--primary-light); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px; transition: 0.4s; }
        .feature-box:hover .icon-wrapper { background: var(--primary-dark); color: var(--gold-main); transform: rotate(5deg); }

        .pricing-section { background-color: #fafafa; padding: 80px 0; }
        .pkg-card { background: white; border-radius: 28px; padding: 35px 25px; text-align: center; border: 1px solid #eee; transition: 0.3s; position: relative; height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
        .pkg-card.featured { background-color: var(--card-cream); border: 2px solid #fff; box-shadow: 0 20px 50px rgba(228, 178, 86, 0.15); transform: scale(1.05); z-index: 5; }
        .badge-popular { position: absolute; top: 25px; left: -30px; background: var(--primary-dark); color: var(--gold-main); padding: 5px 40px; font-weight: 800; font-size: 0.8rem; transform: rotate(-45deg); box-shadow: 0 5px 10px rgba(0,0,0,0.1); }
        .pkg-name { font-weight: 800; font-size: 1.3rem; color: var(--primary-dark); margin-bottom: 15px; }
        .pkg-gift { background: rgba(255,255,255,0.6); border-radius: 10px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #333; margin-bottom: 20px; font-size: 0.95rem; }
        .btn-3d { width: 100%; border: none; padding: 12px; border-radius: 14px; font-weight: 800; font-size: 1.05rem; position: relative; transition: 0.1s; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; }
        .btn-3d.orange { background: var(--btn-orange); color: white; box-shadow: 0 5px 0 var(--btn-orange-shadow); }
        .btn-3d.green { background: var(--primary-dark); color: white; box-shadow: 0 5px 0 #0f3d22; }
        .btn-3d.outline { background: white; color: var(--primary-dark); border: 2px solid var(--primary-dark); box-shadow: 0 5px 0 #e2e8f0; }
        .btn-3d:active { transform: translateY(5px); box-shadow: none !important; }
        .cursor-icon { position: absolute; bottom: -12px; left: 12px; width: 24px; filter: drop-shadow(0 3px 3px rgba(0,0,0,0.2)); animation: floatCursor 2.5s infinite ease-in-out; pointer-events: none; }

        .teacher-slide { padding: 10px; }
        .teacher-card { background: white; border-radius: 24px; padding: 25px 15px; text-align: center; box-shadow: 0 8px 25px rgba(0,0,0,0.03); border: 1px solid #f5f5f5; transition: 0.3s; }
        .teacher-card:hover { transform: translateY(-5px); border-color: var(--gold-main); }
        .teacher-img { width: 90px; height: 90px; border-radius: 50%; margin: 0 auto 12px; padding: 3px; background: linear-gradient(to bottom, var(--primary-dark), var(--gold-main)); }
        .teacher-img img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid white; }

        footer { background: var(--primary-dark); color: white; padding: 60px 0 25px; }

        /* Responsive Fixes */
        @media (max-width: 991px) {
            .navbar { padding: 8px 0; }
            .navbar-collapse { background: white; padding: 25px 20px; border-radius: 20px; margin-top: 15px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); text-align: center; }
            .nav-link { padding: 12px; border-bottom: 1px solid #f5f5f5; font-size: 1.1rem; }
            .nav-link::before { display: none; }
            .nav-link-teacher { display: block; width: 100%; margin: 20px 0 10px 0; padding: 10px !important; background: #fff8e1; color: var(--gold-main) !important; border: 1px solid var(--gold-main); }
            .btn-nav-cta { width: 100%; padding: 12px; margin-top: 10px; }
            .hero-section { padding-top: 100px; padding-bottom: 50px; text-align: center; }
            .hero-title { font-size: 2.2rem; margin-bottom: 15px; }
            .hero-subtitle { font-size: 1rem; margin: 0 auto 25px auto; padding: 0 10px; }
            .hero-btns-group { justify-content: center !important; gap: 10px !important; }
            .store-btn { flex: 1 1 auto; min-width: 130px; max-width: 180px; padding: 8px 12px; }
            .store-btn-icon { font-size: 20px; margin-left: 6px; }
            .store-btn-big { font-size: 12px; }
            .teacher-separator { justify-content: center; font-size: 0.85rem; margin: 30px 0 15px; }
            .teacher-separator::after { display: none; }
            .hero-image-wrapper { margin-top: 40px; }
            .hero-phone { max-width: 260px; box-shadow: 0 15px 30px rgba(0,0,0,0.1); animation: none !important; transform: none !important; }
            .hero-bg-anim .floating-shape { display: none; }
            .pkg-card.featured { transform: none; margin: 15px 0; z-index: 1; }
            .badge-popular { top: -15px; left: 50%; transform: translateX(-50%) rotate(0deg); padding: 5px 20px; border-radius: 20px; width: auto; }
        }
    </style>
</head>
<body>

    <div id="loader-screen">
        <div class="loader-container">
            <h1 class="loader-brand" data-text="ورتل">ورتل</h1>
            <div class="loader-sub">رتل القرآن ترتيلاً</div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><img width="50px" height="50px" src="{{ asset('images/wrtlv3.png') }}" alt="ورتل"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="#">الرئيسية</a></li>
                    <li class="nav-item"><a class="nav-link" href="#why-us">لماذا ورتل</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">عن التطبيق</a></li>
                    <li class="nav-item"><a class="nav-link" href="#packages">الأسعار</a></li>
                </ul>
                <div class="d-flex flex-column flex-lg-row align-items-center">
                    <a href="{{ route('teacher.index') }}" class="nav-link-teacher"><i class="fa-solid fa-chalkboard-user ms-1"></i> انضم كمعلم</a>
                    <button class="btn-nav-cta">حمل التطبيق <i class="fa-solid fa-arrow-down-long ms-2"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section d-flex align-items-center">
        <div class="hero-bg-anim"><div class="floating-shape shape-1"></div><div class="floating-shape shape-2"></div></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div data-aos="fade-up" data-aos-delay="0"><div class="d-inline-block px-3 py-2 rounded-pill bg-white shadow-sm mb-3 border border-1"><span class="fw-bold text-success small"><i class="fa-solid fa-star me-2 text-warning"></i> التطبيق الإسلامي الأول</span></div></div>
                    <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">احفظ القرآن الكريم<br>بإتقان وسند متصل</h1>
                    <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="400">ابدأ رحلتك مع كتاب الله الآن برفقة نخبة من المعلمون والمعلمات المجازون بالسند المتصل</p>
                    <div class="d-flex hero-btns-group mb-2" data-aos="fade-up" data-aos-delay="600">
                        <a href="#" class="store-btn store-btn-dark"><i class="fab fa-apple store-btn-icon"></i><div class="store-btn-text"><span class="store-btn-small">Download on the</span><span class="store-btn-big">App Store</span></div></a>
                        <a href="#" class="store-btn store-btn-light"><i class="fab fa-google-play store-btn-icon text-success"></i><div class="store-btn-text"><span class="store-btn-small">GET IT ON</span><span class="store-btn-big">Google Play</span></div></a>
                    </div>
                    <div class="teacher-separator" data-aos="fade-in" data-aos-delay="800"><i class="fa-solid fa-user-tie ms-2"></i> نسخة المعلمين</div>
                    <div class="d-flex hero-btns-group" data-aos="fade-up" data-aos-delay="900">
                        <a href="#" class="store-btn store-btn-dark" style="background: var(--primary-dark); border: 1px solid transparent;"><i class="fab fa-apple store-btn-icon"></i><div class="store-btn-text"><span class="store-btn-small">تطبيق المعلم</span><span class="store-btn-big">App Store</span></div></a>
                        <a href="#" class="store-btn store-btn-light" style="border-color: var(--primary-dark); color: var(--primary-dark);"><i class="fab fa-google-play store-btn-icon"></i><div class="store-btn-text"><span class="store-btn-small">تطبيق المعلم</span><span class="store-btn-big">Google Play</span></div></a>
                    </div>
                    <div class="mt-4" data-aos="fade-in" data-aos-delay="1000"><a href="{{ route('teacher.index') }}" class="text-decoration-none fw-bold small text-muted">هل ترغب في الانضمام لفريقنا؟ <span style="color: var(--gold-main); text-decoration: underline;">قدم طلبك الآن <i class="fa-solid fa-arrow-left"></i></span></a></div>
                </div>
                <div class="col-lg-6 text-center" data-aos="zoom-in" data-aos-duration="1200" data-aos-delay="300">
                    <div class="hero-image-wrapper"><img src="{{ asset('images/main.jpeg') }}" alt="App UI" class="hero-phone"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="why-us" class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">لماذا نحن؟</span>
                <h2 class="section-title">تجربة تعليمية فريدة</h2>
                <p class="text-muted">نجمع بين الأصالة والتقنية الحديثة لتحقيق أهدافك</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-clock"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">على مدار الساعة</h4>
                        <p class="text-muted">جلسات مباشرة بالصوت أو الفيديو متاحة لك في أي وقت، 24 ساعة يومياً.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-users-rays"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">مختلف الأعمار</h4>
                        <p class="text-muted">مهما كان عمرك أو مستواك، ستجد معلماً متخصصاً ومنهجاً يناسبك.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-calendar-check"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">خطط مرنة</h4>
                        <p class="text-muted">اختر خطتك ومسارك التعليمي المفضل بلا قيود وبحسب وقت فراغك.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-5" style="background-color: #fcfcfc;">
        <div class="container py-4">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">مسارات التعلم</span>
                <h2 class="section-title">منهجية متكاملة للإتقان</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100"><div class="feature-box"><div class="icon-wrapper"><i class="fa-solid fa-microphone-lines"></i></div><h4 class="fw-bold mb-3">المصحح الذكي</h4><p class="text-muted">تعرف فوري على الأخطاء في التلاوة باستخدام تقنيات AI المتطورة.</p></div></div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200"><div class="feature-box"><div class="icon-wrapper"><i class="fa-solid fa-user-group"></i></div><h4 class="fw-bold mb-3">حلقات مباشرة</h4><p class="text-muted">تواصل بالصوت والصورة مع نخبة من المعلمين المجازين لتصحيح التلاوة.</p></div></div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300"><div class="feature-box"><div class="icon-wrapper"><i class="fa-solid fa-medal"></i></div><h4 class="fw-bold mb-3">الإجازة والسند</h4><p class="text-muted">مسار خاص للمتقدمين لختم القرآن كاملاً والحصول على إجازة بسند متصل.</p></div></div>
            </div>
        </div>
    </section>

    <section id="about" class="py-5" style="background: var(--primary-light);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-left">
                    <span class="section-tag">من نحن</span>
                    <h2 class="section-title mb-4" style="text-align: right;">عن تطبيق ورتل</h2>
                    <p class="text-muted lead" style="line-height: 1.8;">
                        تطبيق "ورتل" هو منصة تفاعلية رائدة لتعليم القرآن الكريم. نجمع بين أحدث التقنيات وأفضل الكفاءات التعليمية لتمكين المسلمين حول العالم من تلاوة وحفظ كتاب الله.
                    </p>
                    <p class="text-muted">يتيح لك التطبيق القراءة على مقرئين مجازين، وتكرار الآيات، واستخدام أدوات ذكية للمراجعة وتتبع التقدم.</p>
                </div>
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="about-card">
                                <i class="fa-solid fa-bullseye about-icon-small"></i>
                                <h5 class="fw-bold text-success">هدفنا</h5>
                                <p class="text-muted m-0 small">تمكين أي شخص، في أي عمر ومكان، من تعلم القرآن بسهولة وإتقان مع أفضل المعلمين.</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="about-card">
                                <i class="fa-solid fa-envelope-open-text about-icon-small"></i>
                                <h5 class="fw-bold text-success">رسالتنا</h5>
                                <p class="text-muted m-0 small">إيصال القرآن إلى قلوب المسلمين عبر تعليم متقن يربط الأجيال بكلام الله حفظاً وتدبراً.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="packages" class="pricing-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">الباقات</span>
                <h2 class="section-title">اختر خطتك التعليمية</h2>
            </div>
            <div class="row g-4 justify-content-center align-items-center">
                <div class="col-lg-4 col-md-6" data-aos="fade-right">
                    <div class="pkg-card">
                        <div><h4 class="pkg-name">باقة التلاوة</h4><div class="pkg-gift bg-light"><i class="fa-solid fa-gift text-warning"></i> 100 دقيقة</div><ul class="list-unstyled text-muted text-start mx-auto small" style="max-width: 200px;"><li class="mb-3"><i class="fa-solid fa-check text-success me-2"></i> صلاحية 30 يوم</li><li class="mb-3"><i class="fa-solid fa-check text-success me-2"></i> تصحيح آلي</li></ul></div><button class="btn-3d outline">250 <span class="small ms-1">EGP</span></button>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="zoom-in">
                    <div class="pkg-card featured">
                        <div class="badge-popular">الأكثر طلباً</div>
                        <div><h4 class="pkg-name">باقة البداية</h4><div class="pkg-gift"><i class="fa-solid fa-gift text-danger fa-lg"></i><span class="fs-6 ms-2">150 د + 30 د</span></div><p class="small text-muted mb-4 fw-bold">مثالية للبدء في الحفظ والمراجعة</p></div><button class="btn-3d orange"><span>470.31</span> <small>EGP</small><span class="text-decoration-line-through text-white-50 ms-2 small">940</span><svg class="cursor-icon" viewBox="0 0 24 24" fill="white"><path d="M5.5 2L18.5 13.5L12 14.5L16 22L13 23.5L9 16L3 18V2Z" stroke="black" stroke-width="1.5"/></svg></button>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-left">
                    <div class="pkg-card">
                        <div><h4 class="pkg-name text-success">باقة الإتقان VIP</h4><div class="pkg-gift bg-light"><i class="fa-solid fa-crown text-warning"></i> 300 د + 60 د</div><ul class="list-unstyled text-muted text-start mx-auto small" style="max-width: 200px;"><li class="mb-3"><i class="fa-solid fa-check text-success me-2"></i> صلاحية 60 يوم</li><li class="mb-3"><i class="fa-solid fa-check text-success me-2"></i> معلم خاص</li></ul></div><button class="btn-3d green">850 <span class="small ms-1">EGP</span></button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="teachers" class="py-5">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-end mb-5"><div><span class="section-tag">الكادر التعليمي</span><h2 class="section-title m-0">نخبة من العلماء</h2></div></div>
            <div class="owl-carousel teachers-carousel owl-theme">
                <div class="teacher-slide"><div class="teacher-card"><div class="teacher-img"><img src="{{ asset('images/a1.jpg.webp') }}" alt="Sheikh"></div><h5 class="fw-bold mb-1">الشيخ أحمد</h5><p class="text-muted small">القراءات العشر</p><div class="text-warning small"><i class="fa-solid fa-star"></i> 4.9</div></div></div>
                <div class="teacher-slide"><div class="teacher-card"><div class="teacher-img"><img src="{{ asset('images/a2.webp') }}" alt="Sheikh"></div><h5 class="fw-bold mb-1">الشيخ علي </h5><p class="text-muted small">أطفال ونساء</p><div class="text-warning small"><i class="fa-solid fa-star"></i> 5.0</div></div></div>
                <div class="teacher-slide"><div class="teacher-card"><div class="teacher-img"><img src="{{ asset('images/a3.webp') }}" alt="Sheikh"></div><h5 class="fw-bold mb-1">الشيخ محمد</h5><p class="text-muted small">إجازة حفص</p><div class="text-warning small"><i class="fa-solid fa-star"></i> 4.8</div></div></div>
                <div class="teacher-slide"><div class="teacher-card"><div class="teacher-img"><img src="{{ asset('images/a4.jpg') }}" alt="Sheikh"></div><h5 class="fw-bold mb-1">الشيخ يوسف</h5><p class="text-muted small">المقامات</p><div class="text-warning small"><i class="fa-solid fa-star"></i> 4.9</div></div></div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container text-center">
            <img src="image_1.png" alt="Logo" style="height: 60px; filter: brightness(0) invert(1); margin-bottom: 30px;">
            <div class="d-flex justify-content-center gap-4 mb-4">
                <a href="#" class="text-white opacity-75 fs-5"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="text-white opacity-75 fs-5"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" class="text-white opacity-75 fs-5"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="text-white opacity-75 fs-5"><i class="fa-brands fa-youtube"></i></a>
            </div>
            <p class="small opacity-50 m-0">جميع الحقوق محفوظة © ورتل 2026</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <script>
        // ADD NO SCROLL ON LOAD
        document.body.classList.add('loading-locked');

        $(document).ready(function(){
            // Loader Exit Logic
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');
                // REMOVE LOCK WHEN EXIT STARTS
                document.body.classList.remove('loading-locked');
            }, 3000);

            // Init Animations
            AOS.init({ once: true, offset: 50, duration: 800 });

            // Init Teachers Carousel
            $(".teachers-carousel").owlCarousel({
                rtl: true, loop: true, margin: 0, nav: false, dots: true,
                autoplay: true, autoplayTimeout: 3000, autoplayHoverPause: true, smartSpeed: 800,
                responsive:{ 0:{ items: 1 }, 600:{ items: 2 }, 1000:{ items: 3 }, 1200:{ items: 4 } }
            });
        });
    </script>
</body>
</html>
