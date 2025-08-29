<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SongApiController;
use App\Http\Controllers\Api\AlbumApiController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\DedicationController;

/* -------------------- Public (no auth) -------------------- */

// songs (public reads)
Route::get('/songs/recent', [SongApiController::class, 'recent']);
Route::get('/songs/genres', [SongApiController::class, 'genres']);
Route::get('/songs/genre/{genre}', [SongApiController::class, 'byGenre']);
Route::get('/songs/search', [SongApiController::class, 'search']);
Route::get('/songs', [SongApiController::class, 'index']);
Route::get('/songs/{id}', [SongApiController::class, 'show']); // keep only once

// 🔹 stream route (needed by audio_url)
Route::get('/songs/stream/{id}', [SongApiController::class, 'stream']);

// auth (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Stripe webhook (public, no auth)
Route::post('/stripe/webhook', [StripeController::class, 'webhook']);


/* -------------------- Authenticated -------------------- */

Route::middleware('auth:sanctum')->group(function () {
    // profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']); // single /user endpoint
    Route::patch('/user/update',         [AuthController::class, 'updateProfile']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);

    // convenience /me payload
    Route::get('/me', function (Request $request) {
        $u = $request->user();
        return response()->json([
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'is_premium' => (bool) $u->is_premium,
        ]);
    });

    // likes
    Route::get('/me/likes', [SongApiController::class, 'likedSongs']);
    Route::post('/songs/{song}/like', [SongApiController::class, 'likeSong']);
    Route::delete('/songs/{song}/like', [SongApiController::class, 'unlikeSong']);

    // downloads (server should enforce premium)
    Route::get('/songs/{song}/download', [SongApiController::class, 'download'])
        ->name('songs.download');

    // playlists
    Route::get('/playlists', [PlaylistController::class, 'index']);
    Route::post('/playlists', [PlaylistController::class, 'store']);
    Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);
    Route::put('/playlists/{playlist}', [PlaylistController::class, 'update']);
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy']);
    Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'addSong']);
    Route::delete('/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong']);

    Route::post('/dedications', [DedicationController::class, 'store']);

    Route::get('/users/search', [\App\Http\Controllers\Api\UserSearchController::class, 'search']);


    // Stripe checkout (create session)
    Route::post('/stripe/checkout', [StripeController::class, 'createCheckoutSession']);

    // ✅ Customer Billing Portal (manage/cancel/change plan)
    Route::post('/stripe/billing-portal', [StripeController::class, 'createBillingPortal']);

    Route::post('/stripe/confirm', [StripeController::class, 'confirmAfterCheckout']);

});

// public
Route::get('/dedications', [DedicationController::class, 'index']);
Route::get('/songs/{song}/dedications', [DedicationController::class, 'bySong']);

// IAP verify (if you use it)
Route::post('/iap/verify', [App\Http\Controllers\IapController::class, 'verify']);
