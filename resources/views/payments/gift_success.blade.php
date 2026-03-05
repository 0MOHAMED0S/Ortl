<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نجاح الإهداء</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .card {
            background: white;
            padding: 40px 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 400px;
            width: 90%;
        }
        .icon-success {
            font-size: 60px;
            color: #2ea68d; /* اللون الأخضر الخاص بتطبيقك */
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 30px;
        }
        .coupon-box {
            background: #eef9f6;
            border: 2px dashed #2ea68d;
            color: #2ea68d;
            font-size: 28px;
            font-weight: bold;
            padding: 15px;
            border-radius: 10px;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }
        .btn-whatsapp {
            background-color: #25D366;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            width: 80%;
            transition: 0.3s;
        }
        .btn-whatsapp:hover {
            background-color: #1ebe57;
        }
        .footer-note {
            margin-top: 20px;
            font-size: 12px;
            color: #aaa;
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon-success">🎁🎉</div>
        <h1>اكتمل الدفع بنجاح!</h1>
        <p>تم تجهيز باقة الهدية الخاصة بك. يمكنك الآن نسخ الكود السري أو إرساله مباشرة لصديقك عبر واتساب.</p>

        <div class="coupon-box">
            {{ $couponCode }}
        </div>

        <a href="{{ $whatsappLink }}" class="btn-whatsapp" target="_blank">
            إرسال الهدية عبر واتساب
        </a>

        <div class="footer-note">
            يمكنك إغلاق هذه الصفحة والعودة للتطبيق.
        </div>
    </div>

</body>
</html>
