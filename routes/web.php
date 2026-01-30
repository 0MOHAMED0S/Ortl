<?php

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
