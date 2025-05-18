<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScoreController;

// Public routes
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware(['jwt.verify'])->group(function () {
    Route::post('/save-score', [ScoreController::class, 'store']);
    Route::get('/overall-score', [ScoreController::class, 'overallScore']);
    Route::get('/weekly-score', [ScoreController::class, 'weeklyScore']);
});
