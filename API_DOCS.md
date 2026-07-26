# API Documentation — Study Center NIAS

**Base URL:** `https://studycenter.seriusman.shop/api`  
**Local Dev:** `http://localhost:8889/api`  
**Format:** JSON (`Accept: application/json`, `Content-Type: application/json`)

---

## Authentication

Sanctum token-based. Semua endpoint protected butuh header:
```
Authorization: Bearer {token}
```

Token didapat dari `POST /auth/login` atau `POST /auth/google`.

---

## Test Results (2026-07-21)

| Status | Keterangan |
|--------|-----------|
| ✓ 200 | OK |
| ✓ 422 | Validation error (expected) |
| ✗ 401 | Unauthorized |
| ✗ 404 | Not found |

---

## 1. Auth

### POST /auth/register
Public. Throttle: 5 req/menit.

**Body:**
```json
{
  "name": "string",
  "email": "string",
  "password": "string",
  "password_confirmation": "string"
}
```
**Response:** `201` token + user | `422` validation error

---

### POST /auth/login
Public. Throttle: 10 req/menit.

**Body:**
```json
{
  "email": "string",
  "password": "string"
}
```
**Response:**
```json
{
  "token": "1|abc...",
  "user": { "id": 1, "name": "...", "roles": [...] }
}
```
**Test:** ✓ 200

---

### GET /auth/google
Redirect ke Google OAuth. Public.

### GET /auth/google/callback
Google OAuth callback. Public.

### POST /auth/google
Mobile Google login. Throttle: 10 req/menit.

**Body:** `{ "id_token": "string" }`

---

### POST /auth/logout
Auth required.

**Response:** `200 { "message": "Logged out" }`  
**Test:** ✓ 200

---

### POST /auth/refresh
Auth required. Refresh token.

---

### GET /me
Auth required. Data user yang sedang login.

**Response:** `200 { user object }`  
**Test:** ✓ 200

---

## 2. Profile & CV

### GET /profil/{username}
Public.

**Test:** ✓ 200 (jika `profile_public = true`) | ✗ 404 (profil privat/tidak ditemukan)

---

### GET /profil/{username}/cv
Public (jika `cv_enabled = true`).

**Test:** ✓ 200 | ✗ 404

---

### GET /profil/{username}/kartu-nama
Public.

**Test:** ✗ 404 (user `administrator` tidak punya kartu nama)

---

### PUT /profile
Auth required.

**Body:** form-data atau JSON, fields user profile.

**Test:** ✓ 200

---

### GET /cv
Auth required. CV milik user yang login.

**Test:** ✓ 200

---

### PUT /cv
Auth required. Update CV.

**Test:** ✓ 200

---

## 3. Blog

### GET /blogs
Public. List semua blog.

**Query params:** `?page=1&per_page=10`

**Test:** ✓ 200

---

### GET /blogs/{slug}
Public. Detail blog by slug.

**Test:** ✓ 200

---

### GET /blogs/{blog}/comments
Public. List komentar blog.

**Test:** ✓ 200

---

### POST /blogs/upload-image
Auth: admin/fulltimer/mentor/student.

**Body:** multipart/form-data `image` file.

---

### POST /blogs
Auth: admin/fulltimer/mentor/student.

**Body:**
```json
{
  "title": "string",
  "content": "string",
  "thumbnail": "file (optional)",
  "status": "draft|published"
}
```

---

### PUT /blogs/{blog}
Auth: admin/fulltimer/mentor/student.

---

### DELETE /blogs/{blog}
Auth: admin/fulltimer/mentor/student.

---

### POST /blogs/{blog}/comments
Auth required. Throttle: 20 req/menit.

**Body:** `{ "content": "string" }`

---

### DELETE /comments/{comment}
Auth required.

---

## 4. Cabang

### GET /cabangs
Public. List semua cabang.

**Test:** ✓ 200

---

### GET /cabangs/{slug}
Public. Detail cabang.

**Test:** ✓ 200 (e.g. `/cabangs/gunungsitoli`)

---

### POST /admin/cabangs
Auth: admin only.

**Body:**
```json
{
  "nama": "string",
  "slug": "string",
  "alamat": "string",
  "kontak": "string",
  "whatsapp_link": "string",
  "kelas_min": 1,
  "kelas_max": 12
}
```

---

### PUT /admin/cabangs/{cabang}
Auth: admin only.

---

### DELETE /admin/cabangs/{cabang}
Auth: admin only.

---

## 5. Jurnal (Student)

Auth: sanctum + role `student`.

### GET /jurnal/today
Data jurnal hari ini.

---

### POST /jurnal/check
Checklist jurnal item.

**Body:** `{ "item_id": 1, "checked": true }`

---

### GET /jurnal/history
Riwayat jurnal.

---

## 6. Galeri (Student)

### GET /galeri
Auth: role `student`.

---

## 7. Laporan (Student)

### GET /laporan/my
Auth: role `student`. Summary laporan.

---

### GET /laporan/my/matrix
Auth: role `student`. Matrix laporan.

---

## 8. Kelas Master

Auth: admin atau mentor.

### GET /kelas-master
**Test:** ✓ 200

### POST /kelas-master
**Body:** `{ "nama": "string", "deskripsi": "string" }`

### PUT /kelas-master/{kelas}

### DELETE /kelas-master/{kelas}

---

## 9. Mentor Presensi

Auth: admin atau mentor.

### GET /mentor-presensi
**Test:** ✓ 200

### POST /mentor-presensi
**Body:**
```json
{
  "tanggal": "2026-01-01",
  "status": "hadir|izin|sakit|alpa",
  "keterangan": "string (optional)"
}
```

### GET /mentor-presensi/{mentorPresensi}

### PUT /mentor-presensi/{mentorPresensi}

### DELETE /mentor-presensi/{mentorPresensi}

---

## 10. Presensi Siswa

Auth: admin atau mentor.

### GET /presensi/students/search
**Query:** `?q=nama`  
**Test:** ✓ 200

### GET /presensi
**Test:** ✓ 200

### POST /presensi
**Body:**
```json
{
  "user_id": 1,
  "tanggal": "2026-01-01",
  "status": "hadir|izin|sakit|alpa",
  "kelas_master_id": 1
}
```

### GET /presensi/{presensi}

### PUT /presensi/{presensi}

### DELETE /presensi/{presensi}

---

## 11. Admin — Dashboard

### GET /admin/dashboard
Auth: admin.  
**Test:** ✓ 200

---

## 12. Admin — Users

Auth: admin.

### GET /admin/users
**Test:** ✓ 200

### POST /admin/users
**Body:** `{ "name", "email", "password", "role", "cabang_id" }`

### GET /admin/users/{user}

### PUT /admin/users/{user} (atau PATCH)

### PATCH /admin/users/{user}/role
**Body:** `{ "role": "admin|mentor|student|fulltimer" }`

### PATCH /admin/users/{user}/toggle-active

### DELETE /admin/users/{user}

---

## 13. Admin — Mata Pelajaran

Auth: admin.

### GET /admin/mata-pelajaran
**Test:** ✓ 200

### POST /admin/mata-pelajaran
**Body:** `{ "nama": "string", "kode": "string" }`

### PUT /admin/mata-pelajaran/{mataPelajaran}

### PATCH /admin/mata-pelajaran/{mataPelajaran}/toggle
Toggle aktif/nonaktif.

### DELETE /admin/mata-pelajaran/{mataPelajaran}

---

## 14. Admin — Pendaftaran

Auth: admin.

### GET /admin/pendaftaran
**Test:** ✓ 200

### GET /admin/pendaftaran/{user}

### PATCH /admin/pendaftaran/{user}/validasi
**Body:** `{ "status": "approved|rejected", "catatan": "string" }`

### POST /admin/pendaftaran/{user}/generate-update-link

---

## 15. Admin — Certificates

Auth: admin.

### GET /admin/certificates/templates
**Test:** ✓ 200

### POST /admin/certificates/templates

### GET /admin/certificates/templates/{template}/preview

### PUT /admin/certificates/templates/{template}

### DELETE /admin/certificates/templates/{template}

### GET /admin/certificates/issued
**Test:** ✓ 200

### POST /admin/certificates/issued
**Body:** `{ "template_id": 1, "user_id": 1 }`

### GET /admin/certificates/issued/{cert}/download

### DELETE /admin/certificates/issued/{cert}

---

## 16. Admin — Roles & Permissions

Auth: admin.

### GET /admin/roles
**Test:** ✓ 200

### POST /admin/roles
**Body:** `{ "name": "string" }`

### PUT /admin/roles/{role}

### POST /admin/roles/{role}/permissions
**Body:** `{ "permissions": [1, 2, 3] }`

### DELETE /admin/roles/{role}

### GET /admin/permissions
**Test:** ✓ 200

### POST /admin/permissions

### PUT /admin/permissions/{permission}

### DELETE /admin/permissions/{permission}

---

## 17. Admin — Jurnal Life Items

Auth: admin.

### GET /admin/jurnal/life-items
**Test:** ✓ 200

### POST /admin/jurnal/life-items
**Body:** `{ "nama": "string", "reset_period": "daily|weekly|never" }`

### PUT /admin/jurnal/life-items/{item}

### DELETE /admin/jurnal/life-items/{item}

### GET /admin/jurnal/students/{student}/life-items

### POST /admin/jurnal/students/{student}/life-items
Sync life items untuk student tertentu.

---

## 18. Admin — Jurnal Bible Schedules

Auth: admin.

### GET /admin/jurnal/bible-schedules
**Test:** ✓ 200

### POST /admin/jurnal/bible-schedules

### POST /admin/jurnal/bible-schedules/bulk
**Body:** `{ "schedules": [...] }`

### PUT /admin/jurnal/bible-schedules/{bibleSchedule}

### DELETE /admin/jurnal/bible-schedules/{bibleSchedule}

---

## 19. Admin — Jurnal Weekly Verses

Auth: admin.

### GET /admin/jurnal/weekly-verses
**Test:** ✓ 200

### POST /admin/jurnal/weekly-verses
**Body:** `{ "verse_ref": "Yohanes 3:16", "content": "string", "week_start": "2026-01-01" }`

### PUT /admin/jurnal/weekly-verses/{weeklyVerse}

### DELETE /admin/jurnal/weekly-verses/{weeklyVerse}

---

## 20. Admin — Jurnal Reports

Auth: admin.

### GET /admin/jurnal/reports
**Test:** ✓ 200

### GET /admin/jurnal/reports/{student}

### GET /admin/jurnal/reports/{student}/export

---

## 21. Admin — Jurnal College

Auth: admin.

### GET /admin/jurnal-college
Dashboard.  
**Test:** ✓ 200

### GET /admin/jurnal-college/laporan
**Test:** ✓ 200

### GET /admin/jurnal-college/laporan/{user}

### GET /admin/jurnal-college/laporan/{user}/export

### GET /admin/jurnal-college/bible
**Test:** ✓ 200

### PUT /admin/jurnal-college/bible/anchor

### POST /admin/jurnal-college/bible/import
**Body:** JSON file upload.

### PUT /admin/jurnal-college/bible/{item}

### GET /admin/jurnal-college/items
**Test:** ✓ 200

### POST /admin/jurnal-college/items

### PUT /admin/jurnal-college/items/{item}

### DELETE /admin/jurnal-college/items/{item}

---

## 22. Admin — Mentor Presensi (Admin Reports)

Auth: admin.

### GET /admin/mentor-presensi
**Test:** ✓ 200

### GET /admin/mentor-presensi/reports
**Test:** ✓ 200

### GET /admin/mentor-presensi/export/excel
**Test:** ✓ 200

### GET /admin/mentor-presensi/export/pdf

---

## 23. Admin — Nametags

Auth: admin.

### GET /admin/nametags
**Test:** ✓ 200

### POST /admin/nametags/generate
Generate name tag untuk student.

---

## 24. Admin — Blog & Comment Moderation

Auth: admin.

### GET /admin/blogs
**Test:** ✓ 200

### DELETE /admin/blogs/{blog}

### GET /admin/comments
**Test:** ✓ 200

### DELETE /admin/comments/{comment}

---

## 25. Sync Export

### GET /sync/export
Authenticated via header `X-Sync-Key: {secret}` (bukan Bearer token).  
Secret dikonfigurasi via `config/sync.php`.

**Test:** ✗ 401 (sync key tidak terkonfigurasi di .env lokal)

---

## Ringkasan Test Results

| Group | Total | ✓ OK | ✗ Fail |
|-------|-------|------|--------|
| Public | 4 | 4 | 0 |
| Auth (any role) | 5 | 5 | 0 |
| Admin+Mentor | 4 | 4 | 0 |
| Admin Only | 22 | 22 | 0 |
| Public Profile | 3 | 0 | 3 (profil privat/404) |
| Sync | 1 | 0 | 1 (key tidak set) |
| **Total** | **39** | **35** | **4** |

### Temuan

1. **`/profil/{username}` → 404** — User `administrator` punya `profile_public = false`. Normal, bukan bug.
2. **`/sync/export` → 401** — `SYNC_SECRET_KEY` tidak ada di `.env`. Perlu dikonfigurasi jika fitur sync antar instance diaktifkan.
3. **`POST /auth/register` → 422** — Normal, karena test kirim body kosong `{}`. Validasi berjalan benar.
4. **MySQL crash** (sudah diperbaiki) — Disk `/mnt/usb_backup` penuh 100%. Diatasi dengan menghapus `system_backup/swap.img` dan `system_backup/snap`. Sisa free space: ~3.3GB. Monitor agar tidak penuh lagi.
