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
        Route::get('analytics', Panel\AnalyticsController::class)->name('analytics');
        Route::get('installs', Panel\InstallStatsController::class)->middleware('throttle:60,1')->name('installs');

        Route::get('feedback', [Panel\FeedbackController::class, 'index'])->name('feedback.index');
        Route::delete('feedback/{id}', [Panel\FeedbackController::class, 'destroy'])->name('feedback.destroy');

        Route::get('notices', [Panel\NoticeController::class, 'index'])->name('notices.index');
        Route::get('notices/create', [Panel\NoticeController::class, 'create'])->name('notices.create');
        Route::post('notices', [Panel\NoticeController::class, 'store'])->name('notices.store');
        Route::get('notices/{id}/edit', [Panel\NoticeController::class, 'edit'])->name('notices.edit');
        Route::put('notices/{id}', [Panel\NoticeController::class, 'update'])->name('notices.update');
        Route::patch('notices/{id}/toggle', [Panel\NoticeController::class, 'toggle'])->name('notices.toggle');
        Route::delete('notices/{id}', [Panel\NoticeController::class, 'destroy'])->name('notices.destroy');

        Route::get('scholars', [Panel\ScholarController::class, 'index'])->name('scholars.index');
        Route::get('scholars/create', [Panel\ScholarController::class, 'create'])->name('scholars.create');
        Route::post('scholars', [Panel\ScholarController::class, 'store'])->name('scholars.store');
        Route::get('scholars/{id}/edit', [Panel\ScholarController::class, 'edit'])->name('scholars.edit');
        Route::put('scholars/{id}', [Panel\ScholarController::class, 'update'])->name('scholars.update');
        Route::patch('scholars/{id}/toggle', [Panel\ScholarController::class, 'toggle'])->name('scholars.toggle');
        Route::patch('scholars/{id}/move/{direction}', [Panel\ScholarController::class, 'move'])->name('scholars.move');
        Route::delete('scholars/{id}', [Panel\ScholarController::class, 'destroy'])->name('scholars.destroy');

        Route::get('scholars/{scholar}/lectures', [Panel\LectureController::class, 'index'])->name('lectures.index');
        Route::get('scholars/{scholar}/lectures/create', [Panel\LectureController::class, 'create'])->name('lectures.create');
        Route::post('scholars/{scholar}/lectures', [Panel\LectureController::class, 'store'])->name('lectures.store');
        Route::get('scholars/{scholar}/lectures/{id}/edit', [Panel\LectureController::class, 'edit'])->name('lectures.edit');
        Route::put('scholars/{scholar}/lectures/{id}', [Panel\LectureController::class, 'update'])->name('lectures.update');
        Route::patch('scholars/{scholar}/lectures/{id}/toggle', [Panel\LectureController::class, 'toggle'])->name('lectures.toggle');
        Route::patch('scholars/{scholar}/lectures/{id}/move/{direction}', [Panel\LectureController::class, 'move'])->name('lectures.move');
        Route::delete('scholars/{scholar}/lectures/{id}', [Panel\LectureController::class, 'destroy'])->name('lectures.destroy');

        Route::get('versions', [Panel\AppVersionController::class, 'index'])->name('versions.index');
        Route::get('versions/create', [Panel\AppVersionController::class, 'create'])->name('versions.create');
        Route::post('versions', [Panel\AppVersionController::class, 'store'])->name('versions.store');
        Route::get('versions/{id}/edit', [Panel\AppVersionController::class, 'edit'])->name('versions.edit');
        Route::put('versions/{id}', [Panel\AppVersionController::class, 'update'])->name('versions.update');
        Route::patch('versions/{id}/toggle', [Panel\AppVersionController::class, 'toggle'])->name('versions.toggle');
        Route::delete('versions/{id}', [Panel\AppVersionController::class, 'destroy'])->name('versions.destroy');

        Route::get('users', [Panel\UserController::class, 'index'])->name('users.index');
        Route::patch('users/{id}/toggle', [Panel\UserController::class, 'toggle'])->name('users.toggle');

        Route::get('profile', [Panel\ProfileController::class, 'edit'])->name('profile');
        Route::put('profile/name', [Panel\ProfileController::class, 'updateName'])->name('profile.name');
        Route::put('profile/password', [Panel\ProfileController::class, 'updatePassword'])->middleware('throttle:5,1')->name('profile.password');
    });
});
