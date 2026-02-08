<?php

use App\Http\Controllers\Api\Ads\AdsController;
use App\Http\Controllers\Api\Country\CountryController;
use App\Http\Controllers\Api\Student\favoriteController;
use App\Http\Controllers\Api\Student\StudentAuthController;
use App\Http\Controllers\Api\Student\StudentPackageController;
use App\Http\Controllers\Api\Student\StudentTeacherController;
use App\Http\Controllers\Api\Teacher\TeacherAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Teacher Authentication Routes
Route::prefix('teacher')->group(function () {

    // Login (Public)
    Route::post('/login', [TeacherAuthController::class, 'login']);
    Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
        // Logout
        Route::post('/logout', [TeacherAuthController::class, 'logout']);
    });
});

// student endpoints
Route::prefix('student')->group(function () {
    // Register
    Route::post('/register/send-otp', [StudentAuthController::class, 'sendOtp']);
    Route::post('/register/check-otp', [StudentAuthController::class, 'checkOtp']);
    Route::post('/register/complete', [StudentAuthController::class, 'completeRegistration']);

    // Login
    Route::post('/login', [StudentAuthController::class, 'login']);

    // Get Countries for Registration Dropdown
    Route::get('/countries', [CountryController::class, 'index']);

    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
        Route::post('/profile/update', [StudentAuthController::class, 'updateProfile']);

        // Logout
        Route::post('/logout', [StudentAuthController::class, 'logout']);

        Route::get('/packages', [StudentPackageController::class, 'index']);
        Route::get('/teachers', [StudentTeacherController::class, 'index']);
        Route::get('/ads', [AdsController::class, 'index']);
        // FAVORITES ROUTES
        Route::post('/favorites/toggle', [favoriteController::class, 'toggle']);
        Route::get('/favorites', [favoriteController::class, 'index']);
    });
});
