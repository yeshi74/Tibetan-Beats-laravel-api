<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SongApiController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AlbumApiController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\DedicationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\InAppMessageController;
use App\Http\Controllers\Api\SongUploadController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\RevenueCatWebhookController;
use App\Http\Controllers\Admin\SongModerationController;

/* -------------------- Public (no auth) -------------------- */

// 🎵 Song discovery (public, but personalized if Bearer token sent)
Route::get('/songs/recent', [SongApiController::class, 'recent']);
Route::get('/songs/genres', [SongApiController::class, 'genres']);
Route::get('/songs/genre/{genre}', [SongApiController::class, 'byGenre']);
Route::get('/songs/by-genre-id/{id}', [SongApiController::class, 'byGenreId']);
Route::get('/songs/search', [SongApiController::class, 'search']);
Route::get('/songs', [SongApiController::class, 'index']);
Route::get('/songs/most-played', [SongApiController::class, 'mostPlayed']);
Route::get('/songs/most-liked', [SongApiController::class, 'mostLiked']);
Route::get('/songs/{id}', [SongApiController::class, 'show']);
Route::get('/songs/stream/{id}', [SongApiController::class, 'stream']); // stream url
Route::post('/songs/{song}/played', [SongApiController::class, 'markPlayed']);

// Auth (public endpoints)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Stripe webhook (public)
Route::post('/stripe/webhook', [StripeController::class, 'webhook']);
Route::post('/revenuecat/webhook', [RevenueCatWebhookController::class, 'handle']);

// Password reset
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password',  [ForgotPasswordController::class, 'reset']);

// Public dedications (read only)
Route::get('/songs/{song}/dedications', [DedicationController::class, 'bySong']);

// Public playlists (global/admin curated)
Route::get('/featured-playlists', [PlaylistController::class, 'featured']);
Route::get('/playlists/by-type/{type}', [PlaylistController::class, 'byType']);

// Public genres
Route::get('/genres', [GenreController::class, 'index']); 
Route::get('/genres/featured', [GenreController::class, 'featured']);


/* -------------------- Authenticated (requires sanctum) -------------------- */
Route::middleware('auth:sanctum')->group(function () {
    // 👤 Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);
    Route::patch('/user/update',         [AuthController::class, 'updateProfile']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/me/profile-image',     [UserController::class, 'updateProfileImage']);

    // convenience /me payload
    Route::get('/me', function (Request $request) {
        $u = $request->user();
        $u->refresh();
        $country = 'IN';
        $currency = 'INR';
        try {
            $location = geoip()->getLocation($request->ip());
            $country = $location->country ?? $country;
            $currency = $location->currency ?? $currency;
        } catch (\Throwable $e) {
            \Log::warning('[/me] geoip failed: ' . $e->getMessage());
        }

        \Log::info('[PremiumTrace][/api/me] returning DB premium state', [
            'user_id' => $u->id,
            'is_premium_raw' => $u->getRawOriginal('is_premium'),
            'is_premium_cast' => (bool) $u->is_premium,
            'premium_ends_at' => optional($u->premium_ends_at)->toIso8601String(),
            'source_of_truth' => 'users.is_premium',
        ]);

        return response()->json([
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'is_premium' => (bool) $u->is_premium,
            'premium_ends_at' => optional($u->premium_ends_at)->toIso8601String(),
            'premium_source' => 'users.is_premium',
            'country'    => $country,
            'currency'   => $currency,
        ]);
    });

    // ❤️ Likes
    Route::get('/me/likes',            [SongApiController::class, 'likedSongs']);
    Route::post('/songs/{song}/like',  [SongApiController::class, 'likeSong']);
    Route::delete('/songs/{song}/like',[SongApiController::class, 'unlikeSong']);

    // ⬇️ Downloads (premium enforced inside controller)
    Route::get('/songs/{song}/download', [SongApiController::class, 'download'])
        ->name('songs.download');

    // 🎶 Playlists (user-owned)
    Route::get('/playlists',                [PlaylistController::class, 'index']);
    Route::post('/playlists',               [PlaylistController::class, 'store']);
    Route::get('/playlists/{playlist}',     [PlaylistController::class, 'show']);
    Route::put('/playlists/{playlist}',     [PlaylistController::class, 'update']);
    Route::delete('/playlists/{playlist}',  [PlaylistController::class, 'destroy']);
    Route::post('/playlists/{playlist}/songs',        [PlaylistController::class, 'addSong']);
    Route::delete('/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong']);

    // 🙏 Dedications (write)
    Route::post('/dedications', [DedicationController::class, 'store']);
    Route::get('/dedications',  [DedicationController::class, 'index']);
    Route::post('/dedications/{dedication}/like',   [DedicationController::class, 'like']);
    Route::post('/dedications/{dedication}/unlike', [DedicationController::class, 'unlike']);

    // 🔍 User search
    Route::get('/users/search', [\App\Http\Controllers\Api\UserSearchController::class, 'search']);

    // 💳 Stripe (authenticated flows)
    Route::post('/stripe/checkout',        [StripeController::class, 'createCheckoutSession']);
    Route::post('/stripe/billing-portal',  [StripeController::class, 'createBillingPortal']);
    Route::post('/stripe/confirm',         [StripeController::class, 'confirmAfterCheckout']);

    // 🎤 Artist
    Route::post('artists/apply', [ArtistController::class, 'apply']);
    Route::get('artists/me',     [ArtistController::class, 'me']);

    // 📤 Song upload
    Route::post('songs', [SongUploadController::class, 'store']);

    // 🛠️ Admin song moderation
    Route::middleware('is_admin')->group(function () {
        Route::post('/admin/songs/{song}/approve', [SongModerationController::class, 'approve']);
        Route::post('/admin/songs/{song}/reject',  [SongModerationController::class, 'reject']);
    });

    // Artist public view
Route::get('/artists/{artist}', [ArtistController::class, 'show']);
});
