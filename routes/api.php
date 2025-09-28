<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'index']);
        Route::post('/', [UserProfileController::class, 'store']);
        Route::patch('/', [UserProfileController::class, 'update']);
        Route::delete('/', [UserProfileController::class, 'destroy']);
    });

    Route::prefix('activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::get('/user/{username}', [ActivityController::class, 'getActivityUser']);
        Route::get('/{id}', [ActivityController::class, 'getActivityDetail']);
        Route::post('/', [ActivityController::class, 'store']);
        Route::put('/{id}', [ActivityController::class, 'update']);
        Route::delete('/{id}', [ActivityController::class, 'destroy']);
    });

    Route::prefix('feeds')->group(function () {
        Route::get('/', [FeedController::class, 'index']);
        Route::post('/', [FeedController::class, 'store']);
        Route::get('/user/{username}', [FeedController::class, 'getByUsername']);
        Route::put('/{id}', [FeedController::class, 'update']);
        Route::delete('/{id}', [FeedController::class, 'destroy']);
    });
});
