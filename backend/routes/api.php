<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\MediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes 
Route::middleware(['auth:sanctum,web'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route Manajemen Organisasi
    Route::apiResource('organizations', OrganizationController::class)->names([
        'index'   => 'api.organizations.index',
        'store'   => 'api.organizations.store',
        'show'    => 'api.organizations.show',
        'update'  => 'api.organizations.update',
        'destroy' => 'api.organizations.destroy',
    ]);
    Route::apiResource('users', UserController::class)->names([
        'index'   => 'api.users.index',
        'store'   => 'api.users.store',
        'show'    => 'api.users.show',
        'update'  => 'api.users.update',
        'destroy' => 'api.users.destroy',
    ]);
    Route::apiResource('activities', ActivityController::class)->names([
        'index'   => 'api.activities.index',
        'store'   => 'api.activities.store',
        'show'    => 'api.activities.show',
        'update'  => 'api.activities.update',
        'destroy' => 'api.activities.destroy',
    ]);
    Route::apiResource('posts', PostController::class)->names([
        'index'   => 'api.posts.index',
        'store'   => 'api.posts.store',
        'show'    => 'api.posts.show',
        'update'  => 'api.posts.update',
        'destroy' => 'api.posts.destroy',
    ]);
    Route::post('/upload-image', [MediaController::class, 'upload'])->name('api.upload.image');
});