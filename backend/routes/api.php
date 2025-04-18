<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DhikrController;
use App\Http\Controllers\AdhkarController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::middleware('auth:api')->group(function () {
    // User related routes with UserController
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('user/avatar', [UserController::class, 'updateAvatar']);
    Route::patch('/user/name', [UserController::class, 'updateName']);
    Route::patch('/user/password', [UserController::class, 'updatePassword']);
    Route::patch('/user/heart', [UserController::class, 'updateHeartScore']);
    Route::get('/user/stats', [UserController::class, 'getUserStats']);
    
    // Auth related routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'get']);
    
    // Other routes
    Route::post('/dhikr', [DhikrController::class, 'store']);
    Route::post('/adhkar/favorites/{id}', [AdhkarController::class, 'toggleFavorite']);
    Route::get('/adhkar/favorites', [AdhkarController::class, 'getFavorites']);
});

// Adhkar routes
Route::get('/adhkars', [AdhkarController::class, 'index']);
Route::get('/collections', [CollectionController::class, 'index']);
Route::get('/collections/{slug}', [CollectionController::class, 'show']);

// Donation routes
Route::prefix('donations')->group(function () {
    Route::get('/recent', [DonationController::class, 'getRecentDonations']);
    Route::post('/create', [DonationController::class, 'create']);
    Route::get('/verify', [DonationController::class, 'verify']);
});

// Blog routes
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);

// Admin blog routes
Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::post('/blog', [BlogController::class, 'store']);
    Route::put('/blog/{id}', [BlogController::class, 'update']);
    Route::delete('/blog/{id}', [BlogController::class, 'destroy']);
    
    // File upload for blog
    Route::post('/blog/upload', [BlogController::class, 'uploadFile']);
    Route::get('/blog', [BlogController::class, 'adminIndex']);
    Route::get('/blog/{id}', [BlogController::class, 'adminShow']);
    
    // User management routes
    Route::get('/users', [AuthController::class, 'adminIndex']);
    Route::get('/users/{id}', [AuthController::class, 'adminShow']);
    Route::post('/users', [AuthController::class, 'adminStore']);
    Route::put('/users/{id}', [AuthController::class, 'adminUpdate']);
    Route::patch('/users/{id}/toggle-status', [AuthController::class, 'toggleStatus']);

    // Collection management routes
    Route::get('/collections', [CollectionController::class, 'adminIndex']);
    Route::post('/collections', [CollectionController::class, 'adminStore']);
    Route::get('/collections/{id}', [CollectionController::class, 'adminShow']);
    Route::put('/collections/{id}', [CollectionController::class, 'adminUpdate']);
    Route::delete('/collections/{id}', [CollectionController::class, 'adminDestroy']);

    // Adhkar management routes
    Route::get('/adhkar', [AdhkarController::class, 'adminIndex']);
    Route::post('/adhkar', [AdhkarController::class, 'adminStore']);
    Route::get('/adhkar/{id}', [AdhkarController::class, 'adminShow']);
    Route::put('/adhkar/{id}', [AdhkarController::class, 'adminUpdate']);
    Route::delete('/adhkar/{id}', [AdhkarController::class, 'adminDestroy']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Media management routes
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{id}', [MediaController::class, 'show']);
    Route::post('/media/upload', [MediaController::class, 'upload']);
    Route::put('/media/{id}', [MediaController::class, 'update']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);
    Route::post('/media/delete-multiple', [MediaController::class, 'deleteMultiple']);

    // Logs routes
    Route::get('/logs', [App\Http\Controllers\Admin\LogController::class, 'index']);
    Route::get('/logs/{id}', [App\Http\Controllers\Admin\LogController::class, 'show']);
    Route::delete('/logs', [App\Http\Controllers\Admin\LogController::class, 'destroy']);
});

Route::get('/blog/related/{id}', [BlogController::class, 'related']);