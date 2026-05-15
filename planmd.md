# Audit Parity Fitur Android vs Web — StudyCenter App

## Context

User minta audit apakah Android app (`id.scnias.app`) sudah 100% punya fitur sama dengan web (Laravel `studycenter-app`). Tujuan: identifikasi gap + daftar API yang perlu dikonsumsi/dikembangkan supaya seluruh fitur web bisa dijalankan di mobile.

Audit berbasis enumerasi route Laravel (`routes/web.php` + `routes/api.php`) vs Retrofit service interface di Android (`android/app/src/main/java/id/scnias/app/data/api/`).

---

## Ringkasan

**Status:** Android baru cover ~55-60% fitur web. Bagian student/mentor sebagian besar lengkap. Bagian **admin** banyak yang hilang (jurnal master data, roles/permissions, name tags, reports, exports).

| Area | Status |
|------|--------|
| Auth dasar (login/register/logout/me) | ✅ Parity |
| Google OAuth | ❌ Hilang |
| Refresh token | ❌ Hilang |
| Blog CRUD + comments | ✅ Parity |
| Profile (view/edit) | ✅ Parity |
| **CV (Curriculum Vitae)** | ❌ Hilang total |
| Cabang public (list) | ✅ |
| Cabang detail by slug | ❌ Hilang |
| Kartu nama (name card profile) | ❌ Hilang |
| Jurnal student (today/check/history) | ✅ Parity |
| Mentor presensi (self attendance) | ✅ Parity |
| Presensi siswa (student attendance) | ✅ Parity |
| Kelas master CRUD | ✅ Parity |
| Admin dashboard stats | ✅ Parity |
| Admin users (list + toggle + delete) | ⚠️ Partial — create/edit/update/role hilang |
| Admin cabang CRUD | ✅ Parity |
| Admin life items CRUD | ✅ Parity |
| **Admin bible schedules CRUD** | ❌ Hilang |
| **Admin weekly verses CRUD** | ❌ Hilang |
| **Admin sync life items ke student** | ❌ Hilang |
| **Admin jurnal reports + export** | ❌ Hilang |
| Admin roles (read-only) | ⚠️ Partial — write hilang |
| **Admin permissions CRUD** | ❌ Hilang |
| **Admin role sync permissions** | ❌ Hilang |
| **Admin name tags (list + generate)** | ❌ Hilang |
| Admin blog list + delete | ⚠️ Pakai endpoint blog umum, tidak ada admin/blogs khusus |
| **Admin comments list (cross-blog)** | ❌ Hilang |
| **Admin mentor presensi reports + export Excel/PDF** | ❌ Hilang |
| **Blog upload-image (separate endpoint)** | ❌ Hilang (hanya multipart blog) |

---

## Detail Gap — API yang Perlu Dikembangkan/Dikonsumsi

Format: `[METHOD] path` → fitur. Tanda 🔧 = endpoint sudah ada di Laravel, Android tinggal konsumsi. Tanda 🆕 = perlu dicek apakah endpoint Laravel sudah ada; jika tidak, perlu dibuat di backend dulu.

### 1. Auth & Session

- 🔧 `POST /api/auth/refresh` — refresh token sebelum expired. Web ada (`AuthApiController` via `auth:sanctum`). Tambah di `AuthApi.kt`.
- 🆕 Google OAuth mobile flow — web pakai `/auth/google` + `/auth/google/callback` (browser redirect). Untuk mobile butuh endpoint baru `POST /api/auth/google` yang terima `id_token` dari Google Sign-In SDK Android → kembalikan Sanctum token.

### 2. Profile / CV / Kartu Nama

- 🔧 `GET /api/cv` — ambil CV user login (Web: `CvController@show`).
- 🔧 `POST /api/cv` / `PUT /api/cv` — create/update CV.
- 🔧 `GET /api/profil/{username}/cv` — lihat CV public user.
- 🆕 `GET /api/profil/{username}/kartu-nama` — name card public (web hanya HTML view; tambah JSON response atau biarkan WebView).
- 🆕 `GET /api/cabangs/{slug}` — detail cabang (web ada `/cabang/{slug}` hanya HTML; tambah API JSON).

Android impact: tambah `CvScreen`, `CvFormScreen`, `KartuNamaScreen`, `CabangDetailScreen` + Retrofit `CvApi`, extend `CabangApi`.

### 3. Blog

- 🔧 `POST /api/blogs/upload-image` (atau `/blog/upload-image` jika dipertahankan) — upload inline image untuk WYSIWYG. Web pakai `/blog/upload-image`. Bila Android pakai multipart full body, tidak wajib; tapi rekomendasi tambah agar bisa edit konten kaya secara independen.

### 4. Admin — User Management

- 🔧 `POST /api/admin/users` — create user (web: `UserController@store`).
- 🔧 `PUT/PATCH /api/admin/users/{user}` — full update (selain toggle).
- 🔧 `PATCH /api/admin/users/{user}/role` — assign/change role.

Android impact: extend `AdminApi.kt` + `AdminUserFormScreen` (create/edit), role picker.

### 5. Admin — Roles & Permissions

Semua endpoint web masih `web.php`, perlu di-expose ke `api.php`:

- 🆕 `GET /api/admin/roles` (sudah ada read-only di Android) — pastikan return permissions list per role.
- 🆕 `POST /api/admin/roles` — create role.
- 🆕 `PUT /api/admin/roles/{role}` — update role.
- 🆕 `DELETE /api/admin/roles/{role}` — delete role.
- 🆕 `POST /api/admin/roles/{role}/permissions` — sync permissions.
- 🆕 `GET /api/admin/permissions` — list permissions.
- 🆕 `POST /api/admin/permissions` — create.
- 🆕 `PUT /api/admin/permissions/{id}` — update.
- 🆕 `DELETE /api/admin/permissions/{id}` — delete.

Android impact: `RoleAdminScreen`, `PermissionAdminScreen`, `RolePermissionSyncScreen`.

### 6. Admin — Jurnal Master Data

Bible schedules — semua via `web.php`, perlu API:

- 🆕 `GET /api/admin/jurnal/bible-schedules`
- 🆕 `POST /api/admin/jurnal/bible-schedules`
- 🆕 `PUT /api/admin/jurnal/bible-schedules/{id}`
- 🆕 `DELETE /api/admin/jurnal/bible-schedules/{id}`
- 🆕 `POST /api/admin/jurnal/bible-schedules/bulk` — bulk import.

Weekly verses:

- 🆕 `GET/POST/PUT/DELETE /api/admin/jurnal/weekly-verses[/{id}]`

Life items per student (sync):

- 🆕 `GET /api/admin/jurnal/students/{student}/life-items`
- 🆕 `POST /api/admin/jurnal/students/{student}/life-items` — assign default+custom items.

Android impact: `BibleScheduleAdminScreen`, `WeeklyVerseAdminScreen`, `StudentLifeItemAssignScreen`.

### 7. Admin — Jurnal Reports

- 🆕 `GET /api/admin/jurnal/reports` — list students with summary.
- 🆕 `GET /api/admin/jurnal/reports/{student}` — detailed report (range filter).
- 🆕 `GET /api/admin/jurnal/reports/{student}/export` — download Excel/PDF (binary; mobile bisa pakai DownloadManager atau open in browser).

### 8. Admin — Name Tags

- 🆕 `GET /api/admin/nametags`
- 🆕 `POST /api/admin/nametags/generate` — generate untuk filter (cabang/kelas).

### 9. Admin — Mentor Presensi Reports

- 🆕 `GET /api/admin/mentor-presensi` — list semua presensi mentor (lintas user).
- 🆕 `GET /api/admin/mentor-presensi/reports` — agregasi.
- 🆕 `GET /api/admin/mentor-presensi/export/excel`
- 🆕 `GET /api/admin/mentor-presensi/export/pdf`

### 10. Admin — Blog & Comment Moderation

- 🔧 `GET /api/admin/blogs` — list (sudah ada di web).
- 🔧 `DELETE /api/admin/blogs/{id}` — delete sebagai admin.
- 🔧 `GET /api/admin/comments` — semua komentar lintas blog.
- 🔧 `DELETE /api/admin/comments/{id}`.

---

## File Backend yang Disentuh (Laravel)

- `routes/api.php` — tambah grup admin lengkap (jurnal master, roles, permissions, name tags, reports, mentor presensi reports).
- `app/Http/Controllers/Api/` — tambah/extend controller API counterpart dari web controller:
  - `Api/Admin/RoleController.php` (baru)
  - `Api/Admin/PermissionController.php` (baru)
  - `Api/Admin/JurnalBibleScheduleController.php` (baru)
  - `Api/Admin/JurnalWeeklyVerseController.php` (baru)
  - `Api/Admin/JurnalLifeItemController.php` — extend untuk sync-to-student
  - `Api/Admin/JurnalReportController.php` (baru)
  - `Api/Admin/NameTagController.php` (baru)
  - `Api/Admin/MentorPresensiAdminController.php` (baru)
  - `Api/Admin/UserController.php` — tambah `store`, full `update`
  - `Api/CvController.php` (baru / extend)
  - `Api/Auth/GoogleController.php` — tambah `POST /api/auth/google` untuk id_token flow
- Tetap pakai `auth:sanctum` + `role:admin` middleware.

## File Android yang Disentuh

- `android/app/src/main/java/id/scnias/app/data/api/` — tambah service interface baru: `CvApi.kt`, `RoleAdminApi.kt`, `PermissionAdminApi.kt`, `BibleScheduleApi.kt`, `WeeklyVerseApi.kt`, `JurnalReportApi.kt`, `NameTagApi.kt`, `MentorPresensiReportApi.kt`. Extend `AuthApi.kt` (refresh, google), `AdminApi.kt` (user create/update/role).
- `android/app/src/main/java/id/scnias/app/data/dto/` — DTO + envelope baru per resource.
- `android/app/src/main/java/id/scnias/app/data/repo/` — repository baru per area.
- `android/app/src/main/java/id/scnias/app/ui/` — screen baru: Cv, CvForm, KartuNama, CabangDetail, AdminUserForm, RoleAdmin, PermissionAdmin, BibleScheduleAdmin, WeeklyVerseAdmin, StudentLifeItemAssign, JurnalReport, NameTag, MentorPresensiReport.
- Navigation graph (Compose NavHost) — tambah route per screen baru.
- DI module Hilt — bind service + repo baru.

---

## Verification

1. Backend: jalankan `php artisan route:list --path=api` setelah perubahan → pastikan semua endpoint baru terdaftar dengan middleware `auth:sanctum` + `role:*`.
2. Test API per endpoint via Postman/HTTPie dengan token admin/mentor/student.
3. Android: bangun APK debug → login per role → akses tiap screen baru → verifikasi CRUD end-to-end.
4. Test export endpoints di Android dengan membuka file hasil download (Excel/PDF) di file manager.
5. Regression: pastikan endpoint existing (jurnal, presensi, blog) tetap jalan.

## Skala Pekerjaan

Estimasi kasar:
- Backend API baru: ~12-15 controller + ~30+ endpoint baru.
- Android: ~15+ screen baru, ~10 service interface baru, ~25+ DTO.

Disarankan kerjakan per **modul** (bukan per endpoint) supaya bisa dirilis bertahap. Urutan prioritas yang masuk akal:
2. Admin User full CRUD + role assignment (pengurus penting).
3. CV + Kartu nama + Cabang detail (parity public).
4. Roles & Permissions admin.
5. Jurnal master data admin (bible schedules, weekly verses, sync life items).
6. Reports + exports (jurnal & mentor presensi).
7. Name tags generator.
8. Blog/comment moderation admin views.
