<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SongApiController;
use App\Http\Controllers\Api\AlbumApiController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\LikeController;


Route::get('/songs/recent', [SongApiController::class, 'recent']);
Route::get('/songs/genres', [SongApiController::class, 'genres']);
Route::get('/songs/genre/{genre}', [SongApiController::class, 'byGenre']);


Route::get('/songs', [SongApiController::class, 'index']);
Route::get('/songs/search', [SongApiController::class, 'search']);
Route::get('/songs/{id}', [SongApiController::class, 'show']);
Route::post('/songs', [SongApiController::class, 'store']);
Route::put('/songs/{id}', [SongApiController::class, 'update']);
Route::delete('/songs/{id}', [SongApiController::class, 'destroy']);

Route::get('/songs/stream/{id}', [SongApiController::class, 'stream']);


// --- Authenticated (must come BEFORE the catch-all /songs/{id}) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me/likes', [SongApiController::class, 'likedSongs']);   // ✅ add this
    Route::post('/songs/{song}/like', [SongApiController::class, 'likeSong']);
    Route::delete('/songs/{song}/like', [SongApiController::class, 'unlikeSong']);
});

// --- Keep this LAST so it doesn't swallow /songs/liked etc. ---
Route::get('/songs/{id}', [SongApiController::class, 'show']);


Route::get('/albums', [AlbumApiController::class, 'index']);
Route::post('/albums', [AlbumApiController::class, 'store']);
Route::get('/albums/{id}', [AlbumApiController::class, 'show']);
Route::put('/albums/{id}', [AlbumApiController::class, 'update']);
Route::delete('/albums/{id}', [AlbumApiController::class, 'destroy']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);

    // 🔹 Add these:
    Route::patch('/user/update',         [AuthController::class, 'updateProfile']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
});


Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    $u = $request->user();
    return response()->json([
        'id'         => $u->id,
        'name'       => $u->name,
        'email'      => $u->email,
        'is_premium' => (bool) $u->is_premium,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/songs/{song}/download', [SongApiController::class, 'download'])
         ->name('songs.download');
});



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


Route::middleware('auth:sanctum')->group(function () {
    // playlists
    Route::get('/playlists', [PlaylistController::class, 'index']);
    Route::post('/playlists', [PlaylistController::class, 'store']);
    Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);
    Route::put('/playlists/{playlist}', [PlaylistController::class, 'update']);
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy']);

    // songs in a playlist
    Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'addSong']);
    Route::delete('/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong']);
});

Route::post('/iap/verify', [App\Http\Controllers\IapController::class, 'verify']);



