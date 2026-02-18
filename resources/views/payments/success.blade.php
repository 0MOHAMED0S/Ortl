<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم الدفع بنجاح</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f7f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px; /* مسافة أمان للجوال */
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            text-align: center;
            max-width: 450px;
            width: 100%;
            transition: transform 0.3s ease;
        }

        /* تأثير بسيط عند التحميل */
        .card { animation: fadeInUp 0.6s ease-out; }

        .icon {
            font-size: clamp(50px, 15vw, 80px); /* حجم مرن للأيقونة */
            color: #28a745;
            margin-bottom: 20px;
            display: block;
        }

        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: clamp(1.5rem, 5vw, 2rem); /* حجم خط مرن */
        }

        p {
            color: #666;
            line-height: 1.7;
            margin-bottom: 30px;
            font-size: clamp(0.95rem, 4vw, 1.1rem);
        }

        .btn {
            background-color: #28a745;
            color: white;
            text-decoration: none;
            padding: 14px 35px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-block;
            width: auto;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn:hover {
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        /* أنيميشن الدخول */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* تحسينات للشاشات الصغيرة جداً */
        @media (max-width: 400px) {
            .card {
                padding: 1.5rem;
                border-radius: 15px;
            }
            .btn {
                display: block; /* الزر يأخذ العرض كامل في الجوال الصغير */
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="icon" role="img" aria-label="Success">✅</span>
        <h1>تمت العملية بنجاح!</h1>
        <p>شكرًا لك! لقد تم دفع قيمة الباقة وتفعيل الدقائق في حسابك بنجاح. يمكنك الآن البدء في رحلتك التعليمية.</p>
    </div>
</body>
</html>
