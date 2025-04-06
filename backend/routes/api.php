<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DhikrController;
use App\Http\Controllers\AdhkarController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\BlogController;

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

// Donation routes
Route::prefix('donations')->group(function () {
    Route::get('/recent', [DonationController::class, 'getRecentDonations']);
    Route::post('/create', [DonationController::class, 'create']);
    Route::get('/verify', [DonationController::class, 'verify']);
});

// Blog routes
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);

// Admin blog routes (protected)
Route::middleware('auth:api')->group(function () {
    // Existing auth routes...
    
    // Blog admin (should be restricted to admins in a real app)
    Route::post('/blog', [BlogController::class, 'store']);
    Route::put('/blog/{id}', [BlogController::class, 'update']);
    Route::delete('/blog/{id}', [BlogController::class, 'destroy']);
    
    // File upload for blog
    Route::post('/blog/upload', [BlogController::class, 'uploadFile']);
});

// Admin blog routes
Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::get('/blog', [BlogController::class, 'adminIndex']);
    Route::get('/blog/{id}', [BlogController::class, 'adminShow']);
    
    // User management routes
    Route::get('/users', [AuthController::class, 'adminIndex']);
    Route::get('/users/{id}', [AuthController::class, 'adminShow']);
    Route::post('/users', [AuthController::class, 'adminStore']);
    Route::put('/users/{id}', [AuthController::class, 'adminUpdate']);
    Route::patch('/users/{id}/toggle-status', [AuthController::class, 'toggleStatus']);
});

Route::get('/blog/related/{id}', [BlogController::class, 'related']);