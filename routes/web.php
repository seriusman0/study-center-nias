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
use App\Http\Controllers\Web\Admin\RoleAdminController;
use App\Http\Controllers\Web\Admin\PermissionAdminController;
use App\Http\Controllers\Web\Admin\NameTagController;
use App\Http\Controllers\Web\PresensiController;
use App\Http\Controllers\Web\JurnalController;
use App\Http\Controllers\Web\Admin\JurnalBibleScheduleController;
use App\Http\Controllers\Web\Admin\JurnalWeeklyVerseController;
use App\Http\Controllers\Web\Admin\JurnalLifeItemController;
use App\Http\Controllers\Web\Admin\JurnalReportController;

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
    Route::post('/blog/upload-image', [BlogWebController::class, 'uploadImage'])->name('blog.upload-image');
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

// Presensi (admin + mentor)
Route::middleware(['auth', 'role:admin,mentor'])->prefix('presensi')->name('presensi.')->group(function () {
    Route::get('/', [PresensiController::class, 'index'])->name('index');
    Route::get('/create', [PresensiController::class, 'create'])->name('create');
    Route::post('/', [PresensiController::class, 'store'])->name('store');
    Route::get('/api/students', [PresensiController::class, 'searchStudents'])->name('students.search');
    Route::get('/{presensi}', [PresensiController::class, 'show'])->name('show');
    Route::get('/{presensi}/edit', [PresensiController::class, 'edit'])->name('edit');
    Route::put('/{presensi}', [PresensiController::class, 'update'])->name('update');
    Route::delete('/{presensi}', [PresensiController::class, 'destroy'])->name('destroy');
});

// Admin panel — dashboard + read-only users list shared with mentor
Route::middleware(['auth', 'role:admin,mentor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
    Route::post('/users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/cabangs', [AdminController::class, 'cabangs'])->name('cabangs');
    Route::post('/cabangs', [AdminController::class, 'storeCabang'])->name('cabangs.store');
    Route::post('/cabangs/{cabang}', [AdminController::class, 'updateCabang'])->name('cabangs.update');
    Route::delete('/cabangs/{cabang}', [AdminController::class, 'deleteCabang'])->name('cabangs.delete');
    Route::get('/blogs', [AdminController::class, 'blogs'])->name('blogs');
    Route::delete('/blogs/{blog}', [AdminController::class, 'deleteBlog'])->name('blogs.delete');

    Route::get('/roles', [RoleAdminController::class, 'index'])->name('roles');
    Route::post('/roles', [RoleAdminController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleAdminController::class, 'update'])->name('roles.update');
    Route::post('/roles/{role}/permissions', [RoleAdminController::class, 'syncPermissions'])->name('roles.permissions');
    Route::delete('/roles/{role}', [RoleAdminController::class, 'destroy'])->name('roles.delete');

    Route::get('/nametags', [NameTagController::class, 'index'])->name('nametags');
    Route::post('/nametags/generate', [NameTagController::class, 'generate'])->name('nametags.generate');

    Route::get('/permissions', [PermissionAdminController::class, 'index'])->name('permissions');
    Route::post('/permissions', [PermissionAdminController::class, 'store'])->name('permissions.store');
    Route::put('/permissions/{permission}', [PermissionAdminController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionAdminController::class, 'destroy'])->name('permissions.delete');
});

// Jurnal (student-facing)
Route::middleware(['auth', 'role:student'])->prefix('jurnal')->name('jurnal.')->group(function () {
    Route::get('/', [JurnalController::class, 'index'])->name('index');
    Route::post('/toggle', [JurnalController::class, 'toggle'])->name('toggle');
});

// Jurnal admin/mentor management
Route::middleware(['auth', 'role:admin,mentor'])->prefix('admin/jurnal')->name('admin.jurnal.')->group(function () {
    Route::get   ('bible-schedules',                     [JurnalBibleScheduleController::class, 'index'])->name('bible-schedules.index');
    Route::post  ('bible-schedules',                     [JurnalBibleScheduleController::class, 'store'])->name('bible-schedules.store');
    Route::put   ('bible-schedules/{bibleSchedule}',     [JurnalBibleScheduleController::class, 'update'])->name('bible-schedules.update');
    Route::delete('bible-schedules/{bibleSchedule}',     [JurnalBibleScheduleController::class, 'destroy'])->name('bible-schedules.destroy');
    Route::post  ('bible-schedules/bulk',                [JurnalBibleScheduleController::class, 'bulkStore'])->name('bible-schedules.bulk');

    Route::get   ('weekly-verses',                       [JurnalWeeklyVerseController::class, 'index'])->name('weekly-verses.index');
    Route::post  ('weekly-verses',                       [JurnalWeeklyVerseController::class, 'store'])->name('weekly-verses.store');
    Route::put   ('weekly-verses/{weeklyVerse}',         [JurnalWeeklyVerseController::class, 'update'])->name('weekly-verses.update');
    Route::delete('weekly-verses/{weeklyVerse}',         [JurnalWeeklyVerseController::class, 'destroy'])->name('weekly-verses.destroy');

    Route::get   ('life-items',                          [JurnalLifeItemController::class, 'index'])->name('life-items.index');
    Route::post  ('life-items',                          [JurnalLifeItemController::class, 'store'])->name('life-items.store');
    Route::put   ('life-items/{item}',                   [JurnalLifeItemController::class, 'update'])->name('life-items.update');
    Route::delete('life-items/{item}',                   [JurnalLifeItemController::class, 'destroy'])->name('life-items.destroy');
    Route::get   ('students/{student}/life-items',       [JurnalLifeItemController::class, 'studentAssignments'])->name('life-items.student');
    Route::post  ('students/{student}/life-items',       [JurnalLifeItemController::class, 'syncStudent'])->name('life-items.sync');

    Route::get('reports',                                [JurnalReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{student}',                      [JurnalReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{student}/export',               [JurnalReportController::class, 'export'])->name('reports.export');
});
