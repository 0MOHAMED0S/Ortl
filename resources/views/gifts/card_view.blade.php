<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="لديك هدية جديدة! 🎁">
    <meta property="og:description"
        content="أرسل لك {{ $giftCard->sender->name ?? 'صديقك' }} باقة دقائق كهدية. افتح الرابط لاستلامها.">
    <title>بطاقة إهداء</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f9f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .gift-card {
            background: linear-gradient(135deg, #2ea68d 0%, #1c7c67 100%);
            border-radius: 20px;
            padding: 30px 20px;
            color: white;
            text-align: center;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 15px 35px rgba(46, 166, 141, 0.3);
            position: relative;
            overflow: hidden;
        }

        .occasion-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        .minutes {
            font-size: 40px;
            font-weight: bold;
            margin: 15px 0;
            color: #f8f9fa;
        }

        .message-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 15px;
            font-style: italic;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .coupon-code {
            background: white;
            color: #2ea68d;
            padding: 15px;
            border-radius: 10px;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .btn-copy {
            background-color: #ffd700;
            color: #333;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }

        .claimed-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            border-radius: 20px;
        }

        .claimed-text {
            color: white;
            font-size: 24px;
            font-weight: bold;
            border: 3px solid white;
            padding: 10px 20px;
            transform: rotate(-15deg);
        }
    </style>
</head>

<body>

    <div class="gift-card">
        @if ($isClaimed)
            <div class="claimed-overlay">
                <div class="claimed-text">تم الاستلام ❌</div>
                <p style="margin-top: 20px;">عذراً، تم استلام هذه الهدية مسبقاً.</p>
            </div>
        @endif

        <div class="occasion-badge">مُناسبة: {{ $giftCard->occasion }}</div>

        <h1>مرحباً، {{ $giftCard->recipient_name }}!</h1>
        <p>لديك إهداء باقة من: <strong>{{ $giftCard->sender->name ?? 'صديق' }}</strong></p>

        <div class="minutes">
            {{ $giftCard->minutes }} <span style="font-size: 20px;">دقيقة</span>
        </div>

        @if ($giftCard->message)
            <div class="message-box">
                "{{ $giftCard->message }}"
            </div>
        @endif

        <p>انسخ هذا الكود واستخدمه داخل التطبيق لاستلام باقتك:</p>

        <div class="coupon-code" id="couponText">{{ $giftCard->coupon_code }}</div>

        <button class="btn-copy" onclick="copyCode()">نسخ الكود والانتقال للتطبيق</button>
    </div>

    <script>
        function copyCode() {
            var text = document.getElementById("couponText").innerText;
            navigator.clipboard.writeText(text).then(function() {
                alert("تم نسخ الكود: " + text + "\nافتح التطبيق الآن لاستلام هديتك!");
                // إذا كان لديك Deep Link لفتح التطبيق مباشرة يمكنك وضعه هنا:
                // window.location.href = "yourapp://claim-gift";
            });
        }
    </script>

</body>

</html>
