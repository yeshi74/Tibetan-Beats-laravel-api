<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SongAdminController;
use App\Http\Controllers\Admin\AlbumController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\PlaylistController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\DedicationModerationController;
use App\Http\Controllers\Admin\InAppMessageController;
use App\Http\Controllers\Admin\AdminSongClaimController;
use App\Http\Controllers\Admin\SongReportAdminController;
use App\Http\Controllers\Admin\AdminArtistClaimController;
use App\Http\Controllers\Admin\ArtistAdminController;
use App\Http\Controllers\Admin\AppSectionController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHomeController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // 🔹 Admin landing/dashboard
        Route::get('/', [AdminHomeController::class, 'index'])->name('dashboard');

        Route::resource('albums', AlbumController::class);
        Route::resource('songs', SongAdminController::class);

        Route::resource('playlists', PlaylistController::class);
        Route::patch('playlists/{playlist}/toggle-featured', [PlaylistController::class, 'toggleFeatured'])
            ->name('playlists.toggleFeatured');
        Route::resource('genres', \App\Http\Controllers\Admin\GenreController::class);

        Route::get('users', [UserAdminController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserAdminController::class, 'show'])->name('users.show');
        Route::patch('users/{user}/ban', [UserAdminController::class, 'ban'])->name('users.ban');
        Route::patch('users/{user}/unban', [UserAdminController::class, 'unban'])->name('users.unban');
        Route::patch('users/{user}/role', [UserAdminController::class, 'updateRole'])->name('users.updateRole');
        Route::patch('users/{user}/toggle-ban', [UserAdminController::class, 'toggleBan'])->name('users.toggleBan');
        Route::delete('users/{user}', [UserAdminController::class, 'softDelete'])->name('users.destroy');
        Route::post('users/{id}/restore', [UserAdminController::class, 'restore'])->name('users.restore');

        Route::get('in-app-messages', [InAppMessageController::class,'index'])->name('in_app_messages.index');
        Route::get('in-app-messages/create', [InAppMessageController::class,'create'])->name('in_app_messages.create');
        Route::post('in-app-messages', [InAppMessageController::class,'store'])->name('in_app_messages.store');
        Route::get('in-app-messages/{inAppMessage}', [InAppMessageController::class,'show'])->name('in_app_messages.show');
        Route::get('in-app-messages/{inAppMessage}/edit', [InAppMessageController::class,'edit'])->name('in_app_messages.edit');
        Route::patch('in-app-messages/{inAppMessage}', [InAppMessageController::class,'update'])->name('in_app_messages.update');
        Route::delete('in-app-messages/{inAppMessage}', [InAppMessageController::class,'destroy'])->name('in_app_messages.destroy');

        Route::get('artists/{artist}',[App\Http\Controllers\Admin\ArtistAdminController::class, 'show'])
        ->name('artists.show');


        Route::get('artists', [\App\Http\Controllers\Admin\ArtistAdminController::class, 'index'])->name('artists.index');
        Route::get('artists/create',[\App\Http\Controllers\Admin\ArtistAdminController::class, 'create'])
        ->name('artists.create');
        Route::get('artists/{artist}/edit',[ArtistAdminController::class, 'edit'])->name('artists.edit');
        Route::put('artists/{artist}',[ArtistAdminController::class, 'update'])->name('artists.update');


        Route::post('artists',[\App\Http\Controllers\Admin\ArtistAdminController::class, 'store'])->name('artists.store');
        Route::get('artists/{artist}', [\App\Http\Controllers\Admin\ArtistAdminController::class, 'show'])->name('artists.show');
        Route::patch('artists/{artist}/approve', [\App\Http\Controllers\Admin\ArtistAdminController::class, 'approve'])->name('artists.approve');
        Route::patch('artists/{artist}/reject', [\App\Http\Controllers\Admin\ArtistAdminController::class, 'reject'])->name('artists.reject');



        Route::patch('songs/{song}/approve', [SongAdminController::class, 'approve'])->name('songs.approve');
        Route::patch('songs/{song}/reject', [SongAdminController::class, 'reject'])->name('songs.reject');

        Route::get('song-claims', [AdminSongClaimController::class, 'index'])->name('song-claims.index');

        Route::get('song-claims/{claim}',[AdminSongClaimController::class, 'show'])->name('song-claims.show');

        Route::patch('song-claims/{claim}/approve',[AdminSongClaimController::class, 'approve'])->name('song-claims.approve');

        Route::patch('song-claims/{claim}/reject',[AdminSongClaimController::class, 'reject'])->name('song-claims.reject');

        //song report
        Route::get('song-reports',[SongReportAdminController::class, 'index'])
            ->name('song-reports.index');

        Route::patch('song-reports/{report}/review',[SongReportAdminController::class, 'review'])
            ->name('song-reports.review');

        Route::patch('song-reports/{report}/dismiss',[SongReportAdminController::class, 'dismiss'])
            ->name('song-reports.dismiss');


            // Artist Claims

        Route::get('artist-claims',[AdminArtistClaimController::class, 'index'])->name('artist-claims.index');

        Route::get('artist-claims/{claim}',[AdminArtistClaimController::class, 'show'])->name('artist-claims.show');

        Route::patch('artist-claims/{claim}/approve',[AdminArtistClaimController::class, 'approve'])->name('artist-claims.approve');

        Route::patch('artist-claims/{claim}/reject',[AdminArtistClaimController::class, 'reject'])->name('artist-claims.reject');

        //home page control
        Route::resource('app-sections', AppSectionController::class);

        Route::get('/settings',[AppSettingController::class, 'edit'])->name('settings.edit');
        Route::post('/settings',[AppSettingController::class, 'update'])->name('settings.update');

        Route::get('/dashboard',[AdminDashboardController::class, 'index'])->name('dashboard');


    });



Route::middleware(['auth','role:admin,moderator'])
    ->prefix('admin/moderation')
    ->name('admin.moderation.')
    ->group(function () {
        Route::patch('dedications/bulk/approve', [DedicationModerationController::class, 'bulkApprove'])
            ->name('dedications.bulkApprove');
        Route::patch('dedications/bulk/reject', [DedicationModerationController::class, 'bulkReject'])
            ->name('dedications.bulkReject');

        // (the others)

        Route::get('dedications/create',[DedicationModerationController::class, 'create'])
            ->name('dedications.create');

        Route::post('dedications',[DedicationModerationController::class, 'store'])
            ->name('dedications.store');

        Route::get('dedications', [DedicationModerationController::class, 'index'])->name('dedications.index');
        Route::patch('dedications/{dedication}/approve', [DedicationModerationController::class, 'approve'])
            ->name('dedications.approve');
        Route::patch('dedications/{dedication}/reject', [DedicationModerationController::class, 'reject'])
            ->name('dedications.reject');

        Route::get('dedications/{dedication}',[DedicationModerationController::class, 'show'])
            ->name('dedications.show');

        Route::delete('dedications/{dedication}',[DedicationModerationController::class, 'destroy'])
            ->name('dedications.destroy');

        Route::get('dedications/{dedication}/comments',[DedicationModerationController::class, 'comments'])
            ->name('dedications.comments');

        Route::delete('dedication-comments/{comment}',[DedicationModerationController::class, 'destroyComment'])
                ->name('dedication-comments.destroy');

        


    });


Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1']) // no 'auth'
    ->name('verification.verify');

Route::get('/email-verified-success', function () {
    return view('verified-success'); // your blade file
});

// Admin login (web)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::view('/stripe/cancel', 'stripe.cancel')->name('stripe.cancel');

Route::get('/stripe/success', function (\Illuminate\Http\Request $request) {
    $sid = $request->query('session_id', '');
    return view('stripe.success', ['sessionId' => $sid]);
})->name('stripe.success');

Route::view('/checkout/success', 'checkout.success')->name('checkout.success');
Route::view('/checkout/cancel',  'checkout.cancel')->name('checkout.cancel');


Route::get('/billing/return', function () {
    return response('<h2>Thanks!</h2><p>You can close this window and return to the app.</p>', 200)
        ->header('Content-Type', 'text/html');
});


