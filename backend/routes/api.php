<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DhikrController;
use App\Http\Controllers\AdhkarController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\Api\LeagueController;
use App\Http\Controllers\CommentController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Public content routes
Route::get('adhkars', [AdhkarController::class, 'index']);
Route::get('collections', [CollectionController::class, 'index']);
Route::get('collections/{slug}', [CollectionController::class, 'show']);
Route::get('posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::get('/posts/{id}/views', [PostController::class, 'getViews']);
Route::get('/posts/views/total', [PostController::class, 'getTotalViews']);
Route::get('posts/{post}/related', [PostController::class, 'related']);
Route::get('posts/{postId}/comments', [CommentController::class, 'getPostComments']);
Route::post('comments', [CommentController::class, 'store']);

// Public donation routes
Route::prefix('donations')->group(function () {
    Route::get('recent', [DonationController::class, 'getRecentDonations']);
    Route::post('create', [DonationController::class, 'create']);
    Route::get('verify', [DonationController::class, 'verify']);
});

// Authenticated routes
Route::middleware('auth:api')->group(function () {
    // User profile routes
    Route::prefix('user')->group(function () {
        Route::get('profile', [UserController::class, 'getProfile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::post('avatar', [UserController::class, 'updateAvatar']);
        Route::patch('name', [UserController::class, 'updateName']);
        Route::patch('password', [UserController::class, 'updatePassword']);
        Route::patch('heart', [UserController::class, 'updateHeartScore']);
        Route::get('stats', [UserController::class, 'getUserStats']);
        Route::get('dashboard', [UserController::class, 'getDashboard']);
        Route::get('completed-days', [UserController::class, 'getCompletedDays']);
    });

    // Auth routes
    Route::get('user', [AuthController::class, 'get']);

    // Dhikr routes
    Route::get('dhikr', [DhikrController::class, 'index']);
    Route::post('dhikr', [DhikrController::class, 'store']);
    Route::post('adhkar/favorites/{id}', [AdhkarController::class, 'toggleFavorite']);
    Route::get('adhkar/favorites', [AdhkarController::class, 'getFavorites']);

    // Authenticated donation routes
    Route::get('donations/user', [DonationController::class, 'getUserDonations']);

    // Badge routes
    Route::get('/badges', [BadgeController::class, 'index']);
    Route::get('/user/badges', [BadgeController::class, 'userBadges']);
    Route::post('/user/check-badges', [BadgeController::class, 'checkAndAwardBadges']);
    Route::post('/user/badges/{badge}', [BadgeController::class, 'awardBadge']);
    Route::delete('/user/badges/{badge}', [BadgeController::class, 'removeBadge']);

    // League progress route
    Route::get('/user/league-progress', [LeagueController::class, 'getProgress']);
});

// Admin routes
Route::middleware(['auth:api', AdminMiddleware::class])
    ->prefix('admin')
    ->group(function () {
        // Posts management
        Route::prefix('posts')->group(function () {
            Route::get('/', [PostController::class, 'adminIndex']);
            Route::post('/', [PostController::class, 'store']);
            Route::put('{post}', [PostController::class, 'update']);
            Route::delete('{post}', [PostController::class, 'destroy']);
            Route::post('{post}/featured-image', [PostController::class, 'uploadFeaturedImage']);
            Route::post('upload', [PostController::class, 'uploadFile']);
        });

        // User management
        Route::prefix('users')->group(function () {
            Route::get('/', [AuthController::class, 'adminIndex']);
            Route::get('{id}', [AuthController::class, 'adminShow']);
            Route::post('/', [AuthController::class, 'adminStore']);
            Route::put('{id}', [AuthController::class, 'adminUpdate']);
            Route::patch('{id}/toggle-status', [AuthController::class, 'toggleStatus']);
        });

        // Collections management
        Route::prefix('collections')->group(function () {
            Route::get('/', [CollectionController::class, 'adminIndex']);
            Route::post('/', [CollectionController::class, 'adminStore']);
            Route::get('{id}', [CollectionController::class, 'adminShow']);
            Route::put('{id}', [CollectionController::class, 'adminUpdate']);
            Route::delete('{id}', [CollectionController::class, 'adminDestroy']);
        });

        // Adhkar management
        Route::prefix('adhkar')->group(function () {
            Route::get('/', [AdhkarController::class, 'adminIndex']);
            Route::post('/', [AdhkarController::class, 'adminStore']);
            Route::get('{id}', [AdhkarController::class, 'adminShow']);
            Route::put('{id}', [AdhkarController::class, 'adminUpdate']);
            Route::delete('{id}', [AdhkarController::class, 'adminDestroy']);
        });

        // Categories management
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::get('{category}', [CategoryController::class, 'show']);
            Route::put('{category}', [CategoryController::class, 'update']);
            Route::delete('{category}', [CategoryController::class, 'destroy']);
        });

        // Media management
        Route::prefix('media')->group(function () {
            Route::get('/', [MediaController::class, 'index']);
            Route::get('{id}', [MediaController::class, 'show']);
            Route::post('upload', [MediaController::class, 'upload']);
            Route::put('{id}', [MediaController::class, 'update']);
            Route::delete('{id}', [MediaController::class, 'destroy']);
            Route::post('delete-multiple', [MediaController::class, 'deleteMultiple']);
        });

        // Logs management
        Route::prefix('logs')->group(function () {
            Route::get('/', [LogController::class, 'index']);
            Route::get('export', [LogController::class, 'export']);
            Route::get('{id}', [LogController::class, 'show']);
            Route::delete('/', [LogController::class, 'destroy']);
        });

        // Comments management
        Route::prefix('comments')->group(function () {
            Route::get('/', [CommentController::class, 'index']);
            Route::put('{comment}', [CommentController::class, 'update']);
            Route::delete('{comment}', [CommentController::class, 'destroy']);
            Route::get('pending/count', [CommentController::class, 'getPendingCount']);
        });
    });