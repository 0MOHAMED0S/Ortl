<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعادة كلمة المرور - ورتل</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');

        body {
            margin: 0; padding: 0;
            background-color: #f3f4f6;
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2d3436;
        }

        .email-wrapper { width: 100%; background-color: #f3f4f6; padding: 40px 0; }
        .email-container {
            max-width: 550px; margin: 0 auto;
            background-color: #ffffff; border-radius: 16px;
            overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .email-header {
            background-color: #ffffff; padding: 30px 0;
            text-align: center; border-bottom: 4px solid #1a4d2e;
        }

        .logo { font-size: 28px; font-weight: 800; color: #1a4d2e; text-decoration: none; }

        .email-body { padding: 40px 30px; text-align: center; }
        h1 { color: #1a4d2e; font-size: 22px; margin: 0 0 16px; font-weight: 700; }
        p { color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 24px; }

        .otp-box {
            background-color: #fff9f0; /* Slight orange tint for security/action */
            border: 2px dashed #c49a46;
            border-radius: 12px; padding: 20px;
            margin: 30px auto; width: fit-content; min-width: 200px;
        }

        .otp-code {
            font-size: 36px; font-weight: 800; color: #1a4d2e;
            letter-spacing: 8px; font-family: 'Courier New', monospace;
            margin: 0; direction: ltr;
        }

        .expiry-note {
            font-size: 14px; color: #c49a46; font-weight: 700;
            margin-top: 15px; display: flex; align-items: center;
            justify-content: center; gap: 5px;
        }

        .email-footer {
            background-color: #fafafa; padding: 20px;
            text-align: center; font-size: 13px; color: #9ca3af;
            border-top: 1px solid #f0f0f0;
        }

        .email-footer a { color: #1a4d2e; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <a href="#" class="logo">ورتـــل</a>
            </div>

            <div class="email-body">
                <h1>طلب إعادة تعيين كلمة المرور</h1>
                <p>تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في منصة ورتل التعليمية. يرجى استخدام الرمز التالي لإكمال العملية:</p>

                <div class="otp-box">
                    <div class="otp-code">{{ $otp }}</div>
                </div>

                <div class="expiry-note">
                    ⚠️ هذا الرمز صالح لمدة 10 دقائق فقط
                </div>

                <p style="font-size: 14px; margin-top: 30px; color: #6b7280; border-top: 1px solid #eee; padding-top: 20px;">
                    إذا لم تطلب إعادة تعيين كلمة المرور، فيرجى تجاهل هذه الرسالة. حسابك سيبقى آمناً ولن يتم تغيير أي بيانات.
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
