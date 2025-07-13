<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SongApiController;


Route::get('/songs', [SongApiController::class, 'index']);
Route::get('/songs/{id}', [SongApiController::class, 'show']);
Route::post('/songs', [SongApiController::class, 'store']);
