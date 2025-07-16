<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SongApiController;
use App\Http\Controllers\Api\AlbumApiController;


Route::get('/songs', [SongApiController::class, 'index']);
Route::get('/songs/{id}', [SongApiController::class, 'show']);
Route::post('/songs', [SongApiController::class, 'store']);
Route::put('/songs/{id}', [SongApiController::class, 'update']);
Route::delete('/songs/{id}', [SongApiController::class, 'destroy']);


Route::get('/albums', [AlbumApiController::class, 'index']);
Route::post('/albums', [AlbumApiController::class, 'store']);
Route::get('/albums/{id}', [AlbumApiController::class, 'show']);
Route::put('/albums/{id}', [AlbumApiController::class, 'update']);
Route::delete('/albums/{id}', [AlbumApiController::class, 'destroy']);
