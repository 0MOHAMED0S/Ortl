<?php

use App\Http\Controllers\Api\Student\StudentAuthController;
use App\Http\Controllers\web\Admin\AdsController;
use App\Http\Controllers\web\Admin\AuthController;
use App\Http\Controllers\web\Admin\PackageController;
use App\Http\Controllers\web\Admin\SettingController;
use App\Http\Controllers\web\Admin\TeacherController;
use App\Http\Controllers\web\Admin\TrackController as AdminTrackController;
use App\Http\Controllers\web\User\MainController;
use App\Http\Controllers\web\User\TeacherApplicationController;
use App\Http\Controllers\web\User\TrackController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// })->name('welcome');

// Route::get('/teacher', function () {
//     return view('main.teacher');
// })->name('teacher.index');

Route::get('/close', function () {
    return view('main.close');
})->name('close.index');

Route::get('/msa', function () {
    return view('dashboard.teachers');
});
Route::get('/teacher', [TeacherApplicationController::class, 'index'])->name('teacher.index');
Route::get('/', [MainController::class, 'index'])->name('welcome');


Route::prefix('admin')->group(function () {

    Route::get('/login', fn() => view('dashboard.login'))->name('admin.login');

    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', fn() => view('dashboard.index'))->name('admin.dashboard');
        Route::post('settings/toggle-registration', [SettingController::class, 'toggleTeacherRegistration'])->name('settings.toggleRegistration');
        Route::post('/teachers/{id}/update-details', [TeacherController::class, 'updateDetails'])->name('teacher.updateDetails');

        Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

        Route::resource('tracks', AdminTrackController::class);
        Route::resource('teachers', TeacherController::class);
        Route::post('/teachers/{id}/approve', [TeacherController::class, 'approve'])->name('teacher.approve');
        Route::post('/teachers/{id}/reject', [TeacherController::class, 'reject'])->name('teacher.reject');
        Route::resource('packages', PackageController::class);
        Route::resource('ads', AdsController::class)->except(['create', 'show', 'edit']);


        Route::get('/students', function () {return view('dashboard.students');})->name('admin.students');
        Route::get('/sessions', function () {return view('dashboard.sessions');})->name('admin.sessions');
        Route::get('/Subscriptions', function () {return view('dashboard.subscriptions');})->name('admin.subscriptions');

    });
});

Route::post('/teacher-apply', [TeacherApplicationController::class, 'store'])
    ->name('teacher.apply');



