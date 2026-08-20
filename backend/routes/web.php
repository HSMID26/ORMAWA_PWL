<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostWebController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\MediaWebController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\CommitteeWebController;
use App\Http\Controllers\ActivityWebController;
use App\Http\Controllers\UserWebController;
use App\Http\Controllers\OrganizationWebController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rute Profile bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute Manajemen Postings Ormawa (TipTap Editor)
    Route::get('/posts', [PostWebController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostWebController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostWebController::class, 'show'])->name('posts.show');
    Route::get('/posts/{post}/edit', [PostWebController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostWebController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostWebController::class, 'destroy'])->name('posts.destroy');

    // Endpoint Upload & Save khusus Web CMS Blade (Breeze Session)
    Route::post('/upload-image', [MediaController::class, 'upload'])->name('upload.image');
    Route::post('/posts-store', [PostController::class, 'store']);
    
    // Rute Manajemen Media Galeri
    Route::get('/media', [MediaWebController::class, 'index'])->name('media.index');

    // Kategori & Tag Routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);

    // Rute Manajemen Pengurus Ormawa
    Route::get('/committees', [CommitteeController::class, 'index']);
    Route::post('/committees', [CommitteeController::class, 'store']);
    Route::post('/committees/{id}', [CommitteeController::class, 'update']); // Menggunakan POST untuk FormData upload foto
    Route::delete('/committees/{id}', [CommitteeController::class, 'destroy']);

    // Halaman Blade Management Pengurus
    Route::get('/committees-view', [CommitteeWebController::class, 'index'])->name('committees.index');

    // Halaman Blade Dashboard Agenda Kegiatan & CRUD Web
    Route::get('/activities-view', [ActivityWebController::class, 'index']);
    Route::get('/activities', [ActivityWebController::class, 'index'])->name('activities.index');
    Route::post('/activities', [ActivityWebController::class, 'store'])->name('activities.store');
    Route::put('/activities/{activity}', [ActivityWebController::class, 'update'])->name('activities.update');
    Route::delete('/activities/{activity}', [ActivityWebController::class, 'destroy'])->name('activities.destroy');

    // Halaman Blade Management User / Pengurus Akun (Fitur 9)
    Route::get('/users-view', [UserWebController::class, 'index']);
    Route::get('/users', [UserWebController::class, 'index'])->name('users.index');
    Route::post('/users', [UserWebController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserWebController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [UserWebController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}', [UserWebController::class, 'destroy'])->name('users.destroy');

    // Halaman Blade Pengaturan Organisasi & Modul (Fitur 10)
    Route::get('/organization/settings', [OrganizationWebController::class, 'settings'])->name('organization.settings');
    Route::post('/organization/settings', [OrganizationWebController::class, 'updateSettings'])->name('organization.settings.update');
});

// Public iCal Feed Route (Google / Apple Calendar Sync)
Route::get('/calendar.ics', [ActivityWebController::class, 'icalFeed'])->name('calendar.ics');

require __DIR__.'/auth.php';