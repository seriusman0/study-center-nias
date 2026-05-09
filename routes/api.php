<?php

use App\Http\Controllers\Admin\CabangController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\GuestAuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/auth/register', [GuestAuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/auth/login', [GuestAuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/auth/google', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Public
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'show']);
Route::get('/blogs/{blog}/comments', [CommentController::class, 'index']);
Route::get('/profil/{username}', [ProfileController::class, 'show']);
Route::get('/profil/{username}/cv', [CvController::class, 'showPublic']);
Route::get('/cabangs', [CabangController::class, 'index']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [GuestAuthController::class, 'logout']);
    Route::get('/me', [GuestAuthController::class, 'me']);

    // Profile
    Route::put('/profile', [ProfileController::class, 'update']);

    // CV
    Route::get('/cv', [CvController::class, 'show']);
    Route::put('/cv', [CvController::class, 'update']);

    // Blog (internal staff can write)
    Route::middleware('role:admin,fulltimer,mentor,student')->group(function () {
        Route::post('/blogs', [BlogController::class, 'store']);
        Route::put('/blogs/{blog}', [BlogController::class, 'update']);
        Route::delete('/blogs/{blog}', [BlogController::class, 'destroy']);
    });

    // Comments (all authenticated users)
    Route::post('/blogs/{blog}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Admin only
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'stats']);

        Route::get('/users', [UserController::class, 'index']);
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole']);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        Route::post('/cabangs', [CabangController::class, 'store']);
        Route::put('/cabangs/{cabang}', [CabangController::class, 'update']);
        Route::delete('/cabangs/{cabang}', [CabangController::class, 'destroy']);

        Route::get('/blogs', [BlogController::class, 'index']);
        Route::get('/comments', fn() => \App\Models\Comment::with('user:id,name,username', 'blog:id,title,slug')->latest()->paginate(20));
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    });
});
