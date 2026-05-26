<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>السياسات ومعلومات التواصل | ورتل</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap');

        :root {
            /* Brand Identity - مستوحاة مباشرة من الشعار */
            --primary-dark: #2d8a74;
            --primary-medium: #4fb299;
            --primary-light: #f0f9f7;
            --gold-main: #d4a753;
            --gold-light: #fdf5e6;

            --bg-body: #fcfcfc;
            --text-main: #1e4d42;
            --text-muted: #6a8d85;
        }

        html, body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden !important;
            scroll-behavior: smooth;
        }

        /* =========================================
           2. NAVBAR (من كودك)
           ========================================= */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
        }

        .navbar-brand img {
            height: 50px;
        }

        .back-link {
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 700;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-link:hover {
            color: var(--gold-main);
            transform: translateX(5px); /* التحريك للوراء في RTL */
        }

        /* =========================================
           3. MAIN CONTENT CUSTOM STYLES
           ========================================= */
        main {
            padding-top: 120px; /* لتعويض مساحة النافبار الثابت */
        }

        .policy-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .policy-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }

        /* =========================================
           5. PROFESSIONAL MEGA FOOTER (من كودك)
           ========================================= */
        footer {
            background-color: #0f2922; /* Darker than primary-dark */
            color: #ecf0f1;
            padding: 80px 0 30px;
            font-size: 0.95rem;
            text-align: right;
            margin-top: 50px;
        }

        .footer-logo {
            height: 70px;
            margin-bottom: 25px;
        }

        .footer-desc {
            color: #bdc3c7;
            margin-bottom: 30px;
            line-height: 1.8;
            max-width: 90%;
        }

        .footer-title {
            color: var(--gold-main);
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.1rem;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 3px;
            background: var(--gold-main);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            text-decoration: none;
            color: #d1d5db;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .footer-links a:hover {
            color: var(--gold-main);
            transform: translateX(-5px); /* التحريك للوراء في RTL */
        }

        .footer-links a i {
            font-size: 12px;
            margin-left: 8px;
            color: var(--gold-main);
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            margin-left: 10px;
            transition: 0.3s;
            font-size: 1.1rem;
            text-decoration: none;
        }

        .social-icons a:hover {
            background: var(--gold-main);
            transform: translateY(-3px);
        }

        .newsletter-form {
            position: relative;
            margin-top: 20px;
        }

        .newsletter-form input {
            width: 100%;
            padding: 12px 20px;
            padding-left: 50px;
            border-radius: 50px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .newsletter-form input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
        }

        .newsletter-form button {
            position: absolute;
            top: 5px;
            left: 5px;
            height: 38px;
            width: 38px;
            border-radius: 50%;
            border: none;
            background: var(--gold-main);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: 0.3s;
        }

        .newsletter-form button:hover {
            background: white;
            color: var(--gold-main);
        }

        .copyright {
            margin-top: 60px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #7f8c8d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <nav class="navbar fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('welcome') }}">
                <img src="{{ asset('images/mainlogo.png') }}" alt="ورتل">
            </a>
            <a href="{{ route('welcome') }}" class="back-link">
                العودة للرئيسية <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    </nav>
<br>
<br>
    <main class="container mx-auto px-4 lg:py-16">

        <div class="text-center mb-16 space-y-3">
            <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight" style="color: var(--primary-dark)">السياسات ومعلومات التواصل</h1>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">كل ما تحتاج معرفته عن استخدام منصة ورتل لتعليم القرآن الكريم، وسياسات حماية حقوقك.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

            <div class="lg:col-span-8 space-y-8">

                <section class="policy-card bg-white p-8 lg:p-10 rounded-2xl border border-gray-100 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center border-b border-gray-100 pb-4">
                        <div class="p-2.5 rounded-xl ml-4" style="background-color: var(--primary-light); color: var(--primary-dark);">
                            <i class="fa-solid fa-file-signature text-xl"></i>
                        </div>
                        شروط الاستخدام (Terms & Conditions)
                    </h2>
                    <div class="text-gray-600 leading-loose space-y-4 text-base lg:text-lg">
                        <p>مرحباً بك في منصة ورتل. باستخدامك للمنصة، فإنك توافق على الالتزام بالشروط والأحكام التالية:</p>
                        <ul class="list-disc list-inside space-y-3 pr-4 marker:text-[var(--primary-dark)]">
                            <li>يُقر المستخدم بأن جميع البيانات المدخلة أثناء عملية التسجيل صحيحة ودقيقة.</li>
                            <li>يُمنع منعاً باتاً استخدام المنصة لأي أغراض خارج إطار التعليم وتلاوة القرآن الكريم.</li>
                            <li>تحتفظ إدارة المنصة بالحق الكامل في إيقاف أو حظر أي حساب يخالف شروط الاستخدام أو يسيء للمعلمين دون سابق إنذار.</li>
                        </ul>
                    </div>
                </section>

                <section class="policy-card bg-white p-8 lg:p-10 rounded-2xl border border-gray-100 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center border-b border-gray-100 pb-4">
                        <div class="p-2.5 rounded-xl ml-4" style="background-color: var(--primary-light); color: var(--primary-dark);">
                            <i class="fa-solid fa-shield-halved text-xl"></i>
                        </div>
                        سياسة الخصوصية (Privacy Policy)
                    </h2>
                    <div class="text-gray-600 leading-loose space-y-4 text-base lg:text-lg">
                        <p>نحن في ورتل نولي أهمية قصوى لخصوصية بياناتك وحمايتها بأحدث التقنيات:</p>
                        <ul class="list-disc list-inside space-y-3 pr-4 marker:text-[var(--primary-dark)]">
                            <li>لا يتم مشاركة بياناتك الشخصية، رقم هاتفك، أو بريدك الإلكتروني مع أي جهات خارجية إطلاقاً.</li>
                            <li>يتم تسجيل الجلسات التعليمية سحابياً لأغراض المراجعة الشخصية للطالب وضمان جودة التعليم، وتحفظ بشكل آمن ومشفر.</li>
                            <li>تتم معالجة جميع المدفوعات عبر بوابات دفع معتمدة وآمنة، ونحن <span class="font-bold underline decoration-[var(--primary-dark)] underline-offset-4">لا نحتفظ</span> بأي تفاصيل لبطاقتك الائتمانية على خوادمنا.</li>
                        </ul>
                    </div>
                </section>

                <section class="policy-card bg-white p-8 lg:p-10 rounded-2xl border border-gray-100 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center border-b border-gray-100 pb-4">
                        <div class="p-2.5 rounded-xl ml-4" style="background-color: var(--primary-light); color: var(--primary-dark);">
                            <i class="fa-solid fa-money-bill-transfer text-xl"></i>
                        </div>
                        سياسة الإلغاء والاسترجاع (Cancellation & Refund)
                    </h2>
                    <div class="text-gray-600 leading-loose space-y-4 text-base lg:text-lg">
                        <p>حرصاً منا على حفظ حقوق الطلاب والمعلمين، نطبق سياسة استرجاع عادلة وشفافة:</p>
                        <ul class="list-disc list-inside space-y-3 pr-4 marker:text-[var(--primary-dark)]">
                            <li><strong>إلغاء الطالب:</strong> يُسمح للطالب بإلغاء الحجز المجدول قبل موعد الجلسة بـ <span class="font-bold text-[var(--primary-dark)] bg-[var(--primary-light)] px-2 py-0.5 rounded">90 دقيقة</span> على الأقل، وفي هذه الحالة يتم استرجاع الدقائق المخصومة تلقائياً إلى رصيده.</li>
                            <li><strong>إلغاء المعلم:</strong> لا يُسمح للمعلم بإلغاء الحصة قبل أقل من 12 ساعة من موعدها. وفي حال الإلغاء ضمن المسموح، يُعاد الرصيد كاملاً للطالب.</li>
                            <li><strong>تغيب المعلم:</strong> في حال تأخر المعلم أو عدم حضوره للجلسة، يقوم النظام التلقائي برصد ذلك وإرجاع الدقائق لرصيد الطالب بالكامل لضمان حقه.</li>
                        </ul>
                    </div>
                </section>

            </div>

            <aside class="lg:col-span-4">
                <div class="bg-white p-8 rounded-2xl border-t-4 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] sticky top-[120px]" style="border-color: var(--primary-dark);">
                    <h3 class="text-xl font-bold text-gray-900 mb-8 border-b border-gray-100 pb-4">معلومات التواصل السريع</h3>

                    <div class="flex items-start mb-8 group cursor-pointer">
                        <div class="p-3.5 rounded-xl transition-colors duration-300 ml-4" style="background-color: var(--primary-light); color: var(--primary-dark);">
                            <i class="fa-solid fa-envelope text-xl"></i>
                        </div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-gray-400 mb-0.5">البريد الإلكتروني للعملاء</p>
                            <a href="mailto:info@wartil.com" class="text-lg font-bold transition block" style="color: var(--text-main);" dir="ltr">info@wartil.com</a>
                        </div>
                    </div>

                    <div class="flex items-start mb-8 group cursor-pointer">
                        <div class="p-3.5 rounded-xl transition-colors duration-300 ml-4" style="background-color: var(--primary-light); color: var(--primary-dark);">
                            <i class="fa-solid fa-phone-volume text-xl"></i>
                        </div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-gray-400 mb-0.5">الدعم الفني والواتساب</p>
                            <a href="tel:+201033882191" class="text-lg font-bold transition block" style="color: var(--text-main);" dir="ltr">+20 103 388 2191</a>
                        </div>
                    </div>

                    <div class="flex items-start group">
                        <div class="p-3.5 rounded-xl transition-colors duration-300 ml-4" style="background-color: var(--primary-light); color: var(--primary-dark);">
                            <i class="fa-solid fa-location-dot text-xl"></i>
                        </div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-gray-400 mb-0.5">المقر الرئيسي</p>
                            <p class="text-lg font-bold leading-snug" style="color: var(--text-main);">القاهرة، جمهورية مصر العربية</p>
                        </div>
                    </div>

                </div>
            </aside>

        </div>
    </main>

    <footer>
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-4 col-md-6">
                    <img src="{{ asset('images/mainlogo.png') }}" alt="Logo" class="footer-logo">
                    <p class="footer-desc">ورتل.. رفيقك في رحلة تعلم القرآن الكريم. نسعى لربط المسلمين بكتاب الله عبر تقنيات حديثة وكوادر تعليمية مؤهلة.</p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/share/1HZASt1L9h/?mibextid=wwXIfr" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/wartil20?igsh=MWxidnk0cjl4YXpwNw==" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">خريطة الموقع</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-chevron-left text-xs"></i> الرئيسية</a></li>
                        <li><a href="#about"><i class="fa-solid fa-chevron-left text-xs"></i> عن التطبيق</a></li>
                        <li><a href="#packages"><i class="fa-solid fa-chevron-left text-xs"></i> الأسعار</a></li>
                        <li><a href="{{ route('teacher.index') }}"><i class="fa-solid fa-chevron-left text-xs"></i> انضم كمعلم</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">المساعدة</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('policies') }}"><i class="fa-solid fa-chevron-left text-xs"></i> سياسة الخصوصية</a></li>
                        {{-- <li><a href="{{ route('policies') }}"><i class="fa-solid fa-chevron-left text-xs"></i> الشروط والأحكام</a></li>
                        <li><a href="{{ route('policies') }}"><i class="fa-solid fa-chevron-left text-xs"></i> الإلغاء والاسترجاع</a></li>
                        <li><a href="{{ route('policies') }}"><i class="fa-solid fa-chevron-left text-xs"></i> اتصل بنا</a></li> --}}
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">اشترك في النشرة</h5>
                    <p class="text-white-50 small mb-3">احصل على أحدث المقالات والنصائح القرآنية.</p>
                    <form class="position-relative mb-4">
                        <input type="email" class="form-control bg-dark border-secondary text-white rounded-pill ps-4" placeholder="البريد الإلكتروني">
                        <button class="btn position-absolute top-0 start-0 h-100 text-warning pe-3"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>

            <div class="copyright">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-end">
                        &copy; {{ date('Y') }} جميع الحقوق محفوظة لـ <strong>ورتل</strong>.
                    </div>
                    <div class="col-md-6 text-center text-md-start mt-2 mt-md-0">
                        تم التطوير بكل ❤️ لخدمة القرآن الكريم
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
