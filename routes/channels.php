<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('teacher.{id}', function ($user, $id) {
    $teacherProfile = $user->teacherProfile;
    if ($teacherProfile && (int) $teacherProfile->id === (int) $id) {
        return true;
    }
    return false;
});

Broadcast::channel('admin.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
