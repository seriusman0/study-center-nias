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
use App\Http\Controllers\Web\Admin\MataPelajaranController;
use App\Http\Controllers\Web\Admin\RoleAdminController;
use App\Http\Controllers\Web\Admin\PermissionAdminController;
use App\Http\Controllers\Web\Admin\NameTagController;
use App\Http\Controllers\Web\PresensiController;
use App\Http\Controllers\Web\JurnalController;
use App\Http\Controllers\Web\Admin\JurnalBibleScheduleController;
use App\Http\Controllers\Web\Admin\JurnalWeeklyVerseController;
use App\Http\Controllers\Web\Admin\JurnalLifeItemController;
use App\Http\Controllers\Web\Admin\JurnalReportController;
use App\Http\Controllers\Web\Admin\KelasMasterController;
use App\Http\Controllers\Web\MentorPresensiController;
use App\Http\Controllers\Web\Admin\MentorPresensiAdminController;
use App\Http\Controllers\Web\Admin\CertificateTemplateController;
use App\Http\Controllers\Web\Admin\IssuedCertificateController;
use App\Http\Controllers\Web\Admin\PendaftaranAdminController;
use App\Http\Controllers\Web\PendaftaranController;
use App\Http\Controllers\Web\BerandaController;

// Public pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog', [BlogWebController::class, 'index'])->name('blog.index');
Route::get('/cabang', [CabangWebController::class, 'index'])->name('cabang.index');
Route::get('/cabang/{slug}', [CabangWebController::class, 'show'])->name('cabang.show');

// Student registration (public)
Route::get('/pendaftaran', [PendaftaranController::class, 'pilihCabang'])->name('pendaftaran.pilih-cabang');
// perbaikan/{token} MUST be before {cabang:slug} to avoid slug conflict
Route::get('/pendaftaran/perbaikan/{token}', [PendaftaranController::class, 'showUpdateForm'])->name('pendaftaran.update.form');
Route::post('/pendaftaran/perbaikan/{token}', [PendaftaranController::class, 'processUpdate'])->name('pendaftaran.update.store');
// Per-cabang multi-step flow
Route::get('/pendaftaran/{cabang:slug}', [PendaftaranController::class, 'showForm'])->name('pendaftaran.form');
Route::post('/pendaftaran/{cabang:slug}', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
Route::get('/pendaftaran/{cabang:slug}/preview', [PendaftaranController::class, 'preview'])->name('pendaftaran.preview');
Route::post('/pendaftaran/{cabang:slug}/konfirmasi', [PendaftaranController::class, 'konfirmasi'])->name('pendaftaran.konfirmasi');
Route::get('/pendaftaran/{cabang:slug}/sukses', [PendaftaranController::class, 'sukses'])->name('pendaftaran.sukses');
Route::get('/pendaftaran/{cabang:slug}/kartu', [PendaftaranController::class, 'kartu'])->name('pendaftaran.kartu');
Route::get('/cek-pendaftaran/{username}', [PendaftaranController::class, 'cekStatus'])->name('pendaftaran.cek');

// Auth pages (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login']);
    Route::get('/daftar', [AuthWebController::class, 'pilihRegistrasi'])->name('register');
    Route::get('/daftar/tamu', [AuthWebController::class, 'registerForm'])->name('register.tamu');
    Route::post('/daftar/tamu', [AuthWebController::class, 'register']);
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
    Route::get('/report', [PresensiController::class, 'report'])->name('report');
    Route::get('/{presensi}', [PresensiController::class, 'show'])->name('show');
    Route::get('/{presensi}/edit', [PresensiController::class, 'edit'])->name('edit');
    Route::put('/{presensi}', [PresensiController::class, 'update'])->name('update');
    Route::delete('/{presensi}', [PresensiController::class, 'destroy'])->name('destroy');
    Route::post('/{presensi}/scan', [PresensiController::class, 'scanStudent'])->name('scan');
});

// Admin panel — dashboard + read-only users list shared with mentor
Route::middleware(['auth', 'role:admin,mentor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');

    Route::get('/pendaftaran', [PendaftaranAdminController::class, 'index'])->name('pendaftaran.index');
    Route::get('/pendaftaran/{user}', [PendaftaranAdminController::class, 'show'])->name('pendaftaran.show');
    Route::patch('/pendaftaran/{user}/validasi', [PendaftaranAdminController::class, 'validasi'])->name('pendaftaran.validasi');
    Route::post('/pendaftaran/{user}/generate-update-link', [PendaftaranAdminController::class, 'generateUpdateLink'])->name('pendaftaran.generate-update-link');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::get('/users/{user}/qr-print', [AdminController::class, 'printQr'])->name('users.qr-print');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
    Route::post('/users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/cabangs', [AdminController::class, 'cabangs'])->name('cabangs');
    Route::post('/cabangs', [AdminController::class, 'storeCabang'])->name('cabangs.store');
    Route::post('/cabangs/{cabang}', [AdminController::class, 'updateCabang'])->name('cabangs.update');
    Route::delete('/cabangs/{cabang}', [AdminController::class, 'deleteCabang'])->name('cabangs.delete');

    Route::get('/mata-pelajaran', [MataPelajaranController::class, 'index'])->name('mata-pelajaran');
    Route::post('/mata-pelajaran', [MataPelajaranController::class, 'store'])->name('mata-pelajaran.store');
    Route::post('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'update'])->name('mata-pelajaran.update');
    Route::post('/mata-pelajaran/{mataPelajaran}/toggle', [MataPelajaranController::class, 'toggleActive'])->name('mata-pelajaran.toggle');
    Route::delete('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'destroy'])->name('mata-pelajaran.destroy');
    Route::get('/blogs', [AdminController::class, 'blogs'])->name('blogs');
    Route::delete('/blogs/{blog}', [AdminController::class, 'deleteBlog'])->name('blogs.delete');

    Route::get('/roles', [RoleAdminController::class, 'index'])->name('roles');
    Route::post('/roles', [RoleAdminController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleAdminController::class, 'update'])->name('roles.update');
    Route::post('/roles/{role}/permissions', [RoleAdminController::class, 'syncPermissions'])->name('roles.permissions');
    Route::delete('/roles/{role}', [RoleAdminController::class, 'destroy'])->name('roles.delete');

    Route::get('/nametags', [NameTagController::class, 'index'])->name('nametags');
    Route::post('/nametags/generate', [NameTagController::class, 'generate'])->name('nametags.generate');

    Route::prefix('nametag-templates')->name('nametag-templates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\Admin\NameTagTemplateController::class, 'index'])->name('index');
        Route::get('/{template}/edit', [\App\Http\Controllers\Web\Admin\NameTagTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [\App\Http\Controllers\Web\Admin\NameTagTemplateController::class, 'update'])->name('update');
        Route::post('/{template}/duplicate', [\App\Http\Controllers\Web\Admin\NameTagTemplateController::class, 'duplicate'])->name('duplicate');
        Route::delete('/{template}', [\App\Http\Controllers\Web\Admin\NameTagTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{template}/preview', [\App\Http\Controllers\Web\Admin\NameTagTemplateController::class, 'preview'])->name('preview');
    });

    Route::get('/permissions', [PermissionAdminController::class, 'index'])->name('permissions');
    Route::post('/permissions', [PermissionAdminController::class, 'store'])->name('permissions.store');
    Route::put('/permissions/{permission}', [PermissionAdminController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionAdminController::class, 'destroy'])->name('permissions.delete');

});

// Beranda (student + college)
Route::middleware(['auth', 'role:student,college'])->group(function () {
    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
});

// Jurnal (student-facing)
Route::middleware(['auth', 'role:student'])->prefix('jurnal')->name('jurnal.')->group(function () {
    Route::get('/', [JurnalController::class, 'index'])->name('index');
    Route::post('/toggle', [JurnalController::class, 'toggle'])->name('toggle');
    Route::post('/foto', [JurnalController::class, 'uploadFoto'])->name('foto.upload');
    Route::delete('/foto', [JurnalController::class, 'deleteFoto'])->name('foto.delete');
});

// Jurnal admin/mentor management
Route::middleware(['auth', 'role:admin,mentor'])->prefix('admin/jurnal')->name('admin.jurnal.')->group(function () {
    Route::get   ('life-items',                          [JurnalLifeItemController::class, 'index'])->name('life-items.index');
    Route::post  ('life-items',                          [JurnalLifeItemController::class, 'store'])->name('life-items.store');
    Route::post  ('life-items/assign-all',               [JurnalLifeItemController::class, 'assignAllToAllStudents'])->name('life-items.assign-all');
    Route::put   ('life-items/{item}',                   [JurnalLifeItemController::class, 'update'])->name('life-items.update');
    Route::delete('life-items/{item}',                   [JurnalLifeItemController::class, 'destroy'])->name('life-items.destroy');
    Route::get   ('students/{student}/life-items',       [JurnalLifeItemController::class, 'studentAssignments'])->name('life-items.student');
    Route::post  ('students/{student}/life-items',       [JurnalLifeItemController::class, 'syncStudent'])->name('life-items.sync');

    Route::get('reports',                                [JurnalReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{student}',                      [JurnalReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{student}/export',               [JurnalReportController::class, 'export'])->name('reports.export');
});

// Mentor self-attendance
Route::middleware(['auth', 'role:mentor'])->prefix('mentor/presensi')->name('mentor-presensi.')->group(function () {
    Route::get('/',                [MentorPresensiController::class, 'index'])->name('index');
    Route::get('/create',          [MentorPresensiController::class, 'create'])->name('create');
    Route::post('/',               [MentorPresensiController::class, 'store'])->name('store');
    Route::get('/{presensi}/edit', [MentorPresensiController::class, 'edit'])->name('edit');
    Route::put('/{presensi}',      [MentorPresensiController::class, 'update'])->name('update');
    Route::delete('/{presensi}',   [MentorPresensiController::class, 'destroy'])->name('destroy');
    Route::get('/api/kelas',       [MentorPresensiController::class, 'searchKelas'])->name('kelas.search');
});

// Shared kelas master search (for /presensi/create form)
Route::middleware(['auth', 'role:admin,mentor'])
    ->get('/presensi/api/kelas-master', [MentorPresensiController::class, 'searchKelas'])
    ->name('presensi.kelas-master.search');

// Kelas master (admin+mentor full CRUD)
Route::middleware(['auth', 'role:admin,mentor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get   ('/kelas-master',          [KelasMasterController::class, 'index'])->name('kelas-master.index');
    Route::post  ('/kelas-master',          [KelasMasterController::class, 'store'])->name('kelas-master.store');
    Route::put   ('/kelas-master/{kelas}',  [KelasMasterController::class, 'update'])->name('kelas-master.update');
    Route::delete('/kelas-master/{kelas}',  [KelasMasterController::class, 'destroy'])->name('kelas-master.destroy');
});

// Admin: mentor-presensi list, reports, export
Route::middleware(['auth', 'role:admin,mentor'])->prefix('admin/mentor-presensi')->name('admin.mentor-presensi.')->group(function () {
    Route::get('/',             [MentorPresensiAdminController::class, 'index'])->name('index');
    Route::get('/reports',      [MentorPresensiAdminController::class, 'reports'])->name('reports');
    Route::get('/export/excel', [MentorPresensiAdminController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf',   [MentorPresensiAdminController::class, 'exportPdf'])->name('export.pdf');
});

// Certificate Generator (admin only)
Route::middleware(['auth', 'role:admin'])->prefix('admin/certificates')->name('admin.certificates.')->group(function () {
    Route::get   ('templates',                    [CertificateTemplateController::class, 'index'])         ->name('templates.index');
    Route::get   ('templates/create',             [CertificateTemplateController::class, 'create'])        ->name('templates.create');
    Route::post  ('templates',                    [CertificateTemplateController::class, 'store'])         ->name('templates.store');
    Route::post  ('templates/preview',            [CertificateTemplateController::class, 'preview'])       ->name('templates.preview');
    Route::get   ('templates/{template}/preview', [CertificateTemplateController::class, 'previewSaved'])  ->name('templates.preview.saved');
    Route::get   ('templates/{template}/edit',    [CertificateTemplateController::class, 'edit'])          ->name('templates.edit');
    Route::put   ('templates/{template}',         [CertificateTemplateController::class, 'update'])        ->name('templates.update');
    Route::delete('templates/{template}',         [CertificateTemplateController::class, 'destroy'])       ->name('templates.destroy');

    Route::get   ('issued',                    [IssuedCertificateController::class, 'index'])     ->name('issued.index');
    Route::post  ('issued',                    [IssuedCertificateController::class, 'store'])     ->name('issued.store');
    Route::get   ('issued/{cert}/download',    [IssuedCertificateController::class, 'download'])  ->name('issued.download');
    Route::delete('issued/{cert}',             [IssuedCertificateController::class, 'destroy'])   ->name('issued.destroy');
});

// Scholarship Journal (student)
use App\Http\Controllers\Web\ScholarshipJournalController;
Route::middleware(['auth', 'role:student'])->prefix('jurnal-mahasiswa')->name('scholarship-journal.')->group(function () {
    Route::get('/',          [ScholarshipJournalController::class, 'index'])->name('index');
    Route::get('/buat',      [ScholarshipJournalController::class, 'create'])->name('create');
    Route::post('/',         [ScholarshipJournalController::class, 'store'])->name('store');
    Route::get('/{id}',      [ScholarshipJournalController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ScholarshipJournalController::class, 'edit'])->name('edit');
    Route::put('/{id}',      [ScholarshipJournalController::class, 'update'])->name('update');
});

// College: jurnal harian (daily check-in) + review dashboard
use App\Http\Controllers\Web\College\JournalReviewController;
use App\Http\Controllers\Web\College\CollegeJurnalController;
Route::middleware(['auth', 'role:college,admin'])->prefix('college')->name('college.')->group(function () {
    Route::get('/dashboard',              [JournalReviewController::class, 'dashboard'])->name('dashboard');
    Route::get('/jurnal/{id}',            [JournalReviewController::class, 'show'])->name('journal.show');
    Route::post('/jurnal/{id}/review',    [JournalReviewController::class, 'review'])->name('journal.review');
});

Route::middleware(['auth', 'role:college'])->prefix('jurnal-college')->name('college-jurnal.')->group(function () {
    Route::get('/',              [CollegeJurnalController::class, 'index'])->name('index');
    Route::get('/user/{userId}', [CollegeJurnalController::class, 'showOther'])->name('other');
    Route::post('/toggle',       [CollegeJurnalController::class, 'toggle'])->name('toggle');
    Route::post('/foto',         [CollegeJurnalController::class, 'uploadFoto'])->name('foto.upload');
    Route::delete('/foto',       [CollegeJurnalController::class, 'deleteFoto'])->name('foto.delete');
});

// Admin: Jurnal College (progress, laporan, alkitab, items)
use App\Http\Controllers\Web\Admin\CollegeJurnalAdminController;
use App\Http\Controllers\Web\Admin\CollegeBibleController;
use App\Http\Controllers\Web\Admin\CollegeBibleScheduleController;
use App\Http\Controllers\Web\Admin\CollegeItemController;
Route::middleware(['auth', 'role:admin,mentor'])->prefix('admin/jurnal-college')->name('admin.jurnal-college.')->group(function () {
    Route::get('/',                         [CollegeJurnalAdminController::class, 'dashboard'])->name('index');
    Route::get('/laporan',                  [CollegeJurnalAdminController::class, 'index'])->name('laporan');
    Route::get('/laporan/{user}',           [CollegeJurnalAdminController::class, 'show'])->name('show');
    Route::get('/laporan/{user}/export',    [CollegeJurnalAdminController::class, 'export'])->name('export');

    // Jadwal Pembacaan Alkitab — CRUD
    Route::get('/bible-schedules',                    [CollegeBibleScheduleController::class, 'index'])->name('bible-schedules.index');
    Route::get('/bible-schedules/create',             [CollegeBibleScheduleController::class, 'create'])->name('bible-schedules.create');
    Route::post('/bible-schedules',                   [CollegeBibleScheduleController::class, 'store'])->name('bible-schedules.store');
    Route::get('/bible-schedules/{bibleSchedule}/edit', [CollegeBibleScheduleController::class, 'edit'])->name('bible-schedules.edit');
    Route::put('/bible-schedules/{bibleSchedule}',    [CollegeBibleScheduleController::class, 'update'])->name('bible-schedules.update');
    Route::delete('/bible-schedules/{bibleSchedule}', [CollegeBibleScheduleController::class, 'destroy'])->name('bible-schedules.destroy');
    Route::post('/bible-schedules/{bibleSchedule}/set-active', [CollegeBibleScheduleController::class, 'setActive'])->name('bible-schedules.set-active');

    // Detail hari per jadwal
    Route::get('/bible',                    [CollegeBibleController::class, 'index'])->name('bible');
    Route::put('/bible/anchor',             [CollegeBibleController::class, 'updateAnchor'])->name('bible.anchor');
    Route::post('/bible/import',            [CollegeBibleController::class, 'importJson'])->name('bible.import');
    Route::put('/bible/{item}',             [CollegeBibleController::class, 'update'])->name('bible.update');

    Route::get('/items',                    [CollegeItemController::class, 'index'])->name('items.index');
    Route::post('/items',                   [CollegeItemController::class, 'store'])->name('items.store');
    Route::put('/items/{item}',             [CollegeItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}',          [CollegeItemController::class, 'destroy'])->name('items.destroy');
});

use App\Http\Controllers\Web\ScholarshipTeenager\ScholarshipTeenagerJurnalController;
use App\Http\Controllers\Web\Admin\ScholarshipTeenagerJurnalAdminController;
use App\Http\Controllers\Web\Admin\ScholarshipTeenagerItemAdminController;

Route::middleware(['auth', 'role:scholarship_teenager'])->prefix('jurnal-scholarship-teenager')->name('scholarship-teenager-jurnal.')->group(function () {
    Route::get('/',              [ScholarshipTeenagerJurnalController::class, 'index'])->name('index');
    Route::get('/user/{userId}', [ScholarshipTeenagerJurnalController::class, 'showOther'])->name('other');
    Route::post('/toggle',       [ScholarshipTeenagerJurnalController::class, 'toggle'])->name('toggle');
    Route::post('/foto',         [ScholarshipTeenagerJurnalController::class, 'uploadFoto'])->name('foto.upload');
    Route::delete('/foto',       [ScholarshipTeenagerJurnalController::class, 'deleteFoto'])->name('foto.delete');
});

Route::middleware(['auth', 'role:admin,mentor'])->prefix('admin/jurnal-scholarship-teenager')->name('admin.jurnal-scholarship-teenager.')->group(function () {
    Route::get('/',                      [ScholarshipTeenagerJurnalAdminController::class, 'dashboard'])->name('index');
    Route::get('/laporan',               [ScholarshipTeenagerJurnalAdminController::class, 'index'])->name('laporan');
    Route::get('/laporan/{user}',        [ScholarshipTeenagerJurnalAdminController::class, 'show'])->name('show');
    Route::get('/laporan/{user}/export', [ScholarshipTeenagerJurnalAdminController::class, 'export'])->name('export');
    Route::get('/items',                 [ScholarshipTeenagerItemAdminController::class, 'index'])->name('items.index');
    Route::post('/items',                [ScholarshipTeenagerItemAdminController::class, 'store'])->name('items.store');
    Route::put('/items/{item}',          [ScholarshipTeenagerItemAdminController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}',       [ScholarshipTeenagerItemAdminController::class, 'destroy'])->name('items.destroy');
});

Route::view('/privacy-policy', 'privacy');
