<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SongApiController;
use App\Http\Controllers\Api\AlbumApiController;
use App\Http\Controllers\Api\AuthController;


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