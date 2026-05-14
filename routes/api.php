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
    Route::post('/auth/refresh', [GuestAuthController::class, 'refresh']);
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

    // Jurnal (student app sync)
    Route::middleware('role:student')->prefix('jurnal')->group(function () {
        Route::get ('/today',   [\App\Http\Controllers\Api\JurnalApiController::class, 'today']);
        Route::post('/check',   [\App\Http\Controllers\Api\JurnalApiController::class, 'check']);
        Route::get ('/history', [\App\Http\Controllers\Api\JurnalApiController::class, 'history']);
    });

    // Kelas master (read for admin+mentor, write for admin)
    Route::middleware('role:admin,mentor')->get('/kelas-master', [\App\Http\Controllers\Api\KelasMasterController::class, 'index']);
    Route::middleware('role:admin')->group(function () {
        Route::post  ('/kelas-master',         [\App\Http\Controllers\Api\KelasMasterController::class, 'store']);
        Route::put   ('/kelas-master/{kelas}', [\App\Http\Controllers\Api\KelasMasterController::class, 'update']);
        Route::delete('/kelas-master/{kelas}', [\App\Http\Controllers\Api\KelasMasterController::class, 'destroy']);
    });

    // Mentor self-attendance (mentor own + admin all)
    Route::middleware('role:admin,mentor')->prefix('mentor-presensi')->group(function () {
        Route::get   ('/',                       [\App\Http\Controllers\Api\MentorPresensiController::class, 'index']);
        Route::post  ('/',                       [\App\Http\Controllers\Api\MentorPresensiController::class, 'store']);
        Route::get   ('/{mentorPresensi}',       [\App\Http\Controllers\Api\MentorPresensiController::class, 'show']);
        Route::put   ('/{mentorPresensi}',       [\App\Http\Controllers\Api\MentorPresensiController::class, 'update']);
        Route::delete('/{mentorPresensi}',       [\App\Http\Controllers\Api\MentorPresensiController::class, 'destroy']);
    });

    // Presensi (mentor records students)
    Route::middleware('role:admin,mentor')->prefix('presensi')->group(function () {
        Route::get   ('/students/search',  [\App\Http\Controllers\Api\PresensiController::class, 'searchStudents']);
        Route::get   ('/',                 [\App\Http\Controllers\Api\PresensiController::class, 'index']);
        Route::post  ('/',                 [\App\Http\Controllers\Api\PresensiController::class, 'store']);
        Route::get   ('/{presensi}',       [\App\Http\Controllers\Api\PresensiController::class, 'show']);
        Route::put   ('/{presensi}',       [\App\Http\Controllers\Api\PresensiController::class, 'update']);
        Route::delete('/{presensi}',       [\App\Http\Controllers\Api\PresensiController::class, 'destroy']);
    });

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
