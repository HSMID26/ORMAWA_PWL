<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostWebController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\PostController;
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

    // 🚀 Rute Manajemen Postings Ormawa (TipTap Editor)
    Route::get('/posts', [PostWebController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostWebController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostWebController::class, 'show'])->name('posts.show');
    Route::get('/posts/{post}/edit', [PostWebController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostWebController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostWebController::class, 'destroy'])->name('posts.destroy');

    Route::post('/upload-image', [MediaController::class, 'upload'])->name('upload.image');
});

require __DIR__.'/auth.php';