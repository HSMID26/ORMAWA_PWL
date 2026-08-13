<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\AnnouncementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Route
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/organization', [\App\Http\Controllers\Api\OrganizationRegistrationController::class, 'store']);

// Protected Routes 
Route::middleware(['auth:sanctum,web'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Super Admin Dashboard Summary
    Route::get('/super-admin/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'superAdminSummary']);

    // Route Manajemen Organisasi
    Route::post('/organizations/{organization}/activate', [OrganizationController::class, 'activate']);
    Route::post('/organizations/{organization}/deactivate', [OrganizationController::class, 'deactivate']);
    
    // Period Management (Organization Periods)
    Route::get('/organizations/{organization}/periods', [\App\Http\Controllers\Api\OrganizationPeriodController::class, 'index']);
    Route::post('/organizations/{organization}/periods', [\App\Http\Controllers\Api\OrganizationPeriodController::class, 'store']);
    Route::get('/organization-periods/{period}', [\App\Http\Controllers\Api\OrganizationPeriodController::class, 'show']);
    Route::put('/organization-periods/{period}', [\App\Http\Controllers\Api\OrganizationPeriodController::class, 'update']);
    Route::post('/organization-periods/{period}/approve', [\App\Http\Controllers\Api\OrganizationPeriodController::class, 'approve']);
    Route::post('/organization-periods/{period}/reject', [\App\Http\Controllers\Api\OrganizationPeriodController::class, 'reject']);

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
    Route::apiResource('announcements', AnnouncementController::class)->names([
        'index'   => 'api.announcements.index',
        'store'   => 'api.announcements.store',
        'show'    => 'api.announcements.show',
        'update'  => 'api.announcements.update',
        'destroy' => 'api.announcements.destroy',
    ]);
    Route::post('/upload-image', [MediaController::class, 'upload'])->name('api.upload.image');

    Route::get('/activity-logs', [\App\Http\Controllers\Api\ActivityLogController::class, 'index']);

    // Super Admin: Organization Registration
    Route::get('/organization-registrations', [\App\Http\Controllers\Api\OrganizationRegistrationController::class, 'index']);
    Route::get('/organization-registrations/{id}', [\App\Http\Controllers\Api\OrganizationRegistrationController::class, 'show']);
    Route::post('/organization-registrations/{id}/approve', [\App\Http\Controllers\Api\OrganizationRegistrationController::class, 'approve']);
    Route::post('/organization-registrations/{id}/reject', [\App\Http\Controllers\Api\OrganizationRegistrationController::class, 'reject']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
});