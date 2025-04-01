<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DhikrController;
use App\Http\Controllers\AdhkarController;
use App\Http\Controllers\CollectionController;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::post('user/avatar', [AuthController::class, 'updateAvatar']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'get']);
    Route::patch('/user/name', [AuthController::class, 'changeName']);
    Route::patch('/user/password', [AuthController::class, 'changePassword']);
    Route::patch('/user/heart', [AuthController::class, 'updateHeartScore']);
    Route::post('/dhikr', [DhikrController::class, 'store']);    
});

// Adhkar routes
Route::get('/adhkars', [AdhkarController::class, 'index']);
Route::get('/collections', [CollectionController::class, 'index']);