<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityParticipantController;
use App\Http\Controllers\ActivityRegistrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserController;
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

Route::prefix('feeds')->group(function () {
    Route::get('/', [FeedController::class, 'index']);
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/get-auth', [AuthController::class, 'getAuth']);
        Route::get('/get-profile', [UserProfileController::class, 'getProfile']);
    });

    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'index']);
        Route::get('/user/{username}', [UserProfileController::class, 'getByUsername']);
        Route::post('/', [UserProfileController::class, 'store']);
        Route::post('/user', [UserProfileController::class, 'updateUserAndProfile']);
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
        Route::post('/', [FeedController::class, 'store']);
        Route::get('/user/{username}', [FeedController::class, 'getByUsername']);
        Route::put('/{id}', [FeedController::class, 'update']);
        Route::delete('/{id}', [FeedController::class, 'destroy']);
    });

    Route::prefix('registrations')->group(function () {
        Route::get('/', [ActivityRegistrationController::class, 'index']);
        Route::post('/', [ActivityRegistrationController::class, 'store']);
        Route::put('/{id}', [ActivityRegistrationController::class, 'update']);
        Route::get('/{activityId}/check', [ActivityRegistrationController::class, 'checkApplied']);
    });

    Route::prefix('participants')->group(function () {
        Route::get('/', [ActivityParticipantController::class, 'index']);
        Route::delete('/{id}', [ActivityParticipantController::class, 'destroy']);
    });

    Route::prefix('follows')->group(function () {
        Route::post('/{username}', [FollowController::class, 'follow']);
        Route::delete('/{username}', [FollowController::class, 'unfollow']);
        Route::get('/{username}/followers', [FollowController::class, 'followersByUsername']);
        Route::get('/{username}/followings', [FollowController::class, 'followingsByUsername']);
        Route::get('/{username}/counts', [FollowController::class, 'countFollowersAndFollowings']);
        Route::get('/{username}/is-following', [FollowController::class, 'isFollowing']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/suggestions', [UserController::class, 'suggestUsers']);
        Route::get('/', [UserController::class, 'getAllUsers']);
    });
});
