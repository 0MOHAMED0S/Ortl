<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التحقق - ورتل</title>
    <style>
        /* استيراد خط كايرو لظهور عربي جميل */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');

        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: #2d3436;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 0;
        }

        .email-container {
            max-width: 550px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        /* رأس الرسالة */
        .email-header {
            background-color: #ffffff;
            padding: 30px 0;
            text-align: center;
            border-bottom: 4px solid #1a4d2e; /* اللون الأساسي */
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #1a4d2e;
            text-decoration: none;
            letter-spacing: -1px;
        }

        /* محتوى الرسالة */
        .email-body {
            padding: 40px 30px;
            text-align: center;
        }

        h1 {
            color: #1a4d2e;
            font-size: 22px;
            margin: 0 0 16px;
            font-weight: 700;
        }

        p {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 24px;
        }

        /* صندوق الكود */
        .otp-box {
            background-color: #f0fdf4; /* خلفية خضراء فاتحة جداً */
            border: 2px dashed #1a4d2e;
            border-radius: 12px;
            padding: 20px;
            margin: 30px auto;
            width: fit-content;
            min-width: 200px;
        }

        .otp-code {
            font-size: 36px;
            font-weight: 800;
            color: #1a4d2e;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace; /* لتمييز الأرقام */
            margin: 0;
            direction: ltr; /* لضمان ظهور الأرقام بشكل صحيح */
        }

        /* تنبيه انتهاء الصلاحية */
        .expiry-note {
            font-size: 14px;
            color: #c49a46; /* اللون الذهبي */
            font-weight: 700;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        /* تذييل الرسالة */
        .email-footer {
            background-color: #fafafa;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            border-top: 1px solid #f0f0f0;
        }

        .email-footer a {
            color: #1a4d2e;
            text-decoration: none;
            font-weight: 600;
            margin: 0 5px;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                width: 95% !important;
            }
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
                <h1>تأكيد البريد الإلكتروني</h1>
                <p>مرحباً بك في منصة ورتل التعليمية،<br>لإكمال عملية التسجيل وتأمين حسابك، يرجى استخدام رمز التحقق التالي:</p>

                <div class="otp-box">
                    <div class="otp-code">{{ $otp }}</div>
                </div>

                <div class="expiry-note">
                    ⚠️ هذا الرمز صالح لمدة 10 دقائق فقط
                </div>

                <p style="font-size: 14px; margin-top: 30px; color: #6b7280; border-top: 1px solid #eee; padding-top: 20px;">
                    إذا لم تقم بطلب هذا الرمز، يمكنك تجاهل هذه الرسالة بأمان، ولن يتم اتخاذ أي إجراء على حسابك.
                </p>
            </div>

            <div class="email-footer">
                &copy; {{ date('Y') }} منصة ورتل. جميع الحقوق محفوظة.<br>
                <div style="margin-top: 8px;">
                    <a href="#">سياسة الخصوصية</a> • <a href="#">مركز المساعدة</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
