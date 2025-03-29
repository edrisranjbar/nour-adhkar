<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/user', [AuthController::class, 'get'])->middleware('auth:api');
Route::patch('/user/name', [AuthController::class, 'changeName'])->middleware('auth:api');
Route::patch('/user/password', [AuthController::class, 'changePassword'])->middleware('auth:api');
Route::patch('/user/heart', [AuthController::class, 'updateHeartScore'])->middleware('auth:api');