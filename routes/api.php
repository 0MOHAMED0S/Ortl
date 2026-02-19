<?php

use App\Http\Controllers\Api\Ads\AdsController;
use App\Http\Controllers\Api\Country\CountryController;
use App\Http\Controllers\Api\Student\favoriteController;
use App\Http\Controllers\Api\Student\StudentAuthController;
use App\Http\Controllers\Api\Student\StudentPackageController;
use App\Http\Controllers\Api\Student\StudentTeacherController;
use App\Http\Controllers\Api\Student\StudentTracksController;
use App\Http\Controllers\Api\Teacher\TeacherAuthController;
use App\Http\Controllers\Api\Teacher\TeacherSlotController;
use App\Http\Controllers\web\User\BuyPackageController;
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
        Route::get('/ads', [AdsController::class, 'index']);
        Route::get('/profile', [TeacherAuthController::class, 'profile']);
        Route::post('/slots', [TeacherSlotController::class, 'setAvailability']);
        Route::get('/my-slots', [TeacherSlotController::class, 'getMySlots']);
        Route::delete('/slots/{id}', [TeacherSlotController::class, 'deleteSlot']);
        Route::post('/slots-by-day', [TeacherSlotController::class, 'deleteDaySlots']);

    });
});

// student endpoints
Route::prefix('student')->group(function () {
    // Register
    Route::post('/register/send-otp', [StudentAuthController::class, 'sendOtp']);
    Route::post('/register/check-otp', [StudentAuthController::class, 'checkOtp']);
    Route::post('/register/complete', [StudentAuthController::class, 'completeRegistration']);
    Route::post('/forgot-password/send-otp', [StudentAuthController::class, 'forgotPasswordSendOtp']);
    Route::post('/forgot-password/check-otp', [StudentAuthController::class, 'checkOtp']);
    Route::post('/forgot-password/reset', [StudentAuthController::class, 'resetPassword']);

    // Login
    Route::post('/login', [StudentAuthController::class, 'login']);

    // Get Countries for Registration Dropdown
    Route::get('/countries', [CountryController::class, 'index']);

    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
        Route::post('/profile/update', [StudentAuthController::class, 'updateProfile']);
        Route::post('/change-password', [StudentAuthController::class, 'ChangePassword']);

        // Logout
        Route::post('/logout', [StudentAuthController::class, 'logout']);
        Route::get('/packages', [StudentPackageController::class, 'index']);
        Route::get('/teachers', [StudentTeacherController::class, 'index']);
        Route::get('/ads', [AdsController::class, 'index']);
        // FAVORITES ROUTES
        Route::post('/favorites/toggle', [favoriteController::class, 'toggle']);
        Route::get('/favorites', [favoriteController::class, 'index']);

        Route::post('/packages/{package}/buy', [BuyPackageController::class, 'buy'])->name('packages.buy');
        Route::get('/user-packages', [StudentPackageController::class, 'userPackages']);
        Route::get('/tracks', [StudentTracksController::class, 'index']);
        Route::get('teachers/{id}', [StudentTeacherController::class, 'show']);
        Route::post('/package/{id}/coupon', [StudentPackageController::class, 'getPrice']);
        Route::get('/{id}/available-slots', [StudentTeacherController::class, 'getTeacherAvailableSlots']);

    });
});
Route::get('payments/callback', [BuyPackageController::class, 'handleCallback']);
