<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SongApiController;
use App\Http\Controllers\Api\AlbumApiController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;


Route::get('/songs', [SongApiController::class, 'index']);
Route::get('/songs/search', [SongApiController::class, 'search']);
Route::get('/songs/{id}', [SongApiController::class, 'show']);
Route::post('/songs', [SongApiController::class, 'store']);
Route::put('/songs/{id}', [SongApiController::class, 'update']);
Route::delete('/songs/{id}', [SongApiController::class, 'destroy']);


Route::get('/albums', [AlbumApiController::class, 'index']);
Route::post('/albums', [AlbumApiController::class, 'store']);
Route::get('/albums/{id}', [AlbumApiController::class, 'show']);
Route::put('/albums/{id}', [AlbumApiController::class, 'update']);
Route::delete('/albums/{id}', [AlbumApiController::class, 'destroy']);


Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
Route::post('logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('user',      [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::post('/login/mobile', [AuthController::class, 'loginWithMobile']);

Route::get('/songs/stream/{id}', [SongApiController::class, 'stream']);


// Send OTP
Route::post('/login/send-otp', [AuthController::class, 'sendOtp']);

// Verify OTP and login
Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:sanctum']);

Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully!']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');



Route::get('/email-verified-success', function () {
    return view('verified-success');
})->name('verification.success');