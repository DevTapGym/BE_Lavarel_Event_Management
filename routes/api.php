<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UploadController;

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

    Route::prefix('/notification')->group(function () {
        Route::get('/{eventId}', [NotificationController::class, 'notificationsByEvent'])->name('get.notifications.by.event');
        Route::get('/', [NotificationController::class, 'getAllNotification'])->name('get.all.notifications');
        Route::post('/', [NotificationController::class, 'store'])->name('create.notification');
        Route::put('/{id}', [NotificationController::class, 'update'])->name('update.notification');
        Route::delete('/{id}', [NotificationController::class, 'delete'])->name('delete.notification');
    });

    Route::prefix('/upload')->group(function () {
        Route::post('/avatar', [UploadController::class, 'uploadAvatar'])->name('upload.avatar');
        Route::post('/speaker-avatar', [UploadController::class, 'uploadSpeakerAvatar'])->name('upload.speaker.avatar');
        Route::post('/event-image', [UploadController::class, 'uploadEventImage'])->name('upload.event.image');
        Route::post('/pages', [UploadController::class, 'uploadPages'])->name('upload.paper.file');
    });

    Route::prefix('/download')->group(function () {
        Route::get('/paper/{paperId}', [UploadController::class, 'downloadPaper'])->name('download.paper');
    });

    //Route::post('/graphql', [GraphQLController::class, '__invoke']);
});
