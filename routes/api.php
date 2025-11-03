<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivationController;

// ---------------------
// Public routes 
// ---------------------
Route::prefix('/v1')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/refresh', [AuthController::class, 'refreshToken']);
        Route::post('/forgot-password', [ActivationController::class, 'forgotPassword']);
        Route::post('/reset-password', [ActivationController::class, 'resetPassword']);
    });

    Route::prefix('/events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::get('/{id}', [EventController::class, 'show']);
        Route::post('/', [EventController::class, 'store']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);
    });
});


// ---------------------
// Protected routes 
// ---------------------

Route::prefix('/v1')->middleware(['jwt.auth'])->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/send-code', [ActivationController::class, 'sendActivationCode']);
        Route::post('/verify-code', [ActivationController::class, 'verifyActivationCode']);
    });
});

Route::prefix('/v1')->middleware(['jwt.auth', 'check.permission', 'active'])->group(function () {

    Route::prefix('/auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('get.info');
        Route::put('/edit-profile', [AuthController::class, 'updateProfile'])->name('edit.profile');
        Route::put('/change-password', [AuthController::class, 'changePassword'])->name('change.password');
    });

    Route::prefix('/events')->group(function () {
        // Additional protected event routes can be added here
    });
});
