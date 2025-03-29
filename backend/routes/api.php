<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/user', [AuthController::class, 'get'])->middleware('auth:api');
Route::patch('/user/name', [AuthController::class, 'changeName'])->middleware('auth:api');
Route::patch('/user/password', [AuthController::class, 'changePassword'])->middleware('auth:api');
Route::patch('/user/heart', [AuthController::class, 'updateHeartScore'])->middleware('auth:api');
Route::middleware('auth:api')->post('/user/heart-score', function (Request $request) {
    $user = $request->user();
    $request->validate([
        'increment' => 'required|integer|min:1|max:100'
    ]);
    $increment = $request->input('increment', 10);

    $user->heart_score += $increment;
    $user->save();

    return response()->json([
        'success' => true,
        'newScore' => $user->heart_score
    ]);
});