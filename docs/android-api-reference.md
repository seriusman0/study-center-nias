# 📱 Study Center Nias — Android API Reference

> **Status:** Diverifikasi dari source code (`routes/api.php`, semua controller di `app/Http/Controllers/Api/`, models, migrations) dan query langsung ke database produksi. Terakhir diperbarui 30 Agustus 2026. Login test dikonfirmasi terhadap API produksi.
>
> **Base URL:** `https://studycenter.nanoprojectdevindonesia.com`
> **Stack:** Laravel 13 + Sanctum (Bearer token) + MySQL

---

## 1. Autentikasi

Semua endpoint API (`/api/*`) memakai **Sanctum Bearer Token**, BUKAN cookie/session web. Header wajib untuk endpoint terproteksi:

```
Authorization: Bearer <token>
Accept: application/json
```

### Endpoint Auth (prefix `/api/auth`)

| Method | Endpoint | Auth | Body | Response |
|---|---|---|---|---|
| POST | `/api/auth/register` | guest | `name`, `email`, `password` (min 8, huruf+angka) | `201` → `{user, token}`. Role otomatis: `guest` |
| POST | `/api/auth/login` | guest | `login` **atau** `email` (keduanya diterima sebagai identifier — bisa isi username ATAU email) + `password` | `200` → `{user, token}` · `401` salah kredensial · `403` akun `is_active=false` |
| POST | `/api/auth/google` | guest | Google ID token (mobile login flow) | `{user, token}` |
| GET | `/api/auth/google` , `/api/auth/google/callback` | guest | — | OAuth redirect flow (web, kurang relevan utk Android — pakai `/api/auth/google` POST) |
| POST | `/api/auth/logout` | **auth** | — | Menghapus token yang sedang dipakai |
| POST | `/api/auth/refresh` | **auth** | — | Hapus token lama, terbitkan token baru |
| GET | `/api/me` | **auth** | — | Profil lengkap + `role_names` (array) |

**Catatan login penting:**
- Field bisa `login` (fleksibel: username atau email) ATAU `email` — validasi `required_without` satu sama lain.
- Response `user` sudah include relasi `roles` (array object) dan `cabang` (object).
- Tidak ada endpoint forgot/reset password di `api.php` — itu hanya ada di web (`routes/auth.php`, session-based), tidak untuk Android.

### Contoh riil (sudah dites)

```bash
curl -X POST https://studycenter.nanoprojectdevindonesia.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"login":"test_student","password":"Test12345"}'
```
```json
{
  "user": {
    "id": 282, "name": "Test Student", "username": "test_student",
    "email": "test_student@studycenter.test",
    "avatar": null, "bio": null, "cabang_id": 1,
    "is_active": true, "profile_public": true, "cv_enabled": false,
    "roles": [{"id":5,"name":"student","description":"Student"}],
    "cabang": {"id":1,"nama":"Gunungsitoli","slug":"gunungsitoli", "...": "..."}
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx"
}
```

---

## 2. Role & Middleware

### Roles (tabel `roles`, dari DB — bukan asumsi)

| ID | name | Jumlah user aktif (Aug 2026) |
|---|---|---|
| — | `admin` | 3 |
| — | `fulltimer` | 5 |
| — | `mentor` | 7 |
| — | `student` | 184 |
| — | `guest` | 4 |
| — | `scholarship_teenager` | 18 |
| — | `college` | 5 |

Relasi **many-to-many** via pivot `user_roles` — satu user bisa punya >1 role secara teori, meski praktiknya 1 role/user. Ambil semua role user dari `user.roles` (array) atau `user.role_names` (dari endpoint `/api/me`).

### Permission per Role (dari DB — tabel `role_permissions`)

| Role | Permissions |
|---|---|
| `admin` | manage_users, manage_roles, manage_cabangs, manage_blogs, create_blog, view_blogs, approve_payment |
| `fulltimer` | review_journals, manage_users, manage_roles, manage_cabangs, manage_blogs, create_blog, view_blogs, create_schedule, approve_payment |
| `mentor` | create_blog, view_blogs, create_schedule |
| `student` | create_blog, view_blogs |
| `guest` | view_blogs |
| `college` | review_journals |
| `scholarship_teenager` | *(tidak ada)* |

### Middleware `role:xxx,yyy`

Dicek via `$user->hasRole($roles)` — array intersect nama role. Jika gagal: **401** (belum login, JSON) atau **403** (login tapi role tidak cocok, JSON `{"message":"Forbidden."}`) untuk request `expectsJson()` (Android harus selalu kirim header `Accept: application/json` agar dapat JSON, bukan redirect HTML).

---

## 3. Response & Error Format

**Response format TIDAK seragam di semua endpoint** — ini perbedaan penting dari asumsi umum:

- Endpoint dengan **pagination manual** (Presensi, MentorPresensi): `{"data":[...], "meta":{"current_page":.., "last_page":.., "per_page":.., "total":..}}`
- Endpoint dengan **Eloquent paginate() langsung di-return**: object Laravel default `{"current_page":1,"data":[...],"first_page_url":...,"last_page":...,"total":...}` (tanpa wrapper `data` di luar) — contoh: `MataPelajaranApiController::index()`, `PendaftaranAdminApiController::index()`
- Endpoint single resource: `{"data": {...}}` atau langsung object tanpa wrapper (cek per-endpoint di bagian 4)

**⚠️ Android harus menangani KEDUA bentuk pagination** — jangan asumsikan satu bentuk `meta` untuk semua list endpoint.

**Error validasi (422):**
```json
{"message": "The email field is required.", "errors": {"email": ["The email field is required."]}}
```

**Error umum:**
- `401` — `{"message":"Unauthenticated."}` (token invalid/expired)
- `403` — `{"message":"Forbidden."}` (role tidak sesuai)
- `404` — not found (route model binding gagal)
- `422` — validasi gagal / business rule (custom message per case, cek controller)

---

## 4. Endpoint API Lengkap (per fitur)

### 🔓 Public (tanpa auth)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/blogs` | List blog terbit |
| GET | `/api/blogs/{slug}` | Detail blog |
| GET | `/api/blogs/{blog}/comments` | Komentar (threaded) |
| GET | `/api/profil/{username}` | Profil publik user |
| GET | `/api/profil/{username}/cv` | CV publik |
| GET | `/api/profil/{username}/kartu-nama` | Kartu nama |
| GET | `/api/cabangs` | List semua cabang |
| GET | `/api/cabangs/{slug}` | Detail cabang + `blogs` (paginate 12) milik cabang itu |

### 🔐 Authenticated umum (semua role login)

| Method | Endpoint | Deskripsi |
|---|---|---|
| PUT | `/api/profile` | Update profil sendiri |
| GET / PUT | `/api/cv` | Lihat/update CV sendiri |
| POST | `/api/blogs/upload-image` | Upload gambar blog (role: admin,fulltimer,mentor,student) |
| POST | `/api/blogs` | Buat blog (role: admin,fulltimer,mentor,student) |
| PUT / DELETE | `/api/blogs/{blog}` | Update/hapus blog sendiri |
| POST | `/api/blogs/{blog}/comments` | Komentar (throttle 20/menit) |
| DELETE | `/api/comments/{comment}` | Hapus komentar (author/admin) |

### 🏠 Beranda (Home) — Integrasi UI Web ke Android

Karena tidak ada endpoint `/api/beranda` khusus, halaman Beranda (khususnya untuk `student` dan `scholarship_teenager`) di Android harus dirakit menggunakan pemanggilan beberapa endpoint berikut sesuai dengan perubahan di web UI:

1. **Profil, Role, dan Cabang:** GET `/api/me`
2. **Progress Jurnal Hari Ini:**
   - `student`: GET `/api/jurnal/today`
   - `scholarship_teenager`: GET `/api/scholarship-teenager-jurnal/today`
   - *(Tombol navigasi "Mulai Isi Jurnal Remaja SC" atau "Mulai Isi Jurnal Remaja Beasiswa" diatur berdasarkan role user).*
3. **Blog Terbaru:** GET `/api/blogs` (ambil 6 teratas, bisa difilter berdasarkan `cabang_id` user).
4. **Galeri/Foto Terbaru:** GET `/api/galeri` (menampilkan foto presensi terbaru cabang).
5. **QR Code Siswa:** Digenerate secara lokal di sisi Android dengan value string `user.id`.

---

### 📖 Jurnal — role `student`, prefix `/api/jurnal`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/jurnal/today?date=YYYY-MM-DD` | Snapshot hari itu: bible porsi (PL/PB dari day_no), verse_ref minggu ini, life_items + status checked, foto_belajar_url. `date` opsional, default hari ini |
| POST | `/jurnal/check` | Body: `item_type` (pl\|pb\|verse\|life), `item_id` (wajib jika life), `date`, `checked`, `verse_ref` (jika verse). Tanggal masa depan ditolak (422) |
| GET | `/jurnal/history?from=&to=` | Array per hari: `pl_checked`, `pb_checked`, `verse_checked`, `life_checked_ids[]` |
| POST | `/jurnal/foto` | Multipart, field `foto` (jpeg/jpg/png/webp, max 4MB), `date` opsional |
| DELETE | `/jurnal/foto` | Hapus foto entri hari itu |

**Catatan bisnis:** Response `check` sekaligus balikin `state` (snapshot terbaru) — hemat 1 request. Minggu jurnal mulai hari Selasa. `verse_week_key` mengikat entri ke minggu tsb.

**Life items role `student`** — kategori `kerohanian`, `pendidikan`, `karakter`. Semua `response_type: check`. Default items (`is_default=true`) di-assign otomatis saat `StudentProfile` dibuat:

| id | Kategori | Label | response_type | reset_period |
|---|---|---|---|---|
| 1 | kerohanian | Mengawali hari dengan berdoa | check | daily |
| 2 | kerohanian | Baca Alkitab | check | daily |
| 3 | kerohanian | Hafal Ayat | check | daily |
| 4 | pendidikan | Hadir di kelas SC | check | daily |
| 5 | pendidikan | Hadir Pembinaan hari Sabtu | check | daily |
| 6 | pendidikan | Hadir Pembinaan hari Minggu | check | daily |
| 7 | karakter | Merapikan tempat tidur | check | daily |
| 8 | karakter | Menyapa orangtua/guru/kakak | check | daily |

Admin bisa tambah/hapus life item per siswa — selalu ambil dari `/api/jurnal/today` (field `life_items[]`), jangan hardcode daftar di atas.

---

### 📖 Jurnal — role `scholarship_teenager`, prefix `/api/scholarship-teenager-jurnal`

Endpoint identik dengan `/api/jurnal` (student) tapi pakai prefix berbeda dan mendukung `item_type: verse` + `verse_check`.

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/scholarship-teenager-jurnal/today?date=YYYY-MM-DD` | Snapshot hari itu. Format response sama dengan student jurnal. Ada form-window enforcement (jam buka/tutup dari `CollegeConfig`) |
| POST | `/scholarship-teenager-jurnal/check` | `item_type`: `pl\|pb\|life\|verse\|verse_check`. Sama dengan student + form-window check |
| GET | `/scholarship-teenager-jurnal/history?from=&to=` | Array per hari: `pl_checked`, `pb_checked`, `verse_checked`, `life_checked_ids[]` |
| POST | `/scholarship-teenager-jurnal/foto` | Multipart, `foto` (jpeg/jpg/png/webp, max 4MB), `date` opsional |
| DELETE | `/scholarship-teenager-jurnal/foto` | Hapus foto hari itu |

**Form-window:** Form jurnal scholarship_teenager hanya bisa diisi dalam rentang `form_open_time`–`form_close_time` (dari `CollegeConfig`) — di luar jam itu server return **403**. Response snapshot include field `config.form_open_time`, `config.form_close_time`, `config.form_active`.

**Verse scholarship_teenager:** Punya `verse_ref` (teks ayat, per-minggu) dan `verse_checked` (centang per-hari) — identik dengan role student. Kirim `item_type: verse` + `verse_ref` untuk simpan teks ayat; kirim `item_type: verse_check` + `checked: bool` untuk toggle per-hari. `verse_checked` tidak bisa dicentang jika `verse_ref` belum diisi untuk minggu itu.

**Snapshot response fields scholarship_teenager:**
```json
{
  "date": "2026-08-30",
  "week": { "...": "..." },
  "config": { "form_open_time": "HH:MM:SS", "form_close_time": "HH:MM:SS", "form_active": true },
  "bible": { "day_no": 1, "pl_porsi": "...", "pb_porsi": "...", "pl_checked": false, "pb_checked": false },
  "verse_ref": "Yohanes 3:16",
  "verse_checked": false,
  "life_items": [{ "id": 9, "kategori": "pembacaan", "label": "Perjanjian Lama", "response_type": "check", "checked": false }],
  "foto_belajar_url": null,
  "streak": 0
}
```

**Life items role `scholarship_teenager`** — kategori `pembacaan`, `sidang`, `rohani`. **16 item identik dengan college** (diverifikasi dari DB produksi — bukan subset). Controller filter sama: `whereIn('kategori', ['pembacaan','sidang','rohani'])`.

| id | Kategori | Label | response_type | reset_period |
|---|---|---|---|---|
| 9 | pembacaan | Perjanjian Lama | check | daily |
| 10 | pembacaan | Perjanjian Baru | check | daily |
| 11 | pembacaan | Upload Pembacaan Alkitab di Group | boolean | daily |
| 13 | sidang | SPR | check | daily |
| 14 | sidang | Sidang Remaja | check | daily |
| 15 | sidang | Sidang Kelompok | check | daily |
| 16 | sidang | Sidang Doa | check | daily |
| 17 | sidang | Sidang Saudari | check | daily |
| 18 | sidang | Sidang Pemuda | check | daily |
| 19 | sidang | Sidang Spesial (Seminar / Sidang Khusus) | check | daily |
| 20 | sidang | Sharing di Sidang SPR | boolean | daily |
| 12 | rohani | Baca Buku Rohani (1 Bab / 1 Judul per Minggu) | boolean | daily |
| 21 | rohani | Buku Catatan | boolean | daily |
| 22 | rohani | Doa saat SPR | boolean | daily |
| 23 | rohani | Belajar | time_range | daily |
| 24 | rohani | Pelayanan | check | daily |

**⚠️ Item `Belajar` (id=23, `response_type: time_range`):** Scholarship_teenager controller hanya terima `item_type: life` — tidak ada `item_type: study`. Item ini dikirim sebagai toggle biasa (`checked: true/false`), bukan input jam. Android tampilkan sebagai checkbox, bukan time picker, untuk role ini.

---

### 📖 Jurnal — role `college`, prefix `/api/college-jurnal`

Mirip student jurnal tapi: (1) **tidak ada verse**, (2) ada **form-window enforcement**, (3) `response_type: time_range` pakai endpoint `item_type: study` (bukan `life`), (4) ada `study_logs` di response.

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/college-jurnal/today?date=YYYY-MM-DD` | Snapshot: `bible`, `life_items[]`, `study_logs{}`, `config`, `streak` |
| POST | `/college-jurnal/check` | `item_type`: `pl\|pb\|life\|study`. Untuk `time_range` item pakai `item_type: study` + `jam_mulai`, `jam_selesai`, `tipe` (mandiri\|kelompok) |
| GET | `/college-jurnal/history?from=&to=` | Per hari: `pl_checked`, `pb_checked`, `life_checked_ids[]`, `study_logs[]` |
| POST | `/college-jurnal/foto` | Multipart, sama dengan student |
| DELETE | `/college-jurnal/foto` | Hapus foto |
| GET | `/college/profile` | `institution_name`, `position` dari `CollegeProfile` |

**Khusus `item_type: study` (untuk item `response_type: time_range`):**
```json
{
  "item_type": "study",
  "item_id": 123,
  "date": "2026-08-30",
  "jam_mulai": "08:00",
  "jam_selesai": "10:00",
  "tipe": "mandiri"
}
```
Kirim `jam_mulai: null` + `jam_selesai: null` untuk hapus log studi hari itu.

**Life items role `college`** — kategori `pembacaan`, `sidang`, `rohani`. 16 item total (diverifikasi dari DB produksi):

| id | Kategori | Label | response_type | reset_period | Cara submit |
|---|---|---|---|---|---|
| 9 | pembacaan | Perjanjian Lama | check | daily | `item_type: life` |
| 10 | pembacaan | Perjanjian Baru | check | daily | `item_type: life` |
| 11 | pembacaan | Upload Pembacaan Alkitab di Group | boolean | daily | `item_type: life` |
| 13 | sidang | SPR | check | daily | `item_type: life` |
| 14 | sidang | Sidang Remaja | check | daily | `item_type: life` |
| 15 | sidang | Sidang Kelompok | check | daily | `item_type: life` |
| 16 | sidang | Sidang Doa | check | daily | `item_type: life` |
| 17 | sidang | Sidang Saudari | check | daily | `item_type: life` |
| 18 | sidang | Sidang Pemuda | check | daily | `item_type: life` |
| 19 | sidang | Sidang Spesial (Seminar / Sidang Khusus) | check | daily | `item_type: life` |
| 20 | sidang | Sharing di Sidang SPR | boolean | daily | `item_type: life` |
| 12 | rohani | Baca Buku Rohani (1 Bab / 1 Judul per Minggu) | boolean | daily | `item_type: life` |
| 21 | rohani | Buku Catatan | boolean | daily | `item_type: life` |
| 22 | rohani | Doa saat SPR | boolean | daily | `item_type: life` |
| 23 | rohani | Belajar | **time_range** | daily | `item_type: study` + `jam_mulai`, `jam_selesai`, `tipe` |
| 24 | rohani | Pelayanan | check | daily | `item_type: life` |

**Perbedaan response_type di Android:**
- `check` → toggle checkbox biasa → submit `item_type: life`, `checked: bool`
- `boolean` → toggle ya/tidak (switch) → submit `item_type: life`, `checked: bool`
- `time_range` (hanya college, item id=23) → input jam mulai + jam selesai → submit `item_type: study`, `item_id`, `jam_mulai`, `jam_selesai`, `tipe` (mandiri\|kelompok). Kirim `jam_mulai: null` + `jam_selesai: null` untuk hapus log.

### 🖼️ Galeri — role `student`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/galeri` | 20 foto presensi terakhir **di cabang user sendiri** (dari tabel `presensi`, bukan jurnal) |

### 📊 Laporan — role `student` & `scholarship_teenager`, prefix `/api/laporan`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/laporan/my` | Ringkasan 30 hari: `pct`, `checked`, `total`, `streak` (hari beruntun ada jurnal) |
| GET | `/laporan/my/matrix?from=&to=` | Matrix harian lengkap (default 14 hari terakhir), `headers[]` + `rows[][]` |

### 📚 Kelas Master — role `admin,mentor`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/api/kelas-master?cabang_id=&active=&q=` | List kelas (mentor default lihat kelas cabang sendiri) |
| POST | `/api/kelas-master` | Body: `nama`, `cabang_id`, `keterangan`, `is_active` |
| PUT | `/api/kelas-master/{kelas}` | Update |
| DELETE | `/api/kelas-master/{kelas}` | Gagal (422) jika sudah dipakai di `mentor_presensi` |

### 👨‍🏫 Mentor Presensi (absensi mentor sendiri) — role `admin,mentor`, prefix `/api/mentor-presensi`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/` | List (mentor lihat punya sendiri; admin bisa filter `mentor_id`, `cabang_id`, `from`, `to`) — **paginate 20, format `{data,meta}`** |
| POST | `/` | Body: `kelas_id` (harus milik cabang mentor & aktif), `tanggal` (≤ hari ini), `jam_datang`, `jam_pulang` (> jam_datang), `jumlah_murid`, `catatan` |
| GET | `/{id}` | Detail |
| PUT | `/{id}` | Update — **ditolak jika sudah >24 jam** (`canEdit()`), kecuali admin |
| DELETE | `/{id}` | Sama, dibatasi 24 jam kecuali admin |

### 📋 Presensi Siswa (kelas oleh mentor) — role `admin,mentor`, prefix `/api/presensi`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/students/search?q=&cabang_id=` | Cari siswa aktif (mentor dibatasi cabang sendiri) |
| GET | `/` | List presensi (paginate 20, `{data,meta}`), filter `cabang_id`,`from`,`to` |
| POST | `/` | Multipart. Body: `mentor_id`, `cabang_id`, `kelas_id`, `tanggal`, `jam_mulai`, `jam_selesai` (> jam_mulai), `materi` (≤5000 char), `foto` (opsional image ≤4MB), `student_ids[]` (wajib ≥1), `student_status[id]` (hadir/izin/sakit/alpha) |
| GET | `/{id}` | Detail + daftar siswa + status masing² |
| PUT | `/{id}` | Update (hanya pemilik/admin) |
| DELETE | `/{id}` | Hapus (hanya pemilik/admin) |

**⚠️ Ini BEDA dengan "presensi diri sendiri"** — field `mentor_id` di body artinya mentor **mencatat presensi kelas + siswa yang hadir**, bukan absen-masuk pribadi. Tidak ada endpoint check-in/check-out lokasi GPS di API — draft lama menyebut `absen-masuk`/`absen-pulang` dengan lat/long, itu **tidak ada di kode asli**, JANGAN diimplementasikan berdasarkan asumsi itu.

### 🏢 Admin — prefix `/api/admin`, role `admin` KECUALI disebutkan lain

| Fitur | Method + Endpoint | Deskripsi |
|---|---|---|
| Dashboard | GET `/admin/dashboard` | Statistik (lihat `DashboardController::stats`) |
| Mata Pelajaran | GET/POST `/admin/mata-pelajaran`, PUT/PATCH(toggle)/DELETE `/admin/mata-pelajaran/{id}` | CRUD; response **Eloquent paginate mentah** (tanpa wrapper `data`) untuk index; nama disimpan UPPERCASE otomatis |
| Pendaftaran | GET `/admin/pendaftaran?status=&search=&cabang_id=` | List (paginate mentah, bukan `{data,meta}`) |
| | GET `/admin/pendaftaran/{user}` | Detail |
| | PATCH `/admin/pendaftaran/{user}/validasi` | Body: `status` (diterima\|ditolak\|perbaikan), `catatan_admin`, `cabang_id` opsional |
| | POST `/admin/pendaftaran/{user}/generate-update-link` | Token 7 hari, return `url` + `expires_at` |
| Users | GET/POST `/admin/users`, GET/PUT/PATCH `/admin/users/{id}`, PATCH role/toggle-active, DELETE (soft) | CRUD full |
| Cabang | POST/PUT/DELETE `/admin/cabangs` (GET public di luar admin) | CRUD |
| Roles | GET/POST/PUT/DELETE `/admin/roles`, POST `/admin/roles/{id}/permissions` | Role bawaan (`admin,student,mentor,guest,fulltimer`) **tidak bisa dihapus** |
| Permissions | GET/POST/PUT/DELETE `/admin/permissions` | CRUD |
| Jurnal life-items | GET/POST/PUT/DELETE `/admin/jurnal/life-items`, GET/POST `/admin/jurnal/students/{id}/life-items` | Kelola checklist kebajikan + assignment per siswa |
| Bible schedules | CRUD `/admin/jurnal/bible-schedules` (+`/bulk`) | Jadwal baca Alkitab mingguan |
| Weekly verses | CRUD `/admin/jurnal/weekly-verses` | |
| Jurnal reports | GET `/admin/jurnal/reports`, `/reports/{student}`, `/reports/{student}/export` (CSV) | Laporan per siswa (mentor: dibatasi cabang sendiri jika bukan admin — lihat route web `mentor.jurnal.*`) |
| Nametags | GET `/admin/nametags`, POST `/admin/nametags/generate` | Generator kartu nama (body: `user_ids[]`, `width_cm`, `height_cm`) |
| Mentor presensi (laporan admin) | GET `/admin/mentor-presensi`, `/reports`, `/export/excel`, `/export/pdf` | |
| Blogs moderasi | GET `/admin/blogs`, DELETE `/admin/blogs/{id}` | |
| Comments moderasi | GET `/admin/comments` (closure route, tanpa auth check tambahan selain grup admin), DELETE `/admin/comments/{id}` | |

### 🏆 Sertifikat — prefix `/api/admin/certificates`, role `admin`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `templates` | List (termasuk soft-deleted, `withTrashed`), paginate 20 |
| POST | `templates` | Body: `nama`,`deskripsi`,`html_content`,`orientation`(portrait\|landscape),`paper_size`(a4),`is_active`,`logo` (image opsional) |
| GET | `templates/{id}/preview` | Stream PDF preview |
| PUT/DELETE | `templates/{id}` | Delete gagal (422) jika sudah dipakai issued certificate |
| GET | `issued` | List sertifikat terbit |
| POST | `issued` | Terbitkan ke user |
| GET | `issued/{id}/download` | Download PDF |
| DELETE | `issued/{id}` | Hapus |

### ⛪ College Jurnal Admin — prefix `/api/admin/jurnal-college`, role `admin`

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/` | Dashboard: config, day_no hari ini, bible hari ini, users (paginate 20), `activeToday`, `totalUsers`, `checkCounts` (7 hari), `lastEntryDates`, `campuses` |
| GET | `/laporan?campus=&q=` | List user college |
| GET | `/laporan/{user}?from=&to=` | Matrix jurnal user (default 14 hari) |
| GET | `/laporan/{user}/export` | CSV (default 30 hari) |
| GET | `/bible` | Semua item + config |
| PUT | `/bible/anchor` | Body: `anchor_day_no`(1-366), `anchor_date`, `form_open_time`, `form_close_time` |
| POST | `/bible/import` | Upload JSON `[{no, PL, PB}, ...]` |
| PUT | `/bible/{item}` | Update satu hari (`pl_text`,`pb_text`) |
| GET/POST/PUT/DELETE | `/items` | Kelola kategori khusus college: `pembacaan`,`sidang`,`rohani`; response_type: `check`,`boolean`,`time_range` |

**Endpoint untuk role `college` sendiri (bukan admin)** ada di **web routes** (`/college/dashboard`, `/jurnal-college/*`) — **belum ada versi `/api/` khusus role college untuk self-service** (hanya admin API di atas). Jika Android butuh akses role college, kemungkinan perlu tambahan endpoint API baru atau pakai endpoint admin dengan pembatasan tambahan di sisi app (perlu didiskusikan dengan backend dev).

---

## 5. Model Data Kunci (field asli dari kode, bukan mockup)

### User
```
id, google_id, name, username(unik), email(unik), avatar, bio, cabang_id,
is_active, profile_public, cv_enabled, email_verified_at,
password(hidden), remember_token(hidden), deleted_at(soft delete)
```
Relasi: `roles` (many-to-many), `cabang`, `blogs`, `comments`, `socialLinks`, `cvData`,
`studentProfile`, `mentorProfile`, `adminProfile`, `collegeProfile`, `scholarshipJournals`,
`jurnalEntries`, `jurnalLifeChecks`.

### StudentProfile
```
user_id(unique), student_number, birth_date, birth_place, gender, address,
guardian_name, guardian_phone, student_phone, school_name, grade_class, entry_year,
campus_name, current_semester, photo, note,
is_pending(bool, default true), status(pending|diterima|ditolak|perbaikan, default pending),
catatan_admin, update_token, update_token_expires_at, mata_pelajaran(json array)
```
⚠️ Saat `StudentProfile` dibuat, **otomatis** assign semua `JurnalLifeItem` default (`is_default=true, is_active=true`) ke siswa itu — jangan duplikasi logic ini di Android.

### Cabang
```
id, nama, slug(unik), alamat, kontak, foto_wajib(bool), pendaftaran_buka(bool),
whatsapp_link, kelas_min, kelas_max, mata_pelajaran(json array), bible_schedule_id
```
Data real saat ini (4 cabang):
| id | nama | slug |
|---|---|---|
| 1 | Gunungsitoli | gunungsitoli |
| 2 | Kabupaten Nias | kabupaten-nias |
| 3 | Kabupaten Nias Selatan | nias-selatan |
| 4 | Kabupaten Nias Utara | nias-utara |

### JurnalEntry
`student_id, tanggal(date, unik per hari per siswa), pl_checked, pb_checked, verse_week_key, verse_ref, foto_belajar`

### JurnalLifeItem
`kategori (kerohanian/pendidikan/karakter untuk student; pembacaan/sidang/rohani untuk college), label, response_type (check/boolean/time_range), reset_period (daily/weekly_saturday/weekly_sunday), is_default, is_active, student_id (null = global default, terisi = kustom per siswa), created_by`

### CollegeProfile
`user_id, institution_name, position`

---

## 6. Aturan Bisnis Penting untuk Android

1. **Login identifier fleksibel** — form login Android sebaiknya 1 field (bukan pisah username/email), kirim sebagai `login`.
2. **Token tidak auto-expire pendek** (Sanctum default tanpa expiry eksplisit terlihat di kode) — tapi selalu sediakan `logout` untuk revoke, dan gunakan `/api/auth/refresh` sebelum token lama dihapus.
3. **Jurnal hanya bisa diisi untuk tanggal ≤ hari ini** — validasi klien untuk UX, tapi server juga menolak (422).
4. **Minggu jurnal dimulai Selasa** (`JurnalWeek` helper) — penting untuk kalkulasi UI kalender/minggu.
5. **Mentor Presensi hanya bisa diedit/dihapus dalam 24 jam** — setelah itu terkunci (kecuali admin).
6. **Kelas Master unik per cabang** (bukan global) — nama kelas boleh sama di cabang berbeda.
7. **Role `guest`** dari `/api/auth/register` **tidak sama** dengan alur pendaftaran siswa (`/pendaftaran/{cabang}` web, multi-step, hasil role `student` + status `pending`). Registrasi via `/api/auth/register` cuma untuk akun blog-baca dasar (role guest), **BUKAN untuk pendaftaran siswa baru** — itu belum ada endpoint API-nya (hanya web multi-step form). Perlu didiskusikan jika Android harus support pendaftaran siswa penuh.
8. **`mata_pelajaran`** di Cabang & StudentProfile disimpan sebagai **JSON array string**, bukan relasi tabel — cocokkan tipe data di model Android.
9. **File upload pakai `multipart/form-data`** standar Laravel — foto max 4MB untuk jurnal/presensi, image untuk certificate logo max 2MB.
10. **CORS**: perlu dikonfirmasi ke backend dev nilai aktual (belum dicek header CORS langsung) — jangan asumsikan `*` tanpa verifikasi jika app pakai WebView.

---

## 7. Kredensial Test (dibuat khusus untuk testing, TIDAK dipakai user produksi)

> ⚠️ **PENTING:** Ini adalah 7 akun BARU yang saya buat khusus untuk keperluan testing Android — bukan akun siswa/staff asli. Aman dipakai bebas untuk development tanpa risiko ke data produksi. Password sama untuk semua, ganti setelah development selesai jika perlu, atau minta saya hapus akun ini nanti.

| Role | Username | Email | Password |
|---|---|---|---|
| admin | `test_admin` | test_admin@studycenter.test | `Test12345` |
| fulltimer | `test_fulltimer` | test_fulltimer@studycenter.test | `Test12345` |
| mentor | `test_mentor` | test_mentor@studycenter.test | `Test12345` |
| student | `test_student` | test_student@studycenter.test | `Test12345` |
| guest | `test_guest` | test_guest@studycenter.test | `Test12345` |
| scholarship_teenager | `test_scholarship_teenager` | test_scholarship_teenager@studycenter.test | `Test12345` |
| college | `test_college` | test_college@studycenter.test | `Test12345` |
| (general testing) | `testuser` | testuser@studycenter.test | `12345` |

Semua akun: `cabang_id = 1` (Gunungsitoli), `is_active = true`. Login sudah **diverifikasi nyata** via `test_student` (lihat contoh curl di bagian 1) — berhasil dapat token.

Login endpoint: `POST /api/auth/login` dengan body `{"login": "<username>", "password": "Test12345"}` (Gunakan password `12345` khusus untuk `testuser`).

---

## 8. Yang Perlu Diklarifikasi ke Backend Dev Sebelum Mulai Coding

- [ ] Belum ada endpoint API self-service untuk role `college` (hanya via web session) — perlu dibuatkan versi `/api/` jika Android harus support role ini.
- [ ] Belum ada endpoint API untuk alur pendaftaran siswa baru (`/api/pendaftaran/*`) — saat ini hanya web multi-step form.
- [ ] CORS header belum diverifikasi langsung dari response — cek dengan `curl -I` + `Origin` header sebelum asumsi WebView/JS fetch akan berfungsi (Android native HTTP client tidak terpengaruh CORS, tapi baik untuk didokumentasikan).
- [ ] Tidak ada endpoint forgot/reset password via API — perlu ditambahkan jika Android butuh fitur ini (saat ini hanya web session-based).
- [x] ~~Scholarship Journal API belum ditelusuri~~ — API `/api/scholarship-teenager-jurnal/*` sudah ada dan terdokumentasi di bagian 4. Controller: `ScholarshipTeenagerJurnalApiController`. Item life identik dengan college (16 item), tapi `item_type: life` untuk semua (termasuk item `time_range`).
- [ ] Item `Belajar` (id=23, `time_range`) untuk scholarship_teenager: controller hanya terima `item_type: life`, bukan `study` — tidak ada dukungan input jam mulai/selesai. Perlu diputuskan apakah tampilkan sebagai checkbox atau minta backend tambahkan `study` support.

---

*Dokumen ini dibuat dan diperbarui dengan membaca langsung `routes/api.php`, `routes/web.php`, seluruh controller di `app/Http/Controllers/Api/` (termasuk subfolder Admin, College, ScholarshipTeenager), model-model kunci, migrations, dan query langsung ke database produksi (roles, permissions, cabang, jurnal_life_items, jurnal_student_life_items). Terakhir diverifikasi 30 Agustus 2026. Draft dokumen lama (`android-preparation-plan.md`) di server berisi banyak endpoint tidak sesuai kode asli — dokumen ini menggantikannya.*

**Koreksi dari versi sebelumnya (30 Agustus 2026):**
- Student life items: 8 item (bukan 6) — tambah `Baca Alkitab` (id=2) dan `Hafal Ayat` (id=3)
- Semua `reset_period` adalah `daily` — tidak ada `weekly_saturday`/`weekly_sunday` di DB
- Scholarship_teenager life items: 16 item **identik** dengan college — bukan 13 item seperti yang didokumentasikan sebelumnya (`Sidang Saudari`, `Sidang Pemuda`, `Baca Buku Rohani` termasuk)
- Ditambahkan kolom `id` DB nyata di semua tabel life items
- Ditambahkan snapshot response format untuk scholarship_teenager
