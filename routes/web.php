<?php

use App\Http\Controllers\web\Admin\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/teacher', function () {
    return view('main.teacher');
})->name('teacher.index');

Route::get('/close', function () {
    return view('main.close');
})->name('close.index');



Route::prefix('admin')->group(function () {

    Route::get('/login', fn() => view('dashboard.login'))
        ->name('admin.login');

    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', fn() => view('dashboard.index'))
            ->name('admin.dashboard');
    });
    Route::post('/admin/logout', [AuthController::class, 'logout'])
        ->name('admin.logout');
});
