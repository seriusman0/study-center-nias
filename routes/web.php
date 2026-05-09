<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\BlogWebController;
use App\Http\Controllers\Web\CabangWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\GoogleWebController;
use App\Http\Controllers\Web\ProfileWebController;
use App\Http\Controllers\Web\CvWebController;
use App\Http\Controllers\Web\CommentWebController;
use App\Http\Controllers\Web\Admin\AdminController;

// Public pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog', [BlogWebController::class, 'index'])->name('blog.index');
Route::get('/cabang', [CabangWebController::class, 'index'])->name('cabang.index');
Route::get('/cabang/{slug}', [CabangWebController::class, 'show'])->name('cabang.show');

// Auth pages (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login']);
    Route::get('/daftar', [AuthWebController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [AuthWebController::class, 'register']);
});
Route::get('/auth/google', [GoogleWebController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleWebController::class, 'callback']);
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout')->middleware('auth');

// Profile edit must be before wildcard /profil/{username}
Route::middleware('auth')->group(function () {
    Route::get('/profil/edit', [ProfileWebController::class, 'edit'])->name('profile.edit');
    Route::post('/profil/edit', [ProfileWebController::class, 'update'])->name('profile.update');
    Route::get('/cv/edit', [CvWebController::class, 'edit'])->name('cv.edit');
    Route::post('/cv/edit', [CvWebController::class, 'update'])->name('cv.update');
});

// Public profile routes (after static /profil/edit)
Route::get('/profil/{username}', [ProfileWebController::class, 'show'])->name('profile.show');
Route::get('/profil/{username}/cv', [CvWebController::class, 'show'])->name('cv.show');
Route::get('/profil/{username}/kartu-nama', [CvWebController::class, 'kartuNama'])->name('cv.kartu-nama');

// Staff-only blog management
Route::middleware(['auth', 'role:admin,fulltimer,mentor,student'])->group(function () {
    Route::get('/blog/tulis', [BlogWebController::class, 'create'])->name('blog.create');
    Route::post('/blog', [BlogWebController::class, 'store'])->name('blog.store');
    Route::get('/blog/{slug}/edit', [BlogWebController::class, 'edit'])->name('blog.edit');
    Route::post('/blog/{blog}', [BlogWebController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{blog}', [BlogWebController::class, 'destroy'])->name('blog.destroy');
});

// Must be after static /blog/tulis and /blog/{slug}/edit
Route::get('/blog/{slug}', [BlogWebController::class, 'show'])->name('blog.show');

// Comments (auth)
Route::middleware('auth')->group(function () {
    Route::post('/blog/{blog}/comments', [CommentWebController::class, 'store'])->name('comment.store');
    Route::delete('/comments/{comment}', [CommentWebController::class, 'destroy'])->name('comment.destroy');
});

// Admin panel
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
    Route::post('/users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/cabangs', [AdminController::class, 'cabangs'])->name('cabangs');
    Route::post('/cabangs', [AdminController::class, 'storeCabang'])->name('cabangs.store');
    Route::post('/cabangs/{cabang}', [AdminController::class, 'updateCabang'])->name('cabangs.update');
    Route::delete('/cabangs/{cabang}', [AdminController::class, 'deleteCabang'])->name('cabangs.delete');
    Route::get('/blogs', [AdminController::class, 'blogs'])->name('blogs');
    Route::delete('/blogs/{blog}', [AdminController::class, 'deleteBlog'])->name('blogs.delete');
});
