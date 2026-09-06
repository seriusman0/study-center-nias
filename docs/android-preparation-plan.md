# 📱 Android Preparation Plan — Study Center Nias

> **Tujuan:** Menyediakan dokumentasi lengkap untuk implementasi frontend Android aplikasi Study Center Nias berbasis Laravel backend. Dokumen ini mencakup uraian fitur, aturan bisnis, skema mockup data, dan rencana endpoint API.
>
> **Catatan:** Dokumen ini bersifat **read-only reference** — tidak mengubah kode sumber, database, atau konfigurasi project.

---

## 🗂️ Daftar Isi

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Role & Hak Akses](#2-role--hak-akses)
3. [Fitur & Aturan (Rule Set)](#3-fitur--aturan-rule-set)
4. [Skema Data & Mockup Data](#4-skema-data--mockup-data)
5. [Rencana Endpoint API](#5-rencana-endpoint-api)
6. [UI Kit Android Reference](#6-ui-kit-android-reference)
7. [Pertimbangan Teknis Android](#7-pertimbangan-teknis-android)

---

## 1. Gambaran Umum Sistem

| Komponen | Teknologi |
|---|---|
| Backend Framework | Laravel 13 (PHP) |
| Auth Driver | Sanctum (Bearer Token) |
| Database | MySQL / MariaDB (SQLite untuk testing) |
| CORS | `*` (buka semua origin) |
| Frontend Web | Laravel Blade + React/Vite build |
| UI Kit Ref | `_template/ui_kits/android/` |

**Autentikasi:** Aplikasi menggunakan Sanctum-style Bearer token. Header otorisasi: `Authorization: Bearer <token>`. Session/cookie auth untuk web, tapi untuk Android sebaiknya gunakan token-based auth via `api` middleware group.

**API Response Format (default):**
```json
{
  "data": { ... },
  "meta": { "pagination": { ... } }  // untuk paginate responses
}
```

**Error Format:**
```json
{
  "message": "[error message]",
  "errors": { "field": ["Error detail"] }
}
```

---

## 2. Role & Hak Akses

### Daftar Role (tabel `roles`)

| ID | Name | Deskripsi |
|---|---|---|
| 1 | `admin` | Administrator penuh — kelola semua |
| 2 | `fulltimer` | Staff tetap penuh waktu |
| 3 | `mentor` | Mentor / guru kelas |
| 4 | `student` | Siswa / murid |
| 5 | `guest` | Tamu publik (read-only blog) |
| 6 | `scholarship_teenager` | Remaja beasiswa |
| 7 | `college` | Koordinator perguruan tinggi / pengelola beasiswa (dinambah migration) |

> **Catatan:** Role di-assign fleksibel via `user_roles` pivot table (many-to-many). Satu user bisa punya multiple role. Jika tidak punya role eksternal, default-nya punya role `student`.

### Permission Mapping

| Permission | Role yang punya |
|---|---|
| `manage_users` | admin |
| `manage_roles` | admin |
| `manage_cabangs` | admin |
| `manage_blogs` | admin |
| `create_blog` | admin, fulltimer, mentor, student |
| `view_blogs` | admin, fulltimer, mentor, student, guest |
| `create_schedule` | mentor |
| `approve_payment` | admin |
| `review_journals` | college |

### Middleware CheckRole

Middleware `app/Http/Middleware/CheckRole.php` memastikan user memiliki role yang diperlukan. Untuk Android API, role cek dilakukan via `$user->hasRole('role_name')`.

---

## 3. Fitur & Aturan (Rule Set)

Berikut adalah uraian tiap fitur, aturan bisnis, dan dependency-nya.

### 🔐 3.1 Sistem Autentikasi

**Endpoint utama:** `routes/auth.php` (disediakan oleh Laravel Breeze/Jetstream-style)

| Endpoint | Method | Role | Aturan |
|---|---|---|---|
| `/login` | POST | guest, all | Validasi: email/username + password. Token dibuat via Sanctum. Password: `password` (default seeder). |
| `/register` | POST | guest | Hanya untuk pendaftaran siswa baru (dengan kode cabang bila perlu). |
| `/forgot-password` | POST | guest | Generate token reset via email. |
| `/reset-password` | POST | guest | Validasi token + password min 5 karakter. |
| `/user` | GET | authenticated | Mengembalikan profil user + role. |
| `/logout` | POST | authenticated | Mencabut token Sanctum. |

**Aturan khusus:**
- User bisa login dengan `email` atau `username`
- `is_active` harus `true` supaya bisa login
- `password` minimal 5 karakter
- User yang baru terdaftar otomatis mendapat role `student`
- Soft delete pada user — `deleted_at` disimpan, user tidak bisa login setelah dihapus

### 📖 3.2 Sistem Jurnal

Ini adalah fitur inti aplikasi. Ini sebuah sistem pelacatan kegiatan harian siswa dengan checklist kebajikan, entri jurnal harian, dan target ayat Alkitab mingguan.

#### Komponen Jurnal:

**A. JurnalWeeklyVerse (Ayat Alkitab Mingguan)**
- Setiap minggu memiliki target ayat Alkitab yang harus dihafal
- Verse key dihitung berdasarkan tanggal → minggu ke-N
- `JurnalWeek` helper class:
  - `JurnalWeek::today()` → Carbon instance dalam timezone lokal
  - `JurnalWeek::weekNumber()` → minggu ke-N dari 17 Juni 2025
  - `JurnalWeek::weekStart()` / `weekEnd()` → rentang tanggal minggu
  - `JurnalWeek::currentAyat()` → ayat Alkitab untuk minggu ini
  - `JurnalWeek::all()` → semua data jadwal pembacaan (dari `jadwal_pembacaan_alkitab.json`)

**B. JurnalEntry (Entri Harian)**
- User mengisi entri sekali per hari
- Field kunci: `tanggal`, `verse_ref` (ayat yang dibaca), `pl_checked`, `pb_checked` (checklist PL/PB)
- `verse_week_key` → mengikat entri ke minggu jurnal tersebut
- `foto_belajar` → opsional, foto dokumentasi belajar
- User hanya bisa mengedit entri dengan `tanggal` = hari ini

**C. JurnalLifeItem + JurnalLifeCheck (Checklist Kebajikan)**
- Life items adalah daftar checklist kebajikan yang harus dicek setiap hari/minggu
- Default life items (dari seeder):
  | Kategori | Label | Reset Period |
  |---|---|---|
  | kerohanian | Mengawali hari dengan berdoa | daily |
  | pendidikan | Hadir di kelas SC | daily |
  | pendidikan | Hadir pembinaan hari Sabtu | weekly_saturday |
  | pendidikan | Hadir pembinaan hari Minggu | weekly_sunday |
  | karakter | Merapikan tempat tidur | daily |
  | karakter | Menyapa orangtua/guru/kakak | daily |

- `response_type`: `check` (checkbox), `boolean`, `time_range`
- `reset_period`: `daily`, `weekly_saturday`, `weekly_sunday`
  - **Aturan reset:**
    - `daily` → reset setiap hari (checkbox hanya berlaku untuk tanggal tertentu)
    - `weekly_saturday` → reset setiap Sabtu (checklist berlaku Selasa–Sabtu)
    - `weekly_sunday` → reset setiap Minggu (checklist berlaku Minggu–Sabtu)

**D. College Items (untuk role `college`)**
- Life items dengan kategori: `pembacaan`, `sidang`, `rohani`
- Response types: `check`, `boolean`, `time_range`
- Hanya terlihat oleh user dengan role `college`
- Di-manage oleh admin melalui API khusus

#### Aturan Bisnis Jurnal:

1. **Entri harian hanya boleh ada 1 per hari per user** — constraint unik pada `tanggal + user_id`
2. **Hari pertama minggu jurnal adalah Selasa** (berdasarkan `JurnalWeek`)
3. **User harus mengecek ayat mingguan** — `pb_checked` dan `pl_checked` bisa di-set true bila sudah dibaca
4. **Foto belajar opsional** — bisa upload 1 foto per entri
5. **Life item response type:**
   - `check` → boolean true/false
   - `boolean` → ya/tidak (enum)
   - `time_range` → input rentang waktu (start_time, end_time)
6. **Reset period menentukan kapan checklist "reset" / berulang**
7. **College users** dapat melihat dashboard khusus dengan matrix jurnal 14 hari

### 📋 3.3 Presensi

#### A. Presensi Umum (`presensi` table)

| Field | Type | Aturan |
|---|---|---|
| `user_id` | FK→users | Wajib |
| `tanggal` | date | Wajib, unik per user+date |
| `jam_masuk` | time | Auto-set saat absen masuk |
| `jam_pulang` | time | Auto-set saat absen pulang |
| `latitude` | decimal | Opsional, untuk validasi lokasi |
| `longitude` | decimal | Opsional |
| `status` | enum | `hadir`, `sakit`, `izin`, `alfa` |
| `kelas_id` | FK→kelas_master | Opsional, filter kelas |

**Aturan:**
- User hanya bisa absen masuk sekali per hari
- Status default: `hadir`
- Absen pulang hanya bisa dilakukan setelah absen masuk pada hari yang sama
- `latitude`/`longitude` bisa digunakan untuk validasi lokasi (opsional)

#### B. Mentor Presensi (`mentor_presensi` table)

| Field | Type | Aturan |
|---|---|---|
| `mentor_id` | FK→users | Wajib |
| `cabang_id` | FK→cabangs | Opsional |
| `kelas_id` | FK→kelas_master | Wajib |
| `tanggal` | date | Wajib |
| `jam_datang` | time | Wajib |
| `jam_pulang` | time | Wajib |
| `jumlah_murid` | unsignedSmallInteger | Default 0 |
| `catatan` | text | Opsional |

**Aturan:**
- Hanya role `mentor` yang bisa mengisi
- Index pada `mentor_id + tanggal`, `cabang_id + tanggal`, `kelas_id + tanggal`
- Digunakan untuk laporan kehadiran mentor di kelas

### 📚 3.4 Kelas Master (Kelas/Kursus)

| Field | Type | Aturan |
|---|---|---|
| `nama` | string(100) | Wajib, unik per cabang |
| `cabang_id` | FK→cabangs | Wajib |
| `keterangan` | string(255) | Opsional |
| `is_active` | boolean | Default true |
| Soft deletes | — | Ya |

**Aturan:**
- Setiap cabang bisa punya banyak kelas
- Nama kelas unik per cabang
- Relasi ke student via... (akan dicek di migration kelas_student pivot)

### 🏢 3.5 Cabang (Branch)

| Field | Type | Aturan |
|---|---|---|
| `nama` | string | Wajib |
| `slug` | string | Wajib, unik |
| `alamat` | text | Opsional |
| `kontak` | string | Opsional |
| `foto_wajib` | boolean | Default true (migration 2026_07_12) |
| `pendaftaran_buka` | boolean | Default true (migration 2026_07_12) |
| `whatsapp_link` | url | Opsional (migration 2026_07_12) |
| `kelas_min` | unsignedTinyInteger | Opsional (migration 2026_07_13) |
| `kelas_max` | unsignedTinyInteger | Opsional, gte kelas_min |
| `mata_pelajaran` | json | Opsional — array nama mata pelajaran (migration 2026_07_13) |
| `bible_schedule_id` | FK→college_bible_schedules | Opsional, hanya untuk cabang college (migration 2026_07_30) |

**Aturan:**
- Seeder default: Gunungsitoli, Kabupaten Nias, Nias Selatan, Nias Utara
- `pendaftaran_buka` mengontrol apakah pendaftaran siswa terbuka
- `foto_wajib` mengontrol apakah user wajib upload foto profil
- `whatsapp_link` untuk tombol join WhatsApp grup cabang

### 🎓 3.6 Pendaftaran Siswa (Registration)

Di-handle oleh `RegisterController` + `StudentProfile`. Alur:
1. User mengisi form pendaftaran → membuat `User` + `StudentProfile`
2. Status default: `pending`
3. Admin memvalidasi → status berubah ke `diterima` atau `ditolak`
4. Untuk perbaikan → status `perbaikan`, `is_pending` tetap `true`
5. Admin bisa generate link update khusus (token-based, 7 hari expiry)

**StudentProfile Fields:**
| Field | Type | Aturan |
|---|---|---|
| `user_id` | FK | Unique |
| `nim` | string | Opsional |
| `nisn` | string | Opsional |
| `tempat_lahir` | string | Opsional |
| `tanggal_lahir` | date | Opsional |
| `alamat` | text | Opsional |
| `guardian_name` | string | Opsional |
| `guardian_phone` | string | Opsional |
| `student_phone` | string(20) | Ditambah migration 2026_07_06 |
| `photo` | string | Ditambah migration 2026_07_06 |
| `note` | text | Ditambah migration 2026_07_06 |
| `is_pending` | boolean | Default true |
| `status` | enum(20) | Default `pending` (migration 2026_07_06) |
| `catatan_admin` | text | Ditambah migration 2026_07_06 |
| `campus_name` | string | Ditambah migration 2026_07_06 (untuk college) |
| `current_semester` | tinyInteger | Ditambah migration 2026_07_06 |
| `address` | text | — |
| `update_token` | string(64) | Ditambah migration 2026_07_12 |
| `update_token_expires_at` | timestamp | Ditambah migration 2026_07_12 |
| `mata_pelajaran` | json | Ditambah migration 2026_07_13 |

### 📝 3.7 Blog System

**Blog Model:**
| Field | Type | Aturan |
|---|---|---|
| `user_id` | FK→users | Author |
| `cabang_id` | FK→cabangs | Cabang tempat blog diposting |
| `title` | string | Wajib |
| `slug` | string | Unik, auto-generated |
| `content` | longText | Wajib — HTML content |
| `image` | string | Opsional — cover image |
| `published_at` | timestamp | Opsional — null = draft |
| Soft deletes | — | Ya |

**Comment Model:**
| Field | Type | Aturan |
|---|---|---|
| `blog_id` | FK→blogs | Wajib |
| `user_id` | FK→users | Wajib |
| `parent_id` | FK→comments | Opsional — untuk nested reply |
| `content` | text | Wajib, max 2000 karakter |
| Soft deletes | — | Ya |

**Tag Model:**
| Field | Type | Aturan |
|---|---|---|
| `name` | string | Unik |
| `slug` | string | Unik |

**Aturan Blog:**
- Semua role bisa membuat blog (bergantung permission `create_blog`)
- Guest hanya bisa melihat blog yang dipublikasikan
- Blog memiliki soft delete — tidak benar-benar dihapus
- Komentar mendukung threaded reply (parent_id)
- Hanya author atau admin yang bisa menghapus komentar

### 👨‍💼 3.8 CV System

**CvData Model:**
| Field | Type | Aturan |
|---|---|---|
| `user_id` | FK | Unique — satu CV per user |
| `pendidikan` | json | Array data pendidikan |
| `pengalaman` | json | Array data pengalaman kerja |
| `keterampilan` | json | Array keterampilan |
| `portofolio` | text | Opsional |
| `template` | string | Default `template1` |

### 👤 3.9 Profil Pengguna

User profil mencakup:
- Data dasar: `name`, `username` (unik), `email` (unik), `avatar`, `bio`
- Social links: `UserSocialLink` — platform (instagram, whatsapp, email, facebook) + value
- Role assignment
- Cabang association
- `is_active`, `profile_public`, `cv_enabled` flags

**Aturan:**
- `username` hanya boleh mengandung hurv lowercase + angka
- User bisa update profil sendiri
- `profile_public` mengontrol visibilitas profil di publik

### ⛪ 3.10 College Bible System

**CollegeConfig:**
| Field | Type | Deskripsi |
|---|---|---|
| `anchor_day_no` | integer | Day number (1-366) yang menjadi acuan |
| `anchor_date` | date | Tanggal acuan untuk menghitung day_no |
| `form_open_time` | time | Jam buka form jurnal |
| `form_close_time` | time | Jam tutup form jurnal |
| `active_schedule_id` | FK→college_bible_schedules | Jadwal aktif |

**Aturan College Bible:**
- Day number dihitung dari `anchor_date` + offset `anchor_day_no`
- Form jurnal hanya bisa diisi antara `form_open_time` dan `form_close_time`
- College users dapat melihat ayat harian berdasarkan day_no

### 🎓 3.11 Scholarship System

**ScholarshipJournal:**
| Field | Type | Aturan |
|---|---|---|
| `user_id` | FK→users | Student yang bersangkutan |
| `title` | string | Judul jurnal |
| `period_month` | tinyInteger | Bulan pelaporan |
| `period_year` | smallInteger | Tahun pelaporan |
| `status` | enum | `draft`, `submitted`, `under_review`, `approved`, `revision_required` |
| `submitted_at` | timestamp | Kapan disubmit |
| `reviewed_by` | FK→users | Admin yang review |
| `reviewed_at` | Timestamp | Kapan direview |
| `reviewer_notes` | text | Catatan reviewer |

**ScholarshipJournalItem (one-to-one):**
| Field | Type | Deskripsi |
|---|---|---|
| `journal_id` | FK | Unique — satu item per journal |
| `gpa_current_semester` | decimal(3,2) | IP semester ini |
| `cumulative_gpa` | decimal(3,2) | IP kumulatif |
| `academic_summary` | text | Ringkasan akademik |
| `class_attendance_percentage` | tinyInteger | Persentase kehadiran |
| `organization_activities` | text | Aktivitas organisasi |
| `training_seminars` | text | Training & seminar |
| `achievements` | text | Pencapaian |
| `community_service_details` | text | Detail pelayanan |
| `service_hours` | unsignedSmallInteger | Jam pelayanan |
| `personal_reflection` | text | Refleksi pribadi |
| `next_month_goals` | text | Target bulan depan |

**ScholarshipJournalAttachment:**
| Field | Type | Aturan |
|---|---|---|
| `journal_id` | FK→scholarship_journals | Wajib |
| `file_name` | string | Nama file |
| `file_path` | string | Path di storage |
| `file_type` | enum | `transkrip_khs`, `sertifikat`, `foto_kegiatan`, `lainnya` |

**ScholarshipTeenager (Remaja Beasiswa):**
- Role khusus untuk remaja penerima beasiswa
- Memiliki alur pendaftaran serupa student

### 🏆 3.12 Sertifikat (Certificate)

**CertificateTemplate:**
| Field | Type | Aturan |
|---|---|---|
| `nama` | string(150) | Wajib |
| `deskripsi` | text | Opsional |
| `html_content` | longText | Wajib — template HTML |
| `orientation` | enum | `portrait`, `landscape` (default portrait) |
| `paper_size` | string(10) | Default `a4` |
| `is_active` | boolean | Default true |
| `created_by` | FK→users | Admin pembuat |
| `logo_path` | string | Ditambah migration |
| Soft deletes | — | Ya |

**IssuedCertificate:**
| Field | Type | Aturan |
|---|---|---|
| `nomor_sertifikat` | string(80) | Unik |
| `user_id` | FK→users | Siswa penerima |
| `template_id` | FK→certificate_templates | Template yang dipakai |
| `issued_by` | FK→users | Admin yang mengeluarkan |
| `tanggal_lulus` | date | Wajib |
| `nama_kursus` | string(150) | Nama kursus |
| `file_path` | string(500) | Path PDF hasil generate |
| `issued_at` | timestamp | Wajib |

### 📢 3.13 Announcement System

**Announcement:**
| Field | Type | Aturan |
|---|---|---|
| `title` | string | Wajib |
| `content` | text | Wajib |
| `type` | enum | `info` (default), `success`, `warning`, `danger` |
| `is_active` | boolean | Default true |
| `starts_at` | timestamp | Opsional — kapan mulai ditampilkan |
| `ends_at` | timestamp | Opsional — kapan berakhir |
| `created_by` | FK→users | Admin pembuat |
| Index | — | `is_active + starts_at + ends_at` |

**Aturan Announcement:**
- Hanya admin yang bisa membuat/announcement
- Umum (semua user role)
- Bisa dijadwalkan (starts_at / ends_at)
- Type memengaruhi tampilan visual (warna badge)

### 🪪 3.14 Nametag Generator

**NameTagTemplate:**
| Field | Type | Aturan |
|---|---|---|
| `nama` | string | Wajib |
| `html_content` | longText | Template HTML |
| `width_cm` | decimal | Lebar kartu |
| `height_cm` | decimal | Tinggi kartu |
| `is_active` | boolean | Default true |
| `is_default` | boolean | Default false |

**Aturan Nametag:**
- Admin bisa pilih template nama kartu
- Sistem generate kartu nama berdasarkan template + data siswa
- Output berupa HTML yang bisa di-render ke PDF

---

## 4. Skema Data & Mockup Data

### Tabel Users (mockup)

```json
{
  "id": 1,
  "name": "Administrator",
  "username": "administrator",
  "email": "admin@studycenter.com",
  "avatar": null,
  "bio": "System administrator for Study Center Nias",
  "role_id": 1,
  "cabang_id": 1,
  "is_active": true,
  "profile_public": false,
  "cv_enabled": false,
  "created_at": "2025-06-17T08:00:00Z",
  "updated_at": "2025-06-17T08:00:00Z"
}
```

### Tabel Roles (mockup)

```json
[
  {"id": 1, "name": "admin", "description": "Administrator"},
  {"id": 2, "name": "fulltimer", "description": "Full Timer"},
  {"id": 3, "name": "mentor", "description": "Mentor"},
  {"id": 4, "name": "student", "description": "Student"},
  {"id": 5, "name": "guest", "description": "Tamu Publik"},
  {"id": 6, "name": "scholarship_teenager", "description": "Remaja Beasiswa"},
  {"id": 7, "name": "college", "description": "Koordinator Perguruan Tinggi"}
]
```

### Tabel Cabangs (mockup)

```json
[
  {"id": 1, "nama": "Gunungsitoli", "slug": "gunungsitoli", "alamat": "Kota Gunungsitoli, Nias", "kontak": "0812-3456-7890", "foto_wajib": true, "pendaftaran_buka": true, "whatsapp_link": "https://chat.whatsapp.com/example", "kelas_min": 1, "kelas_max": 6, "mata_pelajaran": ["MATEMATIKA", "BAHASA INGGRIS", "BAHASA MANDARIN", "KOMPUTER"]},
  {"id": 2, "nama": "Kabupaten Nias", "slug": "kabupaten-nias", "alamat": "Kabupaten Nias", "kontak": null, "foto_wajib": true, "pendaftaran_buka": true, "whatsapp_link": null, "kelas_min": null, "kelas_max": null, "mata_pelajaran": ["MATEMATIKA", "BAHASA INGGRIS", "BAHASA MANDARIN", "KOMPUTER"]}
]
```

### Tabel JurnalWeeklyVerse (mockup) — from `jadwal_pembacaan_alkitab.json`

```json
{
  "week_number": 1,
  "start_date": "2025-06-17",
  "end_date": "2025-06-23",
  "pl_verse": "Yohanes 3:16-17",
  "pb_verse": "Roma 8:28"
}
```

### Tabel JurnalEntries (mockup)

```json
{
  "id": 1,
  "user_id": 4,
  "tanggal": "2025-06-17",
  "verse_ref": "Yohanes 3:16",
  "verse_week_key": "week_1_2025",
  "pl_checked": true,
  "pb_checked": false,
  "foto_belajar": "/storage/jurnal/foto/abc123.jpg",
  "created_at": "2025-06-17T08:30:00Z",
  "updated_at": "2025-06-17T08:30:00Z"
}
```

### Tabel JurnalLifeItems (mockup) — default items

```json
[
  {"id": 1, "kategori": "kerohanian", "label": "Mengawali hari dengan berdoa", "response_type": "check", "reset_period": "daily", "is_default": true, "is_active": true},
  {"id": 2, "kategori": "pendidikan", "label": "Hadir di kelas SC", "response_type": "check", "reset_period": "daily", "is_default": true, "is_active": true},
  {"id": 3, "kategori": "pendidikan", "label": "Hadir pembinaan hari Sabtu", "response_type": "check", "reset_period": "weekly_saturday", "is_default": true, "is_active": true},
  {"id": 4, "kategori": "pendidikan", "label": "Hadir pembinaan hari Minggu", "response_type": "check", "reset_period": "weekly_sunday", "is_default": true, "is_active": true},
  {"id": 5, "kategori": "karakter", "label": "Merapikan tempat tidur", "response_type": "check", "reset_period": "daily", "is_default": true, "is_active": true},
  {"id": 6, "kategori": "karakter", "label": "Menyapa orangtua/guru/kakak", "response_type": "check", "reset_period": "daily", "is_default": true, "is_active": true}
]
```

### Tabel JurnalLifeChecks (mockup)

```json
{
  "id": 1,
  "user_id": 4,
  "life_item_id": 1,
  "tanggal": "2025-06-17",
  "checked": true,
  "response_type": "check",
  "time_range_start": null,
  "time_range_end": null,
  "note": null,
  "created_at": "2025-06-17T08:30:00Z",
  "updated_at": "2025-06-17T08:30:00Z"
}
```

### Tabel Presensi (mockup)

```json
{
  "id": 1,
  "user_id": 4,
  "tanggal": "2025-06-17",
  "jam_masuk": "08:00:00",
  "jam_pulang": "10:00:00",
  "latitude": -1.4578,
  "longitude": 121.9287,
  "status": "hadir",
  "kelas_id": 2,
  "created_at": "2025-06-17T08:02:00Z",
  "updated_at": "2025-06-17T10:05:00Z"
}
```

### Tabel Blogs (mockup)

```json
{
  "id": 1,
  "user_id": 1,
  "cabang_id": 1,
  "title": "Selamat Datang di Study Center Nias",
  "slug": "selamat-datang-di-study-center-nias",
  "content": "<p>Selamat datang di Study Center Nias...</p>",
  "image": "/storage/blogs/welcome.jpg",
  "published_at": "2025-06-17T08:00:00Z",
  "created_at": "2025-06-17T07:00:00Z",
  "updated_at": "2025-06-17T07:00:00Z"
}
```

### Tabel Comments (mockup)

```json
{
  "id": 1,
  "blog_id": 1,
  "user_id": 4,
  "parent_id": null,
  "content": "Terima kasih atas informasinya!",
  "created_at": "2025-06-17T09:00:00Z",
  "updated_at": "2025-06-17T09:00:00Z"
}
```

### Tabel Announcements (mockup)

```json
{
  "id": 1,
  "title": "Pembukaan Pendaftaran Semester Baru",
  "content": "Pendaftaran untuk semester baru telah dibuka. Silakan mendaftar melalui aplikasi.",
  "type": "info",
  "is_active": true,
  "starts_at": "2025-06-17T00:00:00Z",
  "ends_at": "2025-06-30T23:59:59Z",
  "created_by": 1,
  "created_at": "2025-06-17T08:00:00Z",
  "updated_at": "2025-06-17T08:00:00Z"
}
```

### Tabel CollegeBibleItems (mockup) — sample

```json
{
  "id": 1,
  "schedule_id": 1,
  "day_no": 1,
  "pl_text": "Kejadian 1:1-31",
  "pb_text": "Words 1:1-6"
}
```

### Tabel ScholarshipJournals (mockup)

```json
{
  "id": 1,
  "user_id": 4,
  "title": "Laporan Bulan Juni 2025",
  "period_month": 6,
  "period_year": 2025,
  "status": "submitted",
  "submitted_at": "2025-06-30T10:00:00Z",
  "reviewed_by": 1,
  "reviewed_at": "2025-07-01T09:00:00Z",
  "reviewer_notes": "Lengkapi dokumen pendukung",
  "created_at": "2025-06-25T08:00:00Z",
  "updated_at": "2025-06-30T10:00:00Z"
}
```

### Tabel ScholarshipJournalItems (mockup)

```json
{
  "id": 1,
  "journal_id": 1,
  "gpa_current_semester": 3.75,
  "cumulative_gpa": 3.80,
  "academic_summary": "Perhatian baik dalam semua mata",
  "class_attendance_percentage": 95,
  "organization_activities": "Ikut kegiatan padus",
  "training_seminars": "Kursus Bahasa Inggris",
  "achievements": "Juara 2 lomba essay",
  "community_service_details": "Mengajarkan anak-anak di panti asuhan",
  "service_hours": 25,
  "personal_reflection": "Saya belajar banyak tentang disiplin",
  "next_month_goals": "Meningkatkan IP menjadi 3.9"
}
```

### Tabel IssuedCertificates (mockup)

```json
{
  "id": 1,
  "nomor_sertifikat": "SC-NIAS-CERT-2025-001",
  "user_id": 4,
  "template_id": 1,
  "issued_by": 1,
  "tanggal_lulus": "2025-06-17",
  "nama_kursus": "Matematika Dasar",
  "file_path": "/storage/certificates/SC-NIAS-CERT-2025-001.pdf",
  "issued_at": "2025-06-17T10:00:00Z"
}
```

---

## 5. Rencana Endpoint API

Berikut adalah endpoint-endpoint API yang tersedia dan direkomendasikan untuk Android. Semua endpoint dilindungi middleware `auth:sanctum` atau `CheckRole`.

### 🔐 Auth Endpoints

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| POST | `/api/login` | guest | Login user, dapatkan token |
| POST | `/api/register` | guest | Registrasi siswa baru |
| POST | `/api/forgot-password` | guest | Kirim email reset password |
| POST | `/api/reset-password` | guest | Reset password dengan token |
| GET | `/api/user` | auth | Dapatkan profil user + role |
| POST | `/api/logout` | auth | Logout, cakcel token |
| POST | `/api/avatar` | auth | Update avatar user |

### 📊 Dashboard

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/dashboard/stats` | admin | Statistik dashboard admin |

### 📖 Jurnal

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/jurnal/today` | student | Hari ini — entry + life items |
| GET | `/api/jurnal/calendar` | student | Kalender jurnal (marking hari aktif) |
| POST | `/api/jurnal/entry` | student | Buat/update entri harian |
| PUT | `/api/jurnal/entry/{id}` | student | Update entri (hanya hari ini) |
| POST | `/api/jurnal/life-check` | student | Checklist life item |
| GET | `/api/jurnal/weekly-verse` | student | Ayat Alkitab minggu ini |
| GET | `/api/jurnal/history` | student | Riwayat jurnal (2 minggu) |
| GET | `/api/jurnal/life-items` | student | Daftar life items yang tersedia |

### 📋 Presensi

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/presensi/today` | student | Status presensi hari ini |
| POST | `/api/presensi/absen-masuk` | student | Absen masuk |
| POST | `/api/presensi/absen-pulang` | student | Absen pulang |
| GET | `/api/presensi/history` | student | Riwayat presensi |
| GET | `/api/presensi/rekap` | student | Rekap presensi bulanan |

### 👨‍🏫 Mentor Presensi

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/mentor-presensi/today` | mentor | Form presensi hari ini |
| POST | `/api/mentor-presensi/store` | mentor | Simpan presensi mentor |
| GET | `/api/mentor-presensi/history` | mentor | Riwayat presensi mentor |
| GET | `/api/mentor-presensi/rekap` | mentor,admin | Rekap presensi per kelas/cabang |

### 📚 Kelas Master

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/kelas-master` | mentor,admin | Daftar kelas (filter cabang) |
| POST | `/api/kelas-master` | admin | Buat kelas baru |
| PUT | `/api/kelas-master/{id}` | admin | Update kelas |
| DELETE | `/api/kelas-master/{id}` | admin | Hapus kelas |
| GET | `/api/kelas-master/{id}/students` | admin | Daftar siswa di kelas |

### 🏢 Cabang

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/cabangs` | all | Daftar semua cabang |
| GET | `/api/cabangs/{slug}` | all | Detail cabang |
| POST | `/api/cabangs` | admin | Buat cabang |
| PUT | `/api/cabangs/{id}` | admin | Update cabang |
| DELETE | `/api/cabangs/{id}` | admin | Hapus cabang |

### 🎓 Pendaftaran (Registration)

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/pendaftaran/cabangs` | guest | Daftar cabang untuk pendaftaran |
| POST | `/api/pendaftaran` | guest | Submit pendaftaran baru |
| GET | `/api/pendaftaran/status` | auth | Cek status pendaftaran sendiri |
| GET | `/api/admin/pendaftaran` | admin | Daftar pendaftar (filter status/cabang) |
| GET | `/api/admin/pendaftaran/{id}` | admin | Detail pendaftar |
| PUT | `/api/admin/pendaftaran/{id}/validasi` | admin | Validasi pendaftaran |
| POST | `/api/admin/pendaftaran/{id}/update-link` | admin | Generate update link |

### 📝 Blog

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/blogs` | all | List blog (filter: cabang, tag, search, populer) |
| GET | `/api/blogs/{slug}` | all | Detail blog |
| GET | `/api/blogs/tags` | all | Daftar tags |
| GET | `/api/blogs/populer` | all | Blog populer (top viewed) |
| POST | `/api/blogs` | create_blog | Buat blog |
| PUT | `/api/blogs/{id}` | author,admin | Update blog |
| DELETE | `/api/blogs/{id}` | author,admin | Hapus blog |

### 💬 Komentar

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/blogs/{blog}/comments` | all | List komentar (threaded) |
| POST | `/api/blogs/{blog}/comments` | auth | Buat komentar |
| POST | `/api/comments/{comment}/reply` | auth | Reply komentar |
| DELETE | `/api/comments/{comment}` | author,admin | Hapus komentar |

### 👨‍💼 CV

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/cv/{user_id}` | all | Lihat CV user |
| POST | `/api/cv` | auth | Buat/update CV sendiri |
| PUT | `/api/cv` | auth | Update CV |
| GET | `/api/cv/template/{id}` | auth | Dapatkan template CV |

### 🪪 Nametag

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/nametags/templates` | admin | Daftar template |
| GET | `/api/nametags/students` | admin | Cari siswa untuk nametag |
| POST | `/api/nametags/generate` | admin | Generate HTML/preview nametag |
| POST | `/api/nametags/preview` | admin | Preview PDF nametag |

### 🏆 Sertifikat (Certificates)

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/certificates/templates` | admin | Daftar template sertifikat |
| POST | `/api/certificates/template` | admin | Buat template |
| PUT | `/api/certificates/template/{id}` | admin | Update template |
| GET | `/api/certificates/my-certificates` | auth | Sertifikat yang diterima user |
| POST | `/api/certificates/issue` | admin | Issue sertifikat ke user |
| GET | `/api/certificates/{id}/pdf` | auth | Generate & download PDF |

### 📢 Announcement

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/announcements` | auth | List announcement aktif |
| GET | `/api/announcements/{id}` | auth | Detail announcement |
| POST | `/api/announcements` | admin | Buat announcement |
| PUT | `/api/announcements/{id}` | admin | Update |
| DELETE | `/api/announcements/{id}` | admin | Hapus |

### ⛪ College Bible System

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/college-bible/config` | college | Konfigurasi jadwal + form time |
| GET | `/api/college-bible/today` | college | Ayat harian berdasarkan day_no |
| GET | `/api/college-bible/items` | college | Semua item (366 hari) |
| GET | `/api/college-bible/dashboard` | admin,college | Dashboard college (progress matrix) |
| GET | `/api/college-bible/users` | admin,college | Daftar user college |
| GET | `/api/college-bible/users/{id}/matrix` | admin,college | Matrix jurnal user |
| GET | `/api/college-bible/schedules` | admin | Daftar jadwal bible |
| POST | `/api/college-bible/schedules` | admin | Buat jadwal baru |
| PUT | `/api/college-bible/schedules/{id}` | admin | Update jadwal |
| DELETE | `/api/college-bible/schedules/{id}` | admin | Hapus jadwal |
| POST | `/api/college-bible/bulk-schedule` | admin | Bulk create schedule |

### 🎓 Scholarship System

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/scholarship/journals` | scholarship_teenager | List journal saya |
| GET | `/api/scholarship/journals/{id}` | scholarship_teenager | Detail journal |
| POST | `/api/scholarship/journals` | scholarship_teenager | Buat draft journal |
| PUT | `/api/scholarship/journals/{id}` | scholarship_teenager | Update journal |
| POST | `/api/scholarship/journals/{id}/submit` | scholarship_teenager | Submit journal |
| GET | `/api/scholarship/items` | scholarship_teenager | Daftar item akademik/aktivitas |
| POST | `/api/scholarship/attachments` | scholarship_teenager | Upload lampiran |

### 📱 General / Utility

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| GET | `/api/mata-pelajaran` | all | Daftar mata pelajaran |
| GET | `/api/roles` | admin | Daftar roles |
| GET | `/api/tags` | all | Daftar tags blog |
| POST | `/api/upload` | auth | Upload file (foto, dokumen) |

---

## 6. UI Kit Android Reference

Dokumen UI kit lengkap tersedia di:
- `_template/ui_kits/android/` — komponen React + token desain
- `_template/ui_kits/android/README.md` — panduan penggunaan
- File komponen: `Atoms.jsx`, `Common.jsx`, `ScreensCommon.jsx`, `ScreensAdmin.jsx`, `ScreensMentor.jsx`, `bundle.jsx`, `Tokens.jsx`, `index.html`

### Desain Token (colors_and_type.css):

- **Primary:** Biru tua (trust, professionalism)
- **Accent Orange:** #FF6B35 (aksi, highlight)
- **Accent Yellow:** #FFD23F (warning, attention)
- **Neutrals:** Gray scale (#1A1A1A → #F5F5F5)
- **Warna status:** success (green), warning (orange), danger (red)

### Komponen UI:

| Komponen | File | Kegunaan |
|---|---|---|
| `Atoms.jsx` | 162 lines | Button, Input, Card, Chip, Badge |
| `Common.jsx` | 205 lines | Navbar, Footer, Avatar, Form controls |
| `ScreensCommon.jsx` | 455 lines | Home, Login, Register, Profile, Blog list/detail |
| `ScreensAdmin.jsx` | 347 lines | Dashboard, User management, Cabang, Blog admin |
| `ScreensMentor.jsx` | 186 lines | Mentor dashboard, Presensi, Jurnal review |

### Layar (Screens) untuk Android:

1. **Splash Screen** — Logo + loading
2. **Login/Register** — Form auth sederhana
3. **Home / Beranda** — Dashboard berdasarkan role
4. **Jurnal** — Daily entry + checklist + weekly verse
5. **Presensi** — Absen masuk/keluar + riwayat
6. **Blog** — List, detail, komentar
7. **Cabang** — Daftar cabang + info kontak
8. **Profil** — Edit profil, CV, social links
9. **Admin Dashboard** — Statistik, kelola user/cabang/blog
10. **Mentor Dashboard** — Presensi harian, review jurnal
11. **College Dashboard** — Matrix jurnal, bible items
12. **Certificates** — List, generate, preview PDF
13. **Nametag** — Generate kartu nama
14. **Announcements** — List, detail
15. **Scholarship** — Journal, attachments

---

## 7. Pertimbangan Teknis Android

### Navigation (berdasarkan role):

**Guest:**
- Login → Register → Home (read-only blog)

**Student:**
- Home → Jurnal (today, calendar, life checklist, weekly verse)
- Home → Presensi (absen masuk/keluar, riwayat, rekap)
- Home → Blog (list, detail, komentar)
- Profil (edit)
- Announcements

**Mentor:**
- Home → Mentor Presensi (form harian, history, rekap)
- Home → Jurnal Review (lihat jurnal siswa)
- Home → Blog
- Profil

**Admin:**
- Dashboard (stats, charts)
- User Management (list, create, edit, delete)
- Cabang Management
- Kelas Master
- Blog Admin (publish, edit semua blog)
- Jurnal Administration (life items, weekly verse, bible schedule)
- Certificate Templates (CRUD)
- Nametag Templates
- Announcements (CRUD)
- Scholarship Review

**College:**
- Dashboard (progress matrix 14 hari)
- Bible Items (lihat ayat harian)
- College Config (anchor settings)

### Aturan Penting untuk Android:

1. **Auth token storage:** Gunakan Android Keystore untuk menyimpan token aman
2. **File download/upload:** Gunakan protokol HTTPS, handle large file upload (foto, PDF)
3. **Offline-first:** Pertimbangkan lokal cache untuk jurnal/presensi saat offline
4. **Image loading:** Foto profil, foto belajar, cover blog — gunakan library seperti Glide/Picasso
5. **Timezone:** Semua waktu server dalam UTC. Gunakan timezone lokal Indonesia (Asia/Jakarta, UTC+7)
6. **Pagination:** Gunakan `page` query param, default 15 records/page. Untuk college dashboard, gunakan 20.
7. **Error handling:** Tampilkan pesan error dari server (field validation errors, HTTP errors)
8. **Form validation:** Validasi di sisi klien sesuai aturan di server (contoh: password min 5 karakter)
9. **Role-based UI rendering:** Sembunyikan/tampilkan menu berdasarkan role user
10. **Push notifications:** Pertimbangkan untuk announcement baru (menggunakan Firebase)

---

## 📎 Lampiran

### A. File Reference

```
Project Root
├── API_DOCS.md              ← Dokumentasi API (baca ini!)
├── README.md
├── composer.json
├── docker-compose.yml
├── routes/
│   ├── api.php              ← 223 lines — semua API endpoint
│   ├── web.php              ← 363 lines — web routes
│   ├── auth.php             ← 38 lines — auth routes
│   └── console.php          ← 9 lines — artisan commands
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── Admin/   ← Admin API controllers
│   │   │   └── Admin/       ← Admin web controllers
│   │   └── Middleware/
│   │       ├── CheckRole.php
│   │       └── HandleCors.php
│   ├── Models/              ← 16+ model Eloquent
│   └── Support/
│       └── JurnalWeek.php   ← Helper perhitungan minggu jurnal
├── database/
│   ├── migrations/          ← 30+ migration files
│   └── seeders/             ← 11+ seeders
├── config/
│   ├── sync.php             ← Konfigurasi sync (untuk prod deployment)
│   ├── sanctum.php          ← Sanctum auth config
│   ├── cors.php             ← CORS config (semua terbuka)
│   └── cache.php
└── _template/ui_kits/android/  ← UI kit React components
    ├── README.md
    ├── Tokens.jsx
    ├── Atoms.jsx
    ├── Common.jsx
    ├── ScreensCommon.jsx
    ├── ScreensAdmin.jsx
    ├── ScreensMentor.jsx
    └── bundle.jsx
```

### B. Environment Variables (.env)

| Variable | Deskripsi |
|---|---|
| `APP_KEY` | Laravel encryption key |
| `APP_URL` | Base URL aplikasi |
| `APP_ENV` | `production` / `local` |
| `APP_DEBUG` | Boolean — aktifkan untuk debugging |
| `DB_CONNECTION` | `mysql` / `sqlite` |
| `DB_HOST` | Database host |
| `DB_DATABASE` | Nama database |
| `DB_USERNAME` | Username database |
| `DB_PASSWORD` | Password database |
| `SANCTUM_STATEFUL_DOMAINS` | Domain stateless auth |
| `SYNC_SECRET_KEY` | Secret key untuk sync production |
| `SYNC_TARGET_URL` | Target URL untuk sync |
| `SYNC_RSYNC_SOURCE` | Rsync source untuk sync |

### C. JurnalWeek Helper

Class `App\Support\JurnalWeek` adalah inti dari sistem jurnal:

| Method | Return | Deskripsi |
|---|---|---|
| `JurnalWeek::today()` | Carbon | Tanggal hari ini di timezone lokal |
| `JurnalWeek::weekNumber(Carbon $date)` | int | Nomor minggu ke-N |
| `JurnalWeek::weekStart(Carbon $date)` | Carbon | Tanggal Selasa (awal minggu) |
| `JurnalWeek::weekEnd(Carbon $date)` | Carbon | Tanggal Senin (akhir minggu) |
| `JurnalWeek::currentAyat()` | array | Ayat Alkitab untuk minggu ini |
| `JurnalWeek::all()` | array | Semua jadwal pembacaan |
| `JurnalWeek::TZ` | string | Timezone: Asia/Jayapura |

### D. Migration Timeline

Berikut urutan migrasi penting yang harus dijalankan di Android (jika butuh reference skema DB):

1. `0001_01_01_000000_create_users_table.php` — roles, cabangs, users, sessions
2. `2026_05_06_000001_create_blogs_table.php` — tags, blogs, blog_tag, comments
3. `2026_05_09_000002_create_user_roles_and_profile_tables.php` — user_roles, profile tables
4. `2026_05_10_000001_create_presensi_tables.php` — presensi
5. `2026_05_14_000001_create_jurnal_tables.php` — jurnal_entries, life_items, life_checks, weekly_verses
6. `2026_05_15_000001_create_kelas_master_table.php` — kelas_master
7. `2026_06_29_000001_create_certificate_templates_table.php`
8. `2026_06_29_000002_create_issued_certificates_table.php`
9. `2026_07_06_005033_create_college_profiles_table.php`
10. `2026_07_06_005034_create_scholarship_journals_table.php`
11. `2026_07_06_005035_create_scholarship_journal_items_table.php`
12. `2026_07_06_005036_create_scholarship_journal_attachments_table.php`
13. `2026_07_06_020001_create_college_bible_items_table.php`
14. `2026_07_06_020003_create_college_study_logs_table.php`
15. `2026_07_13_000003_create_mata_pelajarans_table.php`
16. `2026_07_29_195559_create_college_bible_schedules_table.php`
17. `2026_07_31_220000_create_announcements_table.php`
18. `2026_07_12_205326_create_nametag_templates_table.php`

---

> **Disclaimer:** Dokumen ini disusun berdasarkan analisis kode font-end dan back-end yang ada di `/var/www/study-center-nias/`. Beberapa field atau endpoint mungkin memerlukan penyesuaian saat implementasi Android didatangkan ke sisi backend yang sudah running. Semua credential dan secret telah disanitasi dalam dokumen ini.

---

*Dokumen disusun untuk persiapan implementasi Android frontend. File ini disimpan di `docs/android-preparation-plan.md`*.
