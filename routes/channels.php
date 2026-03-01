<?php

use App\Models\Teacher;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('teacher.{id}', function ($user, $id) {
    return $user->id === Teacher::where('id', $id)->value('user_id');
});
