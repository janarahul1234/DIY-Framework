<?php

use App\core\Route;
use App\Controllers\AuthController;
use App\Controllers\PageController;

Route::get('/', [PageController::class])->name('home');

Route::view('/welcome', 'pages/welcome')->name('welcome');

Route::get('/about', [PageController::class, 'about']);

Route::get('/contact', [PageController::class, 'contact']);

Route::get('/services', function () {
    return view('pages/services');
})->name('services');

Route::get('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

Route::get('/posts/{postId}', function ($postId) {
    return "PostId: {$postId}";
});

Route::get('/users/{userId}', [AuthController::class, 'user']);

Route::fallback('errors/404');