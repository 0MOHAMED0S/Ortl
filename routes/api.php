<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    // teacher endpoints
});

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    // student endpoints
});
