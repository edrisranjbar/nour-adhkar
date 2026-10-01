<?php

use App\Http\Controllers\ProgressSyncController;
use Illuminate\Support\Facades\Route;

Route::post('progress/sync', [ProgressSyncController::class, 'sync'])
    ->middleware(['auth:api', 'throttle:10,1']);
