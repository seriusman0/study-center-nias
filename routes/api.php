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
Route::post('/auth/google', [GoogleController::class, 'mobileLogin'])->middleware('throttle:10,1');

// Public
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'show']);
Route::get('/blogs/{blog}/comments', [CommentController::class, 'index']);
Route::get('/profil/{username}', [ProfileController::class, 'show']);
Route::get('/profil/{username}/cv', [CvController::class, 'showPublic']);
Route::get('/profil/{username}/kartu-nama', [\App\Http\Controllers\Api\ProfileExtraController::class, 'kartuNama']);
Route::get('/cabangs', [CabangController::class, 'index']);
Route::get('/cabangs/{slug}', [\App\Http\Controllers\Api\CabangController::class, 'show']);

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

    // Comments
    Route::post('/blogs/{blog}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Jurnal (student)
    Route::middleware('role:student')->prefix('jurnal')->group(function () {
        Route::get ('/today',   [\App\Http\Controllers\Api\JurnalApiController::class, 'today']);
        Route::post('/check',   [\App\Http\Controllers\Api\JurnalApiController::class, 'check']);
        Route::get ('/history', [\App\Http\Controllers\Api\JurnalApiController::class, 'history']);
    });

    // Kelas master
    Route::middleware('role:admin,mentor')->group(function () {
        Route::get   ('/kelas-master',         [\App\Http\Controllers\Api\KelasMasterController::class, 'index']);
        Route::post  ('/kelas-master',         [\App\Http\Controllers\Api\KelasMasterController::class, 'store']);
        Route::put   ('/kelas-master/{kelas}', [\App\Http\Controllers\Api\KelasMasterController::class, 'update']);
        Route::delete('/kelas-master/{kelas}', [\App\Http\Controllers\Api\KelasMasterController::class, 'destroy']);
    });

    // Mentor presensi
    Route::middleware('role:admin,mentor')->prefix('mentor-presensi')->group(function () {
        Route::get   ('/',                 [\App\Http\Controllers\Api\MentorPresensiController::class, 'index']);
        Route::post  ('/',                 [\App\Http\Controllers\Api\MentorPresensiController::class, 'store']);
        Route::get   ('/{mentorPresensi}', [\App\Http\Controllers\Api\MentorPresensiController::class, 'show']);
        Route::put   ('/{mentorPresensi}', [\App\Http\Controllers\Api\MentorPresensiController::class, 'update']);
        Route::delete('/{mentorPresensi}', [\App\Http\Controllers\Api\MentorPresensiController::class, 'destroy']);
    });

    // Presensi siswa
    Route::middleware('role:admin,mentor')->prefix('presensi')->group(function () {
        Route::get   ('/students/search', [\App\Http\Controllers\Api\PresensiController::class, 'searchStudents']);
        Route::get   ('/',                [\App\Http\Controllers\Api\PresensiController::class, 'index']);
        Route::post  ('/',                [\App\Http\Controllers\Api\PresensiController::class, 'store']);
        Route::get   ('/{presensi}',      [\App\Http\Controllers\Api\PresensiController::class, 'show']);
        Route::put   ('/{presensi}',      [\App\Http\Controllers\Api\PresensiController::class, 'update']);
        Route::delete('/{presensi}',      [\App\Http\Controllers\Api\PresensiController::class, 'destroy']);
    });

    // Admin only
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'stats']);

        // Users (full CRUD)
        Route::get   ('/users',                       [UserController::class, 'index']);
        Route::post  ('/users',                       [UserController::class, 'store']);
        Route::get   ('/users/{user}',                [UserController::class, 'show']);
        Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update']);
        Route::patch ('/users/{user}/role',           [UserController::class, 'updateRole']);
        Route::patch ('/users/{user}/toggle-active',  [UserController::class, 'toggleActive']);
        Route::delete('/users/{user}',                [UserController::class, 'destroy']);

        // Cabang
        Route::post  ('/cabangs',          [CabangController::class, 'store']);
        Route::put   ('/cabangs/{cabang}', [CabangController::class, 'update']);
        Route::delete('/cabangs/{cabang}', [CabangController::class, 'destroy']);

        // Roles & permissions (full)
        Route::get   ('/roles',                          [\App\Http\Controllers\Api\Admin\RoleController::class, 'index']);
        Route::post  ('/roles',                          [\App\Http\Controllers\Api\Admin\RoleController::class, 'store']);
        Route::put   ('/roles/{role}',                   [\App\Http\Controllers\Api\Admin\RoleController::class, 'update']);
        Route::post  ('/roles/{role}/permissions',       [\App\Http\Controllers\Api\Admin\RoleController::class, 'syncPermissions']);
        Route::delete('/roles/{role}',                   [\App\Http\Controllers\Api\Admin\RoleController::class, 'destroy']);
        Route::get   ('/permissions',                    [\App\Http\Controllers\Api\Admin\PermissionController::class, 'index']);
        Route::post  ('/permissions',                    [\App\Http\Controllers\Api\Admin\PermissionController::class, 'store']);
        Route::put   ('/permissions/{permission}',       [\App\Http\Controllers\Api\Admin\PermissionController::class, 'update']);
        Route::delete('/permissions/{permission}',       [\App\Http\Controllers\Api\Admin\PermissionController::class, 'destroy']);

        // Jurnal life items + student sync
        Route::get   ('/jurnal/life-items',                       [\App\Http\Controllers\Api\Admin\JurnalLifeItemController::class, 'index']);
        Route::post  ('/jurnal/life-items',                       [\App\Http\Controllers\Api\Admin\JurnalLifeItemController::class, 'store']);
        Route::put   ('/jurnal/life-items/{item}',                [\App\Http\Controllers\Api\Admin\JurnalLifeItemController::class, 'update']);
        Route::delete('/jurnal/life-items/{item}',                [\App\Http\Controllers\Api\Admin\JurnalLifeItemController::class, 'destroy']);
        Route::get   ('/jurnal/students/{student}/life-items',    [\App\Http\Controllers\Api\Admin\JurnalLifeItemController::class, 'studentAssignments']);
        Route::post  ('/jurnal/students/{student}/life-items',    [\App\Http\Controllers\Api\Admin\JurnalLifeItemController::class, 'syncStudent']);

        // Jurnal bible schedules
        Route::get   ('/jurnal/bible-schedules',                  [\App\Http\Controllers\Api\Admin\JurnalBibleScheduleController::class, 'index']);
        Route::post  ('/jurnal/bible-schedules',                  [\App\Http\Controllers\Api\Admin\JurnalBibleScheduleController::class, 'store']);
        Route::post  ('/jurnal/bible-schedules/bulk',             [\App\Http\Controllers\Api\Admin\JurnalBibleScheduleController::class, 'bulkStore']);
        Route::put   ('/jurnal/bible-schedules/{bibleSchedule}',  [\App\Http\Controllers\Api\Admin\JurnalBibleScheduleController::class, 'update']);
        Route::delete('/jurnal/bible-schedules/{bibleSchedule}',  [\App\Http\Controllers\Api\Admin\JurnalBibleScheduleController::class, 'destroy']);

        // Jurnal weekly verses
        Route::get   ('/jurnal/weekly-verses',                 [\App\Http\Controllers\Api\Admin\JurnalWeeklyVerseController::class, 'index']);
        Route::post  ('/jurnal/weekly-verses',                 [\App\Http\Controllers\Api\Admin\JurnalWeeklyVerseController::class, 'store']);
        Route::put   ('/jurnal/weekly-verses/{weeklyVerse}',   [\App\Http\Controllers\Api\Admin\JurnalWeeklyVerseController::class, 'update']);
        Route::delete('/jurnal/weekly-verses/{weeklyVerse}',   [\App\Http\Controllers\Api\Admin\JurnalWeeklyVerseController::class, 'destroy']);

        // Jurnal reports
        Route::get('/jurnal/reports',                     [\App\Http\Controllers\Api\Admin\JurnalReportController::class, 'index']);
        Route::get('/jurnal/reports/{student}',           [\App\Http\Controllers\Api\Admin\JurnalReportController::class, 'show']);
        Route::get('/jurnal/reports/{student}/export',    [\App\Http\Controllers\Api\Admin\JurnalReportController::class, 'export']);

        // Name tags
        Route::get ('/nametags',          [\App\Http\Controllers\Api\Admin\NameTagController::class, 'index']);
        Route::post('/nametags/generate', [\App\Http\Controllers\Api\Admin\NameTagController::class, 'generate']);

        // Mentor presensi admin reports
        Route::get('/mentor-presensi',                 [\App\Http\Controllers\Api\Admin\MentorPresensiAdminController::class, 'index']);
        Route::get('/mentor-presensi/reports',         [\App\Http\Controllers\Api\Admin\MentorPresensiAdminController::class, 'reports']);
        Route::get('/mentor-presensi/export/excel',    [\App\Http\Controllers\Api\Admin\MentorPresensiAdminController::class, 'exportExcel']);
        Route::get('/mentor-presensi/export/pdf',      [\App\Http\Controllers\Api\Admin\MentorPresensiAdminController::class, 'exportPdf']);

        // Blogs & comments moderation
        Route::get   ('/blogs',                [BlogController::class, 'index']);
        Route::delete('/blogs/{blog}',         [BlogController::class, 'destroy']);
        Route::get   ('/comments',             fn() => \App\Models\Comment::with('user:id,name,username', 'blog:id,title,slug')->latest()->paginate(20));
        Route::delete('/comments/{comment}',   [CommentController::class, 'destroy']);
    });
});
