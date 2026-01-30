<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انضم كمعلم - ورتل</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

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

            --btn-orange: #ffab40;
            --btn-orange-shadow: #e69123;
        }

        /* --- Global Reset & Lock --- */
        html, body {
            font-family: 'Cairo', sans-serif;
            background-color: #fcfcfc;
            color: var(--text-main);
            overflow-x: hidden !important;
            width: 100%;
        }
        body.loading-locked { overflow: hidden !important; height: 100vh; }

        /* =========================================
           1. LOADER (Consistent Style)
           ========================================= */
        #loader-screen {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #ffffff; z-index: 9999999;
            display: flex; justify-content: center; align-items: center;
            transition: transform 0.9s cubic-bezier(0.77, 0, 0.175, 1);
        }
        .loader-brand {
            font-size: clamp(3rem, 10vw, 5rem); font-weight: 900; line-height: 1;
            position: relative; color: #f3f3f3; margin: 0; letter-spacing: -2px;
        }
        .loader-brand::before {
            content: attr(data-text); position: absolute; top: 0; left: 0; width: 0; height: 100%;
            color: var(--primary-dark); border-right: 4px solid var(--gold-main);
            overflow: hidden; animation: fillText 2s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            white-space: nowrap;
        }
        .loader-sub {
            margin-top: 10px; font-size: clamp(0.9rem, 3vw, 1.2rem); color: var(--gold-main);
            font-weight: 700; opacity: 0; transform: translateY(15px);
            animation: fadeUp 0.8s ease forwards 1.5s; text-align: center;
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
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        .navbar-brand img { height: 45px; }
        .back-link {
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 700;
            transition: 0.3s;
            display: flex; align-items: center; gap: 8px;
        }
        .back-link:hover { color: var(--gold-main); transform: translateX(5px); }

        /* =========================================
           3. FORM HEADER
           ========================================= */
        .page-header {
            padding: 120px 0 50px;
            text-align: center;
            background: linear-gradient(180deg, #fff 0%, #fcfcfc 100%);
        }
        .page-title {
            font-size: 2.5rem; font-weight: 900; color: var(--primary-dark);
            margin-bottom: 10px;
        }
        .page-subtitle { color: var(--text-muted); max-width: 600px; margin: 0 auto; }

        /* =========================================
           4. REGISTRATION FORM STYLES
           ========================================= */
        .reg-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.02);
            margin-bottom: 80px;
        }

        .section-label {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-label::before {
            content: ''; width: 5px; height: 25px;
            background: var(--gold-main); border-radius: 5px;
        }

        /* Input Styling */
        .form-floating > .form-control,
        .form-floating > .form-select {
            border-radius: 15px;
            border: 1px solid #eee;
            background-color: #fdfdfd;
            height: 60px;
        }
        .form-floating > .form-control:focus,
        .form-floating > .form-select:focus {
            border-color: var(--gold-main);
            box-shadow: 0 0 0 4px rgba(196, 154, 70, 0.1);
        }
        .form-floating > label {
            padding-right: 20px; color: #999;
        }

        /* --- TRACK SELECTION CARDS --- */
        .track-option { display: none; }
        .track-card {
            cursor: pointer;
            border: 2px solid #eee;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            background: white;
            position: relative;
            overflow: hidden;
        }
        .track-card:hover { transform: translateY(-5px); border-color: #ddd; }
        .track-icon {
            width: 60px; height: 60px;
            background: var(--primary-light); color: var(--primary-dark);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin: 0 auto 15px; transition: 0.3s;
        }
        .track-title { font-weight: 800; color: var(--primary-dark); margin-bottom: 5px; }
        .track-desc { font-size: 0.85rem; color: #777; line-height: 1.4; }

        .track-option:checked + .track-card {
            border-color: var(--gold-main); background-color: var(--gold-light);
            box-shadow: 0 10px 20px rgba(196, 154, 70, 0.15);
        }
        .track-option:checked + .track-card .track-icon { background-color: var(--gold-main); color: white; }
        .check-badge {
            position: absolute; top: 15px; right: 15px; color: var(--gold-main);
            opacity: 0; transition: 0.3s; transform: scale(0);
        }
        .track-option:checked + .track-card .check-badge { opacity: 1; transform: scale(1); }

        /* --- FILE UPLOAD --- */
        .file-upload-box {
            border: 2px dashed #ddd; border-radius: 15px; padding: 30px;
            text-align: center; cursor: pointer; transition: 0.3s; background: #fafafa;
        }
        .file-upload-box:hover { border-color: var(--primary-dark); background: #f0fdf4; }
        .file-icon { font-size: 2rem; color: #aaa; margin-bottom: 10px; }
        .file-text { font-weight: 600; color: var(--text-main); display: block; }
        .file-sub { font-size: 0.8rem; color: #999; }

        /* --- SUBMIT BUTTON --- */
        .btn-submit {
            width: 100%; border: none; padding: 18px; border-radius: 16px;
            font-weight: 800; font-size: 1.2rem; background: var(--primary-dark);
            color: white; box-shadow: 0 6px 0 #0f3d22; transition: 0.1s; margin-top: 20px;
        }
        .btn-submit:hover { background: #143d24; transform: translateY(-2px); }
        .btn-submit:active { transform: translateY(6px); box-shadow: none; }

        footer { background: var(--primary-dark); color: white; padding: 40px 0; text-align: center; }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header { padding-top: 100px; }
            .reg-card { padding: 25px 15px; border-radius: 20px; }
            .section-label { font-size: 1rem; }
        }
    </style>
</head>
<body>

    <div id="loader-screen">
        <div class="loader-container">
            <h1 class="loader-brand" data-text="ورتل">ورتل</h1>
            <div class="loader-sub">انضم لنخبة المعلمين</div>
        </div>
    </div>

    <nav class="navbar fixed-top">
        <div class="container">
            <a  href="#"><img  width="50px" height="50px" src="{{ asset('images/wrtlv3.png') }}" alt="ورتل"></a>
            <a href="index.html" class="back-link">
                العودة للرئيسية <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1 class="page-title" data-aos="fade-up">استمارة تسجيل معلم</h1>
            <p class="page-subtitle" data-aos="fade-up" data-aos-delay="100">
                انضم إلى فريق "ورتل" وساهم في تعليم القرآن الكريم للطلاب حول العالم. املأ البيانات التالية بدقة.
            </p>
        </div>
    </section>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <form class="reg-card" data-aos="fade-up" data-aos-delay="200">

                    <h4 class="section-label">البيانات الشخصية</h4>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="fullName" placeholder="الاسم" required>
                                <label for="fullName">الاسم الرباعي</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                                <label for="email">البريد الإلكتروني</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phone" placeholder="رقم الهاتف" required style="direction: ltr; text-align: right;">
                                <label for="phone">رقم الهاتف (مع كود الدولة)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="gender">
                                    <option selected>اختر النوع</option>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                                <label for="gender">النوع</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="countrySelect">
                                    <option selected disabled>جاري تحميل الدول...</option>
                                </select>
                                <label for="countrySelect">الدولة / المدينة</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="age" placeholder="العمر">
                                <label for="age">العمر</label>
                            </div>
                        </div>
                    </div>

                    <h4 class="section-label">المؤهلات القرآنية</h4>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="form-floating">
                                <select class="form-select" id="hifzStatus">
                                    <option selected>مقدار الحفظ</option>
                                    <option value="1">خاتم للقرآن كاملاً</option>
                                    <option value="2">20 جزء فأكثر</option>
                                    <option value="3">10 أجزاء فأكثر</option>
                                    <option value="4">أقل من 10 أجزاء</option>
                                </select>
                                <label for="hifzStatus">حالة الحفظ</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="ijazah">
                                    <option selected>هل لديك إجازة؟</option>
                                    <option value="1">نعم، إجازة بالسند المتصل</option>
                                    <option value="2">لا</option>
                                </select>
                                <label for="ijazah">الإجازة</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="riwayah" placeholder="الرواية">
                                <label for="riwayah">الروايات التي تتقنها (مثال: حفص، ورش)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" placeholder="خبرات سابقة" id="experience" style="height: 100px"></textarea>
                                <label for="experience">نبذة عن خبرتك في التعليم (سنوات الخبرة، أونلاين/أوفلاين)</label>
                            </div>
                        </div>
                    </div>

                    <h4 class="section-label">مسار التدريس المفضل <span class="text-danger">*</span></h4>
                    <p class="text-muted small mb-3">يمكنك اختيار أكثر من مسار إذا كنت مؤهلاً لذلك</p>

                    <div class="row g-3 mb-5">
                        <div class="col-md-4">
                            <input type="checkbox" class="track-option" name="track" id="track1" value="correction">
                            <label class="track-card" for="track1">
                                <i class="fa-solid fa-circle-check check-badge"></i>
                                <div class="track-icon"><i class="fa-solid fa-microphone-lines"></i></div>
                                <h5 class="track-title">مسار التصحيح</h5>
                                <p class="track-desc">تصحيح التلاوة وضبط المخارج والصفات للطلاب.</p>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="checkbox" class="track-option" name="track" id="track2" value="memorization">
                            <label class="track-card" for="track2">
                                <i class="fa-solid fa-circle-check check-badge"></i>
                                <div class="track-icon"><i class="fa-solid fa-book-open-reader"></i></div>
                                <h5 class="track-title">مسار التحفيظ</h5>
                                <p class="track-desc">متابعة حفظ الطلاب الجديد والمراجعة وفق خطة زمنية.</p>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="checkbox" class="track-option" name="track" id="track3" value="ijazah">
                            <label class="track-card" for="track3">
                                <i class="fa-solid fa-circle-check check-badge"></i>
                                <div class="track-icon"><i class="fa-solid fa-certificate"></i></div>
                                <h5 class="track-title">مسار الإجازة</h5>
                                <p class="track-desc">الإقراء لمنح الإجازة بالسند المتصل (للمجازين فقط).</p>
                            </label>
                        </div>
                    </div>

                    <h4 class="section-label">المرفقات ونموذج التلاوة</h4>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="file-upload-box w-100" for="cvUpload">
                                <input type="file" id="cvUpload" hidden accept=".pdf,.doc,.docx">
                                <i class="fa-solid fa-file-pdf file-icon"></i>
                                <span class="file-text">ارفع السيرة الذاتية (CV)</span>
                                <span class="file-sub">PDF, DOC (Max 5MB)</span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="file-upload-box w-100" for="audioUpload">
                                <input type="file" id="audioUpload" hidden accept="audio/*">
                                <i class="fa-solid fa-microphone file-icon text-success"></i>
                                <span class="file-text">ارفع تسجيل صوتي لتلاوتك</span>
                                <span class="file-sub">MP3, WAV (دقيقة واحدة كحد أقصى)</span>
                            </label>
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <button type="submit" class="btn-submit">إرسال طلب الانضمام</button>
                        <p class="text-muted small mt-2">سيقوم فريقنا بمراجعة طلبك والتواصل معك في أقرب وقت.</p>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="small opacity-50 m-0">جميع الحقوق محفوظة © ورتل 2026</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Lock Scroll on Load
        document.body.classList.add('loading-locked');

        $(document).ready(function(){
            // Loader Exit
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');
                document.body.classList.remove('loading-locked');
            }, 2500);

            // Init AOS
            AOS.init({ once: true, offset: 50, duration: 800 });

            // --- COUNTRY API LOGIC (Frontend) ---
            const countryList = [
                { "name": "مصر", "code": "EG", "dial_code": "+20" },
                { "name": "السعودية", "code": "SA", "dial_code": "+966" },
                { "name": "الإمارات", "code": "AE", "dial_code": "+971" },
                { "name": "الكويت", "code": "KW", "dial_code": "+965" },
                { "name": "قطر", "code": "QA", "dial_code": "+974" },
                { "name": "البحرين", "code": "BH", "dial_code": "+973" },
                { "name": "عمان", "code": "OM", "dial_code": "+968" },
                { "name": "الأردن", "code": "JO", "dial_code": "+962" },
                { "name": "المغرب", "code": "MA", "dial_code": "+212" },
                { "name": "الجزائر", "code": "DZ", "dial_code": "+213" },
                { "name": "تونس", "code": "TN", "dial_code": "+216" },
                { "name": "فلسطين", "code": "PS", "dial_code": "+970" },
                { "name": "العراق", "code": "IQ", "dial_code": "+964" },
                { "name": "لبنان", "code": "LB", "dial_code": "+961" },
                { "name": "ليبيا", "code": "LY", "dial_code": "+218" },
                { "name": "السودان", "code": "SD", "dial_code": "+249" },
                { "name": "اليمن", "code": "YE", "dial_code": "+967" },
                { "name": "سوريا", "code": "SY", "dial_code": "+963" },
                { "name": "تركيا", "code": "TR", "dial_code": "+90" },
                { "name": "ماليزيا", "code": "MY", "dial_code": "+60" },
                { "name": "إندونيسيا", "code": "ID", "dial_code": "+62" },
                { "name": "الولايات المتحدة", "code": "US", "dial_code": "+1" },
                { "name": "المملكة المتحدة", "code": "GB", "dial_code": "+44" },
                { "name": "ألمانيا", "code": "DE", "dial_code": "+49" },
                { "name": "فرنسا", "code": "FR", "dial_code": "+33" }
            ];

            const selectElement = document.getElementById('countrySelect');

            // Clear Loading Option
            selectElement.innerHTML = '<option selected disabled>اختر الدولة</option>';

            // Populate Options
            countryList.forEach(country => {
                const option = document.createElement('option');
                option.value = country.code;
                option.text = `${country.name} (${country.dial_code})`;
                selectElement.appendChild(option);
            });
        });
    </script>
</body>
</html>
