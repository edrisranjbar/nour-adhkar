<?php

use App\Http\Controllers\Panel;
use App\Http\Middleware\PanelAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// App admin panel (Blade, session guard "admin")
Route::prefix('admin')->name('panel.')->group(function () {
    Route::get('login', [Panel\AuthController::class, 'show'])->name('login');
    Route::post('login', [Panel\AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(PanelAdmin::class)->group(function () {
        Route::post('logout', [Panel\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Panel\DashboardController::class)->name('dashboard');

        Route::get('feedback', [Panel\FeedbackController::class, 'index'])->name('feedback.index');
        Route::delete('feedback/{id}', [Panel\FeedbackController::class, 'destroy'])->name('feedback.destroy');

        Route::get('notices', [Panel\NoticeController::class, 'index'])->name('notices.index');
        Route::get('notices/create', [Panel\NoticeController::class, 'create'])->name('notices.create');
        Route::post('notices', [Panel\NoticeController::class, 'store'])->name('notices.store');
        Route::get('notices/{id}/edit', [Panel\NoticeController::class, 'edit'])->name('notices.edit');
        Route::put('notices/{id}', [Panel\NoticeController::class, 'update'])->name('notices.update');
        Route::patch('notices/{id}/toggle', [Panel\NoticeController::class, 'toggle'])->name('notices.toggle');
        Route::delete('notices/{id}', [Panel\NoticeController::class, 'destroy'])->name('notices.destroy');

        Route::get('users', [Panel\UserController::class, 'index'])->name('users.index');
        Route::patch('users/{id}/toggle', [Panel\UserController::class, 'toggle'])->name('users.toggle');

        Route::get('profile', [Panel\ProfileController::class, 'edit'])->name('profile');
        Route::put('profile/name', [Panel\ProfileController::class, 'updateName'])->name('profile.name');
        Route::put('profile/password', [Panel\ProfileController::class, 'updatePassword'])->middleware('throttle:5,1')->name('profile.password');
    });
});
