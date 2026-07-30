# Study Center NIAS — API Reference (Student Role)

> Dokumen ini adalah referensi tunggal untuk pengembangan Android APK Study Center NIAS.
> Mencakup semua endpoint yang dibutuhkan oleh role **student**.
> Semua endpoint telah diverifikasi live di production.

---

## 1. Overview

| Item | Value |
|------|-------|
| **Base URL** | `https://studycenter.nanoprojectdevindonesia.com` |
| **API Prefix** | `/api` |
| **Auth Type** | Laravel Sanctum (Bearer Token) |
| **Content-Type** | `application/json` (kecuali upload file: `multipart/form-data`) |
| **Timezone** | `Asia/Jakarta` (semua tanggal/waktu) |
| **Pagination** | Default 12–20 item per halaman |

### Headers Wajib

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Format Error

```json
{
  "message": "Deskripsi error",
  "errors": {
    "field_name": ["Pesan validasi"]
  }
}
```

| HTTP Code | Arti |
|-----------|------|
| 200 | OK |
| 201 | Created |
| 401 | Unauthenticated (token tidak ada / expired) |
| 403 | Forbidden (akun nonaktif / role tidak cukup) |
| 404 | Resource tidak ditemukan |
| 422 | Validation error |
| 429 | Rate limit exceeded |

---

## 2. Authentication

### 2.1 Login

```http
POST /api/auth/login
```

**Auth Required:** Tidak  
**Rate Limit:** 10 req/menit

**Request Body:**
```json
{
  "login": "username_atau_email",
  "password": "password123"
}
```

> `login` bisa berupa username atau email. Alternatif: gunakan field `email` sebagai pengganti `login`.

**Response 200:**
```json
{
  "user": {
    "id": 99,
    "name": "Test Student API",
    "username": "teststudentapi",
    "email": "teststudentapi@test.com",
    "email_verified_at": null,
    "avatar": null,
    "bio": null,
    "cabang_id": 1,
    "is_active": true,
    "profile_public": true,
    "cv_enabled": false,
    "roles": [
      { "id": 5, "name": "student" }
    ],
    "cabang": {
      "id": 1,
      "nama": "Gunungsitoli",
      "slug": "gunungsitoli"
    }
  },
  "token": "22|8GP2N7JaDIarpQOxKUkoWNUUddp5Oxih..."
}
```

**Error 401:**
```json
{ "message": "Username/email atau password salah." }
```

**Error 403:**
```json
{ "message": "Akun Anda tidak aktif." }
```

---

### 2.2 Register

```http
POST /api/auth/register
```

**Auth Required:** Tidak  
**Rate Limit:** 5 req/menit

> **Penting:** Akun baru otomatis mendapat role `guest`. Admin harus assign role `student` secara manual melalui web admin.

**Request Body:**
```json
{
  "name": "Nama Lengkap",
  "email": "email@example.com",
  "password": "password123"
}
```

**Validasi:**
- `name`: wajib, max 255 karakter
- `email`: wajib, format email, unik
- `password`: wajib, min 8 karakter, harus mengandung huruf dan angka

**Response 201:**
```json
{
  "user": {
    "id": 100,
    "name": "Nama Lengkap",
    "username": "nama-lengkap",
    "email": "email@example.com",
    "avatar": null,
    "cabang_id": null,
    "is_active": true,
    "roles": [{ "id": 6, "name": "guest" }],
    "cabang": null
  },
  "token": "23|abc123..."
}
```

---

### 2.3 Google Login (Mobile)

```http
POST /api/auth/google
```

**Auth Required:** Tidak  
**Rate Limit:** 10 req/menit

> Gunakan Google Sign-In SDK di Android, ambil `id_token`, kirim ke endpoint ini.
> Jika akun belum ada, otomatis dibuat dengan role `student`.

**Request Body:**
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIs..."
}
```

**Response 200:**
```json
{
  "token": "24|xyz789...",
  "user": {
    "id": 101,
    "name": "Nama dari Google",
    "username": "nama-dari-google",
    "email": "user@gmail.com",
    "avatar": "https://lh3.googleusercontent.com/...",
    "cabang_id": null,
    "roles": [{ "id": 5, "name": "student" }],
    "cabang": null
  }
}
```

---

### 2.4 Logout

```http
POST /api/auth/logout
```

**Auth Required:** Ya

**Response 200:**
```json
{ "message": "Logged out." }
```

---

### 2.5 Refresh Token

```http
POST /api/auth/refresh
```

**Auth Required:** Ya

> Token lama dihapus, token baru dikembalikan. Gunakan ketika token mendekati expired.

**Response 200:**
```json
{
  "user": {
    "id": 99,
    "name": "Test Student API",
    "username": "teststudentapi",
    "email": "teststudentapi@test.com",
    "avatar": null,
    "roles": [{ "id": 5, "name": "student" }],
    "cabang": { "id": 1, "nama": "Gunungsitoli", "slug": "gunungsitoli" }
  },
  "token": "25|newtoken123..."
}
```

---

## 3. User & Profile

### 3.1 Get Current User (Me)

```http
GET /api/me
```

**Auth Required:** Ya

**Response 200:**
```json
{
  "id": 99,
  "name": "Test Student API",
  "username": "teststudentapi",
  "email": "teststudentapi@test.com",
  "email_verified_at": null,
  "avatar": null,
  "bio": null,
  "cabang_id": 1,
  "is_active": true,
  "profile_public": true,
  "cv_enabled": false,
  "roles": [
    { "id": 5, "name": "student" }
  ],
  "cabang": {
    "id": 1,
    "nama": "Gunungsitoli",
    "slug": "gunungsitoli"
  },
  "socialLinks": [
    {
      "id": 1,
      "platform": "instagram",
      "value": "https://instagram.com/username"
    }
  ],
  "role_names": ["student"]
}
```

---

### 3.2 Update Profile

```http
PUT /api/profile
Content-Type: multipart/form-data
```

**Auth Required:** Ya

**Request Body (form-data):**
```
name         = "Nama Baru"                        (string, opsional, max 255)
bio          = "Bio singkat"                      (string, opsional, max 500)
cabang_id    = 1                                  (integer, opsional)
avatar       = [file image, max 2MB]              (file, opsional)
profile_public = true                             (boolean, opsional)
cv_enabled   = false                              (boolean, opsional)
social_links[0][platform] = "instagram"           (string: instagram|whatsapp|email|facebook)
social_links[0][value]    = "https://..."         (string, max 255)
```

**Response 200:** User object lengkap (sama dengan `/api/me`)

---

### 3.3 Get Public Profile

```http
GET /api/profil/{username}
```

**Auth Required:** Tidak

**Response 200:**
```json
{
  "user": {
    "id": 99,
    "name": "Test Student API",
    "username": "teststudentapi",
    "email": "teststudentapi@test.com",
    "avatar": null,
    "bio": null,
    "cabang_id": 1,
    "is_active": true,
    "profile_public": true,
    "cv_enabled": false,
    "roles": [{ "id": 5, "name": "student" }],
    "cabang": { "id": 1, "nama": "Gunungsitoli", "slug": "gunungsitoli" },
    "socialLinks": []
  },
  "blogs": []
}
```

> Hanya tampil jika user aktif dan `profile_public = true`.

---

### 3.4 Get Business Card (Kartu Nama)

```http
GET /api/profil/{username}/kartu-nama
```

**Auth Required:** Tidak

**Response 200:**
```json
{
  "user": {
    "id": 99,
    "name": "Test Student API",
    "username": "teststudentapi",
    "avatar": null,
    "bio": null,
    "role": "student",
    "cabang": "Gunungsitoli"
  },
  "social_links": [
    { "id": 1, "platform": "instagram", "value": "https://instagram.com/..." }
  ]
}
```

---

## 4. CV (Curriculum Vitae)

### 4.1 Get My CV

```http
GET /api/cv
```

**Auth Required:** Ya

**Response 200 (sudah ada data):**
```json
{
  "id": 1,
  "user_id": 99,
  "pendidikan": [
    {
      "jenjang": "SMA",
      "institusi": "SMAN 1 Gunungsitoli",
      "tahun_lulus": "2024"
    }
  ],
  "pengalaman": [
    {
      "posisi": "Ketua OSIS",
      "deskripsi": "Memimpin organisasi siswa",
      "tahun": "2023"
    }
  ],
  "keterampilan": ["Desain Grafis", "Public Speaking"],
  "portofolio": "https://portfolio.example.com",
  "template": "template1",
  "created_at": "2026-07-01T00:00:00.000000Z",
  "updated_at": "2026-07-30T00:00:00.000000Z"
}
```

**Response 200 (belum ada data):**
```json
{
  "user_id": 99
}
```

> Cek apakah ada field selain `user_id` untuk menentukan apakah CV sudah diisi.

---

### 4.2 Update / Create CV

```http
PUT /api/cv
```

**Auth Required:** Ya

**Request Body:**
```json
{
  "pendidikan": [
    {
      "jenjang": "SMA",
      "institusi": "SMAN 1 Gunungsitoli",
      "tahun_lulus": "2024"
    }
  ],
  "pengalaman": [
    {
      "posisi": "Ketua OSIS",
      "deskripsi": "Deskripsi kegiatan",
      "tahun": "2023"
    }
  ],
  "keterampilan": ["Desain Grafis", "Public Speaking"],
  "portofolio": "https://portfolio.example.com",
  "template": "template1"
}
```

**Validasi:**
- `pendidikan[].jenjang`: wajib jika ada
- `pendidikan[].institusi`: wajib jika ada
- `template`: salah satu dari `template1`, `template2`, `template3`

**Response 200:** CV object lengkap

---

### 4.3 Get Public CV

```http
GET /api/profil/{username}/cv
```

**Auth Required:** Tidak

> Hanya muncul jika `cv_enabled = true` di profil user.

**Response 200:**
```json
{
  "user": {
    "id": 99,
    "name": "Test Student API",
    "username": "teststudentapi",
    "avatar": null,
    "bio": null,
    "role": "student",
    "cabang": "Gunungsitoli"
  },
  "cv": {
    "id": 1,
    "pendidikan": [...],
    "pengalaman": [...],
    "keterampilan": [...],
    "portofolio": null,
    "template": "template1",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

---

## 5. Jurnal Harian

> Semua endpoint Jurnal memerlukan role **student**. Admin/mentor tidak bisa akses.

### 5.1 Get Jurnal Hari Ini

```http
GET /api/jurnal/today
GET /api/jurnal/today?date=2026-07-30
```

**Auth Required:** Ya (role: student)

**Query Params:**
| Param | Type | Default | Keterangan |
|-------|------|---------|-----------|
| `date` | string (YYYY-MM-DD) | hari ini | Tanggal yang ingin dilihat |

**Response 200:**
```json
{
  "date": "2026-07-30",
  "week": {
    "tahun": 2026,
    "bulan": 7,
    "minggu": 4,
    "key": "2026-07-4"
  },
  "bible": {
    "pl_porsi": "Kejadian 1-2",
    "pb_porsi": "Matius 1",
    "pl_checked": false,
    "pb_checked": false
  },
  "verse_ref": "Yohanes 3:16",
  "foto_belajar_url": "https://studycenter.nanoprojectdevindonesia.com/storage/jurnal-foto/2026/07/abc.jpg",
  "life_items": [
    {
      "id": 1,
      "kategori": "Pembinaan",
      "label": "Doa Pagi",
      "response_type": "checkbox",
      "checked": false
    },
    {
      "id": 2,
      "kategori": "Pembinaan",
      "label": "Baca Alkitab",
      "response_type": "checkbox",
      "checked": true
    }
  ]
}
```

> `bible.pl_porsi` / `bible.pb_porsi` = `null` jika cabang belum punya jadwal baca aktif.
> `verse_ref` = hafalan ayat minggu ini (bisa null jika belum dikonfigurasi).
> `life_items` = daftar item jadwal kehidupan yang di-assign ke student ini.

---

### 5.2 Check / Uncheck Item Jurnal

```http
POST /api/jurnal/check
```

**Auth Required:** Ya (role: student)

**Request Body:**
```json
{
  "item_type": "pl",
  "date": "2026-07-30",
  "checked": true
}
```

**`item_type` options:**

| Value | Keterangan | Field tambahan wajib |
|-------|-----------|---------------------|
| `pl` | Perjanjian Lama (baca Alkitab) | — |
| `pb` | Perjanjian Baru (baca Alkitab) | — |
| `verse` | Hafal Ayat Mingguan | `verse_ref` (string, max 100) |
| `life` | Item Jadwal Kehidupan | `item_id` (integer) |

**Contoh — check PL:**
```json
{
  "item_type": "pl",
  "date": "2026-07-30",
  "checked": true
}
```

**Contoh — isi hafal ayat:**
```json
{
  "item_type": "verse",
  "date": "2026-07-30",
  "checked": true,
  "verse_ref": "Yohanes 3:16"
}
```

**Contoh — check life item:**
```json
{
  "item_type": "life",
  "item_id": 1,
  "date": "2026-07-30",
  "checked": true
}
```

**Response 200:**
```json
{
  "ok": true,
  "state": {
    "date": "2026-07-30",
    "week": {
      "tahun": 2026,
      "bulan": 7,
      "minggu": 4,
      "key": "2026-07-4"
    },
    "bible": {
      "pl_porsi": "Kejadian 1-2",
      "pb_porsi": "Matius 1",
      "pl_checked": true,
      "pb_checked": false
    },
    "verse_ref": null,
    "foto_belajar_url": null,
    "life_items": [
      {
        "id": 1,
        "kategori": "Pembinaan",
        "label": "Doa Pagi",
        "response_type": "checkbox",
        "checked": false
      }
    ]
  }
}
```

**Validasi:**
- `date` tidak boleh di masa depan
- `item_id` wajib jika `item_type = life`
- `verse_ref` wajib jika `item_type = verse`

---

### 5.3 Get Riwayat Jurnal

```http
GET /api/jurnal/history?from=2026-07-01&to=2026-07-30
```

**Auth Required:** Ya (role: student)

**Query Params:**
| Param | Type | Wajib | Keterangan |
|-------|------|-------|-----------|
| `from` | string (YYYY-MM-DD) | Ya | Tanggal mulai |
| `to` | string (YYYY-MM-DD) | Ya | Tanggal akhir (≥ from) |

**Response 200:**
```json
{
  "data": [
    {
      "date": "2026-07-01",
      "pl_checked": false,
      "pb_checked": false,
      "verse_checked": false,
      "life_checked_ids": []
    },
    {
      "date": "2026-07-02",
      "pl_checked": true,
      "pb_checked": true,
      "verse_checked": false,
      "life_checked_ids": [1, 3]
    }
  ]
}
```

> Setiap hari dalam range selalu ada entry, meski semua false/kosong.
> `life_checked_ids` = array ID life_items yang sudah dicentang hari itu.

---

### 5.4 Upload Foto Belajar

```http
POST /api/jurnal/foto
Content-Type: multipart/form-data
```

**Auth Required:** Ya (role: student)

**Request Body (form-data):**
```
foto = [file image, max 4MB, format: jpeg/jpg/png/webp]
date = "2026-07-30" (string YYYY-MM-DD, opsional, default hari ini)
```

**Response 200:**
```json
{
  "ok": true,
  "url": "https://.../storage/jurnal-foto/2026/07/abc.jpg",
  "state": { /* state object persis seperti GET /api/jurnal/today */ }
}
```

---

### 5.5 Hapus Foto Belajar

```http
DELETE /api/jurnal/foto
```

**Auth Required:** Ya (role: student)

**Request Body:**
```json
{
  "date": "2026-07-30" 
}
```
> `date` opsional, default hari ini.

**Response 200:**
```json
{
  "ok": true,
  "state": { /* state object persis seperti GET /api/jurnal/today */ }
}
```

---

## 6. Laporan Progress

### 6.1 Ringkasan 30 Hari

```http
GET /api/laporan/my
```

**Auth Required:** Ya (role: student)

**Response 200:**
```json
{
  "from": "2026-07-01",
  "to": "2026-07-30",
  "pct": 45.5,
  "checked": 41,
  "total": 90,
  "streak": 3
}
```

| Field | Keterangan |
|-------|-----------|
| `from` / `to` | Range 30 hari terakhir |
| `pct` | Persentase penyelesaian (0–100) |
| `checked` | Total item yang sudah dicentang |
| `total` | Total item yang mungkin (PL + PB + life items × hari) |
| `streak` | Jumlah hari berturut-turut menyelesaikan semua item |

---

### 6.2 Matrix Detail

```http
GET /api/laporan/my/matrix
GET /api/laporan/my/matrix?from=2026-07-24&to=2026-07-30
```

**Auth Required:** Ya (role: student)

**Query Params:**
| Param | Type | Default | Keterangan |
|-------|------|---------|-----------|
| `from` | string (YYYY-MM-DD) | 13 hari lalu | Tanggal mulai |
| `to` | string (YYYY-MM-DD) | hari ini | Tanggal akhir |

**Response 200:**
```json
{
  "from": "2026-07-24",
  "to": "2026-07-30",
  "headers": [
    "Tanggal",
    "PL",
    "PB",
    "Hafal Ayat",
    "Pembinaan: Doa Pagi",
    "Pembinaan: Baca Alkitab"
  ],
  "rows": [
    ["2026-07-24", "-", "-", "-", "-", "-"],
    ["2026-07-25", "Y", "Y", "-", "Y", "-"],
    ["2026-07-26", "Y", "-", "-", "-", "Y"],
    ["2026-07-27", "-", "-", "-", "-", "-"],
    ["2026-07-28", "-", "-", "-", "-", "-"],
    ["2026-07-29", "Y", "Y", "Y", "Y", "Y"],
    ["2026-07-30", "Y", "-", "-", "-", "-"]
  ],
  "pct": 38.1,
  "checked": 8,
  "total": 21
}
```

> `Y` = selesai, `-` = belum.
> `headers[3+]` = nama kolom setiap life item dalam format `"Kategori: Label"`.

---

## 7. Galeri Foto

### 7.1 Get Galeri

```http
GET /api/galeri
```

**Auth Required:** Ya (role: student)

> Menampilkan foto presensi terbaru dari cabang student (max 20 foto).

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "foto_url": "https://studycenter.nanoprojectdevindonesia.com/storage/presensi/foto.jpg",
      "tanggal": "2026-07-29"
    },
    {
      "id": 2,
      "foto_url": "https://studycenter.nanoprojectdevindonesia.com/storage/presensi/foto2.jpg",
      "tanggal": "2026-07-22"
    }
  ]
}
```

---

## 8. Blog & Artikel

### 8.1 List Blog (Publik)

```http
GET /api/blogs
GET /api/blogs?page=1&cabang=gunungsitoli&search=judul
```

**Auth Required:** Tidak

**Query Params:**
| Param | Type | Keterangan |
|-------|------|-----------|
| `page` | integer | Halaman (default: 1) |
| `cabang` | string | Filter by cabang slug |
| `tag` | string | Filter by tag slug |
| `author` | string | Filter by username |
| `search` | string | Cari di judul |
| `sort` | string | `latest` (default) atau `popular` |

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Judul Artikel",
      "slug": "judul-artikel",
      "content": "<p>Isi artikel...</p>",
      "image": "/storage/blogs/foto.jpg",
      "published_at": "2026-07-15T08:00:00.000000Z",
      "user": {
        "id": 10,
        "name": "Administrator",
        "username": "administrator",
        "avatar": null
      },
      "cabang": {
        "id": 1,
        "nama": "Gunungsitoli",
        "slug": "gunungsitoli"
      },
      "tags": [
        { "id": 1, "name": "Rohani", "slug": "rohani" }
      ]
    }
  ],
  "links": {
    "first": "https://.../api/blogs?page=1",
    "last": "https://.../api/blogs?page=3",
    "prev": null,
    "next": "https://.../api/blogs?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 12,
    "to": 12,
    "total": 30
  }
}
```

---

### 8.2 Detail Blog

```http
GET /api/blogs/{slug}
```

**Auth Required:** Tidak

**Response 200:**
```json
{
  "id": 1,
  "title": "Judul Artikel",
  "slug": "judul-artikel",
  "content": "<p>Isi artikel HTML...</p>",
  "image": "/storage/blogs/foto.jpg",
  "published_at": "2026-07-15T08:00:00.000000Z",
  "user": {
    "id": 10,
    "name": "Administrator",
    "username": "administrator",
    "avatar": null,
    "cabang_id": null,
    "roles": [{ "id": 2, "name": "admin" }]
  },
  "cabang": { "id": 1, "nama": "Gunungsitoli", "slug": "gunungsitoli" },
  "tags": [{ "id": 1, "name": "Rohani", "slug": "rohani" }]
}
```

---

### 8.3 Buat Blog

```http
POST /api/blogs
Content-Type: multipart/form-data
```

**Auth Required:** Ya (role: student, mentor, fulltimer, admin)

**Request Body (form-data):**
```
title      = "Judul Artikel"       (string, wajib, max 255)
content    = "<p>Isi...</p>"       (string, wajib, HTML)
cabang_id  = 1                     (integer, wajib)
tags[]     = "Rohani"              (string array, opsional)
image      = [file, max 2MB]       (file, opsional)
```

**Response 201:** Blog object lengkap  
**Catatan:** Slug auto-generated dari title. Jika duplikat, ditambah suffix `-1`, `-2`.

---

### 8.4 Update Blog

```http
PUT /api/blogs/{blog_id}
Content-Type: multipart/form-data
```

**Auth Required:** Ya (hanya pemilik blog atau admin)

**Request Body:** Sama seperti buat blog, semua field opsional.

**Response 200:** Blog object lengkap

---

### 8.5 Hapus Blog

```http
DELETE /api/blogs/{blog_id}
```

**Auth Required:** Ya (hanya pemilik blog atau admin)

**Response 200:**
```json
{ "message": "Blog dihapus." }
```

---

### 8.6 Upload Gambar Inline Blog

```http
POST /api/blogs/upload-image
Content-Type: multipart/form-data
```

**Auth Required:** Ya (role: student, mentor, fulltimer, admin)

**Request Body:**
```
image = [file image, max 5MB, format: jpeg/jpg/png/gif/webp]
```

**Response 200:**
```json
{
  "url": "https://studycenter.nanoprojectdevindonesia.com/storage/blogs/inline/abc123.jpg"
}
```

---

## 9. Komentar Blog

### 9.1 List Komentar

```http
GET /api/blogs/{blog_id}/comments
```

**Auth Required:** Tidak

**Response 200:**
```json
[
  {
    "id": 1,
    "content": "Komentar ini sangat bagus!",
    "parent_id": null,
    "user": {
      "id": 99,
      "name": "Test Student",
      "username": "teststudent",
      "avatar": null
    },
    "replies": [
      {
        "id": 2,
        "content": "Terima kasih!",
        "user": {
          "id": 10,
          "name": "Administrator",
          "username": "administrator",
          "avatar": null
        }
      }
    ],
    "created_at": "2026-07-15T09:00:00.000000Z",
    "updated_at": "2026-07-15T09:00:00.000000Z"
  }
]
```

---

### 9.2 Kirim Komentar

```http
POST /api/blogs/{blog_id}/comments
```

**Auth Required:** Ya  
**Rate Limit:** 20 req/menit

**Request Body:**
```json
{
  "content": "Isi komentar",
  "parent_id": null
}
```

> `parent_id` = ID komentar yang ingin dibalas (untuk reply). Kosongkan/null untuk komentar utama.

**Validasi:**
- `content`: wajib, max 2000 karakter

**Response 201:** Comment object

---

### 9.3 Hapus Komentar

```http
DELETE /api/comments/{comment_id}
```

**Auth Required:** Ya (hanya pemilik komentar atau admin)

**Response 200:**
```json
{ "message": "Komentar dihapus." }
```

---

## 10. Cabang (Branch)

### 10.1 List Semua Cabang

```http
GET /api/cabangs
```

**Auth Required:** Tidak

**Response 200 (live data):**
```json
[
  {
    "id": 1,
    "nama": "Gunungsitoli",
    "slug": "gunungsitoli",
    "alamat": "Kota Gunungsitoli, Nias",
    "kontak": null,
    "foto_wajib": false,
    "pendaftaran_buka": true,
    "kelas_min": 6,
    "kelas_max": 9,
    "mata_pelajaran": ["MATEMATIKA", "BAHASA INGGRIS", "BAHASA MANDARIN"],
    "bible_schedule_id": null,
    "whatsapp_link": "https://chat.whatsapp.com/...",
    "created_at": null,
    "updated_at": "2026-07-12T23:38:10.000000Z"
  },
  {
    "id": 2,
    "nama": "Teluk Dalam",
    "slug": "teluk-dalam",
    "alamat": "Teluk Dalam, Nias Selatan",
    "kontak": null,
    "foto_wajib": false,
    "pendaftaran_buka": true,
    "kelas_min": 6,
    "kelas_max": 12,
    "mata_pelajaran": [],
    "bible_schedule_id": null,
    "whatsapp_link": null,
    "created_at": null,
    "updated_at": "2026-07-10T03:38:52.000000Z"
  }
]
```

---

### 10.2 Detail Cabang + Blog

```http
GET /api/cabangs/{slug}
```

**Auth Required:** Tidak

**Response 200:**
```json
{
  "cabang": {
    "id": 1,
    "nama": "Gunungsitoli",
    "slug": "gunungsitoli",
    "alamat": "Kota Gunungsitoli, Nias",
    "kontak": null,
    "foto_wajib": false,
    "pendaftaran_buka": true,
    "kelas_min": 6,
    "kelas_max": 9,
    "mata_pelajaran": ["MATEMATIKA", "BAHASA INGGRIS", "BAHASA MANDARIN"],
    "bible_schedule_id": null,
    "whatsapp_link": "https://chat.whatsapp.com/...",
    "blogs_count": 12
  },
  "blogs": {
    "data": [...],
    "links": {...},
    "meta": { "current_page": 1, "last_page": 2, "per_page": 12, "total": 12 }
  }
}
```

---

## 11. Ringkasan Akses per Fitur

| Fitur Android | Endpoint | Auth | Role |
|--------------|----------|------|------|
| Login | POST /api/auth/login | Tidak | — |
| Google Login | POST /api/auth/google | Tidak | — |
| Daftar | POST /api/auth/register | Tidak | — |
| Logout | POST /api/auth/logout | Ya | — |
| Data diri | GET /api/me | Ya | — |
| Edit profil | PUT /api/profile | Ya | — |
| Profil publik | GET /api/profil/{username} | Tidak | — |
| Kartu nama | GET /api/profil/{username}/kartu-nama | Tidak | — |
| CV saya | GET /api/cv | Ya | — |
| Update CV | PUT /api/cv | Ya | — |
| Jurnal hari ini | GET /api/jurnal/today | Ya | student |
| Check item jurnal | POST /api/jurnal/check | Ya | student |
| Upload foto jurnal | POST /api/jurnal/foto | Ya | student |
| Hapus foto jurnal | DELETE /api/jurnal/foto | Ya | student |
| Riwayat jurnal | GET /api/jurnal/history | Ya | student |
| Laporan 30 hari | GET /api/laporan/my | Ya | student |
| Matrix laporan | GET /api/laporan/my/matrix | Ya | student |
| Galeri foto | GET /api/galeri | Ya | student |
| List artikel | GET /api/blogs | Tidak | — |
| Detail artikel | GET /api/blogs/{slug} | Tidak | — |
| Tulis artikel | POST /api/blogs | Ya | student+ |
| Edit artikel | PUT /api/blogs/{id} | Ya | pemilik/admin |
| Hapus artikel | DELETE /api/blogs/{id} | Ya | pemilik/admin |
| Upload gambar | POST /api/blogs/upload-image | Ya | student+ |
| Komentar | GET /api/blogs/{id}/comments | Tidak | — |
| Kirim komentar | POST /api/blogs/{id}/comments | Ya | — |
| Hapus komentar | DELETE /api/comments/{id} | Ya | pemilik/admin |
| List cabang | GET /api/cabangs | Tidak | — |
| Detail cabang | GET /api/cabangs/{slug} | Tidak | — |

---

## 12. Catatan Implementasi Android

### Token Management
- Simpan token di **EncryptedSharedPreferences** atau **Keystore**
- Saat response 401, redirect ke halaman login
- Refresh token sebelum expire: `POST /api/auth/refresh`

### File Upload
- Gunakan `multipart/form-data` bukan `application/json`
- Limit size: avatar 2MB, blog image 5MB
- Format yang diterima: jpeg, jpg, png, gif, webp
- URL gambar dari response adalah path relatif atau absolute tergantung field

### URL Gambar
- Avatar & blog image mungkin berupa path saja (contoh: `/storage/blogs/foto.jpg`)
- Prepend base URL: `https://studycenter.nanoprojectdevindonesia.com` + path
- Galeri foto sudah berupa URL lengkap

### Timezone
- Semua operasi jurnal gunakan tanggal lokal `Asia/Jakarta` (WIB, UTC+7)
- Kirim tanggal dalam format `YYYY-MM-DD`
- Jangan kirim tanggal masa depan ke `/api/jurnal/check`

### Pagination
- Gunakan field `links.next` untuk mengetahui apakah ada halaman berikutnya
- Default per halaman: 12 (blog), 20 (galeri)
- Query param: `?page=2`

### Rate Limiting
- Register: 5 req/menit
- Login: 10 req/menit
- Komentar: 20 req/menit
- Response 429: tampilkan pesan "Terlalu banyak percobaan, coba lagi nanti"

### Jurnal — Logic Penting
- `GET /api/jurnal/today` dipanggil saat buka halaman Jurnal
- `POST /api/jurnal/check` dipanggil setiap kali user tap checkbox
- Response dari `check` mengembalikan state terbaru — update UI dari response ini
- `verse_ref` harus diisi oleh user (teks ayat hafalan), kirim bersamaan dengan `checked: true`
- Hafal ayat bersifat mingguan: satu ayat untuk semua hari dalam minggu yang sama

### Test Account (Development)
```
Username : teststudentapi
Password : password123
Role     : student
Cabang   : Gunungsitoli (id: 1)
```

---

*Dokumen ini dibuat berdasarkan source code Laravel dan live testing di production.*  
*Last updated: 2026-07-30*
