<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <title>رسالة جديدة</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: #fff; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto;">
        <h2 style="color: #2d8a74;">رسالة تواصل جديدة</h2>
        <hr>
        <p><strong>الاسم:</strong> {{ $data['name'] }}</p>
        <p><strong>البريد:</strong> {{ $data['email'] }}</p>
        <p><strong>الموضوع:</strong> {{ $data['subject'] }}</p>
        <hr>
        <h3>الرسالة:</h3>
        <p style="background-color: #f9f9f9; padding: 15px; border-radius: 5px;">
            {{ $data['message'] }}
        </p>
    </div>
</body>

</html>
