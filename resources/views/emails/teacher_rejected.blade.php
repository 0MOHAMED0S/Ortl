<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحديث بخصوص طلبك - ورتل</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');

        body {
            margin: 0; padding: 0; background-color: #f3f4f6;
            font-family: 'Cairo', sans-serif; color: #2d3436;
        }
        .email-wrapper { width: 100%; background-color: #f3f4f6; padding: 40px 0; }
        .email-container {
            max-width: 600px; margin: 0 auto; background-color: #ffffff;
            border-radius: 16px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb;
        }
        .email-header {
            background-color: #ffffff; padding: 30px 0; text-align: center;
            border-bottom: 4px solid #1a4d2e;
        }
        .logo {
            font-size: 28px; font-weight: 800; color: #1a4d2e;
            text-decoration: none; letter-spacing: -1px;
        }
        .email-body { padding: 40px 30px; text-align: right; }
        h1 { color: #1a4d2e; font-size: 22px; margin: 0 0 20px; font-weight: 700; text-align: center;}
        p { color: #4b5563; font-size: 16px; line-height: 1.8; margin: 0 0 20px; }

        .highlight-box {
            background-color: #f9fafb; border-right: 4px solid #9ca3af;
            padding: 15px 20px; margin: 25px 0; border-radius: 8px 0 0 8px;
            color: #374151; font-size: 15px;
        }

        .signature {
            margin-top: 40px; font-weight: 700; color: #1a4d2e; text-align: center;
        }

        .email-footer {
            background-color: #fafafa; padding: 20px; text-align: center;
            font-size: 13px; color: #9ca3af; border-top: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <a href="#" class="logo">ورتـــل</a>
            </div>
            <div class="email-body">
                <h1>مرحباً أ. {{ $application->full_name }}،</h1>

                <p>نشكرك جزيل الشكر على اهتمامك ووقتك الذي أمضيته في تقديم طلب الانضمام كمعلم في منصة ورتل.</p>

                <div class="highlight-box">
                    لقد قامت الإدارة بمراجعة طلبك وسيرتك الذاتية بعناية فائقة. نأسف لإبلاغك بأننا لن نتمكن من المضي قدماً في قبول طلبك في الوقت الحالي، حيث أن مؤهلاتك لا تتطابق تماماً مع الشواغر والاحتياجات الحالية لدينا.
                </div>

                <p>نتمنى لك كل التوفيق والنجاح في مسيرتك المهنية، وسنحتفظ ببياناتك في سجلاتنا للتواصل معك في حال توفرت فرص مناسبة مستقبلاً.</p>

                <div class="signature">
                    مع خالص التحيات،<br>
                    فريق إدارة ورتل
                </div>
            </div>
            <div class="email-footer">
                &copy; {{ date('Y') }} منصة ورتل. جميع الحقوق محفوظة.
            </div>
        </div>
    </div>
</body>
</html>
