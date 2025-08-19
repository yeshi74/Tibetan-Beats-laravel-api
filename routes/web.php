<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SongAdminController;
use App\Http\Controllers\Admin\AlbumController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\PlaylistController;


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


Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('albums', AlbumController::class);
    Route::resource('songs', SongAdminController::class);
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


Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('playlists', PlaylistController::class);
});


