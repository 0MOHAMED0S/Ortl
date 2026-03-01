<?php

use Illuminate\Support\Facades\Broadcast;

// قناة المستخدم العادية
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// 🔒 التحقق من صلاحية دخول المعلم لقناته الخاصة
Broadcast::channel('teacher.{id}', function ($user, $id) {
    // نجلب ملف المعلم الخاص بالمستخدم الحالي
    $teacherProfile = $user->teacherProfile;

    // إذا كان المستخدم لديه ملف معلم، والـ ID يطابق الـ ID المطلوب
    if ($teacherProfile && (int) $teacherProfile->id === (int) $id) {
        return true; // مسموح بالدخول
    }

    return false; // مرفوض (سيعطي 403)
});
