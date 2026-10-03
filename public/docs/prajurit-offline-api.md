# API Presensi Offline Prajurit — Dokumentasi untuk Pengembang Android

**Base URL:** `https://studycenter.nanoprojectdevindonesia.com/api`  
**Versi:** 1.0.0  
**Tanggal:** 2026-09-27  
**Untuk:** Pengembang APK Android Presensi Prajurit Study Center Nias

---

## Arsitektur Offline-First

Aplikasi Android dirancang menggunakan pola **offline-first**:

```
┌─────────────────────────────────────────────────────────────────┐
│                        ANDROID APK                              │
│                                                                 │
│  ┌─────────────┐   Scan QR   ┌──────────────────────────────┐  │
│  │  QR Scanner │ ──────────▶ │   Local SQLite / Room DB     │  │
│  └─────────────┘             │                              │  │
│                              │  - prajurits[]               │  │
│  ┌─────────────┐  Centang    │  - life_items[]              │  │
│  │ Form Jurnal │ ──────────▶ │  - pending_syncs[]           │  │
│  └─────────────┘             │  - synced_records[]          │  │
│                              └──────────────────────────────┘  │
│                                         │                       │
│                              Saat Online ↓                      │
│                    ┌─────────────────────────────┐             │
│                    │      SYNC SERVICE           │             │
│                    │  - Pull bootstrap (refresh) │             │
│                    │  - Push pending_syncs       │             │
│                    └─────────────────────────────┘             │
└──────────────────────────────┬──────────────────────────────────┘
                               │ HTTPS
                               ▼
            ┌──────────────────────────────────────────┐
            │   Study Center Nias Backend (Laravel)    │
            │   /api/admin/prajurit-offline/*          │
            └──────────────────────────────────────────┘
```

### Alur Kerja

1. **Pertama kali / Saat Online**: Panggil `GET /bootstrap` → simpan ke Room/SQLite
2. **Offline**: Scan QR → cari prajurit dari DB lokal → centang item → simpan ke `pending_syncs`
3. **Kembali Online**: Panggil `POST /sync` dengan semua `pending_syncs` → server menyimpan

---

## Autentikasi

Semua endpoint memerlukan token **Sanctum Bearer**.

**Login (dapatkan token):**

```
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@studycenter.com",
  "password": "password"
}
```

**Respons:**
```json
{
  "user": { "id": 10, "name": "Administrator", ... },
  "token": "1|xxxxxxxxxxxxxxxxxxxx"
}
```

**Gunakan token di setiap request:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxx
Accept: application/json
```

**Catatan:** Token ini untuk akun admin/mentor. APK hanya boleh digunakan oleh admin/mentor.

---

## Endpoint

### 1. `GET /api/admin/prajurit-offline/bootstrap`

**Fungsi:** Mengambil semua data awal yang dibutuhkan APK untuk beroperasi offline.  
Simpan respons ini ke database lokal. Refresh setiap kali APK online.

**Request:** Tidak ada body.

**Respons:**
```json
{
  "server_time": "2026-09-27T14:30:00+07:00",
  "server_date": "2026-09-27",
  "config": {
    "form_open_time":  "06:00:00",
    "form_close_time": "23:59:00"
  },
  "total_prajurits": 24,
  "total_items": 4,
  "prajurits": [
    {
      "id":          336,
      "name":        "Andrian Pril Faomasi Waruwu",
      "username":    "andrian-pril-faomasi-waruwu627",
      "kelas":       "5",
      "avatar":      null,
      "qr_payload":  "336"
    }
  ],
  "life_items": [
    { "id": 25, "label": "Tidak Memaki",                "response_type": "boolean" },
    { "id": 26, "label": "Membaca Alkitab di Sekolah",  "response_type": "boolean" },
    { "id": 27, "label": "Jumlah Benar ayat Hafalan",   "response_type": "number"  },
    { "id": 28, "label": "Membaca Alkitab Bersama-sama","response_type": "boolean" }
  ]
}
```

**Field penting:**
| Field | Deskripsi |
|-------|-----------|
| `prajurits[].qr_payload` | String yang ada di dalam QR code — cocokkan saat scan |
| `life_items[].response_type` | `"boolean"` = checkbox, `"number"` = input angka |
| `config.form_open_time` | Jam form jurnal dibuka (tidak diberlakukan di APK offline, hanya informasi) |

**Contoh kode Android (Kotlin):**
```kotlin
suspend fun bootstrap(token: String): BootstrapResponse {
    return apiService.bootstrap("Bearer $token")
}

// Simpan ke Room:
db.prajuritDao().insertAll(response.prajurits)
db.lifeItemDao().insertAll(response.lifeItems)
prefs.saveServerDate(response.serverDate)
```

---

### 2. `POST /api/admin/prajurit-offline/scan`

**Fungsi:** Mencari data prajurit berdasarkan QR code + mengambil centangan hari ini dari server.

> ⚠️ **Untuk operasi offline**: gunakan data bootstrap lokal saja.  
> Endpoint ini hanya berguna saat online untuk mendapatkan centangan real-time dari server.

**Body:**
```json
{
  "user_id": 336,
  "date": "2026-09-27"
}
```

| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| `user_id` | integer | ✓ | ID user dari QR payload |
| `date` | string (YYYY-MM-DD) | ✗ | Default: hari ini |

**Respons sukses (`200`):**
```json
{
  "status": "found",
  "prajurit": {
    "id":       336,
    "name":     "Andrian Pril Faomasi Waruwu",
    "username": "andrian-pril-faomasi-waruwu627",
    "kelas":    "5",
    "avatar":   null
  },
  "date": "2026-09-27",
  "today_checks": [
    { "item_id": 25, "checked": false, "value": null },
    { "item_id": 26, "checked": false, "value": null },
    { "item_id": 27, "checked": false, "value": null },
    { "item_id": 28, "checked": false, "value": null }
  ],
  "has_entry": false
}
```

**Respons tidak ditemukan (`404`):**
```json
{ "status": "not_found" }
```

**Contoh alur scan QR:**
```kotlin
// 1. Decode QR → dapat string "336"
val userId = qrPayload.toIntOrNull() ?: return showError("QR tidak valid")

// 2a. Mode offline: cari dari DB lokal
val prajurit = db.prajuritDao().findById(userId) ?: return showError("Prajurit tidak ditemukan")

// 2b. Mode online: panggil endpoint scan untuk data real-time
val scanResult = apiService.scan(ScanRequest(userId, today))
if (scanResult.status == "not_found") return showError("Bukan prajurit")

// 3. Tampilkan modal jurnal
showJurnalModal(prajurit, scanResult.todayChecks)
```

---

### 3. `POST /api/admin/prajurit-offline/sync`

**Fungsi:** Mengirim batch centangan dari operasi offline ke server. **Endpoint utama untuk sinkronisasi.**

**Body:**
```json
{
  "device_id": "android-uuid-device-123",
  "records": [
    {
      "local_id":           "uuid-v4-lokal-1",
      "user_id":            336,
      "date":               "2026-09-27",
      "item_id":            25,
      "checked":            true,
      "value":              null,
      "offline_created_at": "2026-09-27T10:30:00+07:00"
    },
    {
      "local_id":           "uuid-v4-lokal-2",
      "user_id":            336,
      "date":               "2026-09-27",
      "item_id":            27,
      "checked":            true,
      "value":              3,
      "offline_created_at": "2026-09-27T10:31:00+07:00"
    }
  ]
}
```

| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| `device_id` | string | ✗ | ID unik perangkat (untuk logging) |
| `records[].local_id` | string | ✓ | ID lokal unik (UUID) — dikembalikan di respons untuk tracking |
| `records[].user_id` | integer | ✓ | ID prajurit |
| `records[].date` | string (YYYY-MM-DD) | ✓ | Tanggal presensi |
| `records[].item_id` | integer | ✓ | ID life item |
| `records[].checked` | boolean | ✗ | `true`=centang, `false`=hapus centang. Default `false` |
| `records[].value` | number/null | ✗ | Hanya untuk `response_type: "number"` |
| `records[].offline_created_at` | ISO 8601 | ✗ | Timestamp saat offline. Digunakan untuk conflict resolution |

**Batas:** Maksimal 500 record per request. Untuk lebih banyak, kirim dalam batch terpisah.

**Respons:**
```json
{
  "synced":  15,
  "skipped": 2,
  "failed":  0,
  "results": [
    { "local_id": "uuid-v4-lokal-1", "status": "ok" },
    { "local_id": "uuid-v4-lokal-2", "status": "ok" },
    { "local_id": "uuid-v4-lokal-3", "status": "skipped", "reason": "newer_data_on_server" },
    { "local_id": "uuid-v4-lokal-4", "status": "failed",  "reason": "user_not_prajurit" }
  ]
}
```

**Status record:**
| Status | Deskripsi |
|--------|-----------|
| `ok` | Berhasil disimpan ke server |
| `skipped` | Data server lebih baru, tidak ditimpa |
| `failed` | Gagal — lihat `reason` |

**Reason failed:**
| Reason | Deskripsi |
|--------|-----------|
| `user_not_prajurit` | user_id bukan prajurit aktif |
| `future_date` | Tanggal lebih dari hari ini |
| `server_error` | Error tidak terduga di server |

**Strategi conflict resolution:**
- Jika `offline_created_at` dikirim: server bandingkan timestamp-nya dengan `updated_at` di DB server.
  Jika server lebih baru → record **di-skip** (data server dipertahankan).
- Jika `offline_created_at` tidak dikirim: server **selalu menerima** data APK (overwrite).

**Contoh kode Android — Sync Service:**
```kotlin
class SyncService(
    private val db: AppDatabase,
    private val api: ApiService,
    private val token: String
) {
    suspend fun syncPending() {
        val pending = db.syncQueueDao().getPending() // ambil dari antrian lokal
        if (pending.isEmpty()) return

        // Kirim dalam batch 100
        pending.chunked(100).forEach { batch ->
            val records = batch.map { it.toSyncRecord() }
            val response = api.sync("Bearer $token", SyncRequest(
                deviceId = DeviceInfo.uuid,
                records  = records
            ))

            // Update status di DB lokal berdasarkan response
            response.results.forEach { result ->
                when (result.status) {
                    "ok"      -> db.syncQueueDao().markSynced(result.localId)
                    "skipped" -> db.syncQueueDao().markSkipped(result.localId)
                    "failed"  -> db.syncQueueDao().markFailed(result.localId, result.reason)
                }
            }
        }
    }
}
```

---

### 4. `GET /api/admin/prajurit-offline/today-snapshot`

**Fungsi:** Mengambil status centangan SEMUA prajurit untuk satu tanggal.  
Gunakan setelah sync untuk memperbarui tampilan daftar di APK.

**Query params:**
| Param | Tipe | Deskripsi |
|-------|------|-----------|
| `date` | string (YYYY-MM-DD) | Tanggal yang ditampilkan. Default: hari ini |

**Contoh request:**
```
GET /api/admin/prajurit-offline/today-snapshot?date=2026-09-27
Authorization: Bearer ...
```

**Respons:**
```json
{
  "date":  "2026-09-27",
  "total": 24,
  "active": 3,
  "prajurits": [
    {
      "user_id":      336,
      "name":         "Andrian Pril Faomasi Waruwu",
      "has_activity": true,
      "checked_ids":  [25, 26, 27],
      "number_values": [
        { "item_id": 27, "value": 3 }
      ]
    },
    {
      "user_id":      337,
      "name":         "Ayu Siska Ndraha",
      "has_activity": false,
      "checked_ids":  [],
      "number_values": []
    }
  ]
}
```

---

### 5. `POST /api/admin/prajurit-offline/save`

**Fungsi:** Menyimpan semua centangan 1 prajurit pada 1 tanggal secara langsung (saat online).  
Lebih efisien daripada `/sync` jika device sedang online saat mengisi presensi.

**Body:**
```json
{
  "user_id": 336,
  "date": "2026-09-27",
  "checks": [
    { "item_id": 25, "checked": true,  "value": null },
    { "item_id": 26, "checked": false, "value": null },
    { "item_id": 27, "checked": true,  "value": 3    },
    { "item_id": 28, "checked": true,  "value": null }
  ]
}
```

| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| `user_id` | integer | ✓ | ID prajurit |
| `date` | string (YYYY-MM-DD) | ✓ | Tanggal presensi (tidak boleh masa depan) |
| `checks` | array | ✓ | Minimal 1 item |
| `checks[].item_id` | integer | ✓ | ID life item |
| `checks[].checked` | boolean | ✗ | Default `false` |
| `checks[].value` | number/null | ✗ | Hanya untuk item `response_type: "number"` |

**Respons sukses (`200`):**
```json
{
  "ok": true,
  "date": "2026-09-27",
  "user_id": 336
}
```

**Respons error tanggal masa depan (`422`):**
```json
{
  "ok": false,
  "message": "Tanggal masa depan tidak diizinkan."
}
```

---

### 6. `GET /api/admin/prajurit-offline/history/{userId}`

**Fungsi:** Mengambil riwayat centangan prajurit tertentu dalam range tanggal.

**Path param:** `userId` = ID user prajurit  
**Query params:**
| Param | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| `from` | string (YYYY-MM-DD) | ✓ | Tanggal mulai |
| `to` | string (YYYY-MM-DD) | ✓ | Tanggal akhir (≥ from) |

**Contoh request:**
```
GET /api/admin/prajurit-offline/history/336?from=2026-09-20&to=2026-09-27
Authorization: Bearer ...
```

**Respons:**
```json
{
  "user_id": 336,
  "name": "Andrian Pril Faomasi Waruwu",
  "from": "2026-09-20",
  "to":   "2026-09-27",
  "days": [
    { "date": "2026-09-20", "has_activity": false, "checked_ids": [], "number_values": [] },
    { "date": "2026-09-21", "has_activity": false, "checked_ids": [], "number_values": [] },
    { "date": "2026-09-25", "has_activity": true,  "checked_ids": [26, 27, 28], "number_values": [{"item_id":27,"value":2}] },
    { "date": "2026-09-27", "has_activity": true,  "checked_ids": [25, 26, 27], "number_values": [{"item_id":27,"value":3}] }
  ]
}
```

---

## Desain Database Lokal Android (Room/SQLite)

```sql
-- Tabel prajurit (diisi dari bootstrap)
CREATE TABLE prajurits (
    id          INTEGER PRIMARY KEY,
    name        TEXT NOT NULL,
    username    TEXT,
    kelas       TEXT,
    avatar_url  TEXT,
    qr_payload  TEXT NOT NULL  -- nilai yang ada di dalam QR code
);

-- Tabel life items (diisi dari bootstrap)
CREATE TABLE life_items (
    id            INTEGER PRIMARY KEY,
    label         TEXT NOT NULL,
    response_type TEXT NOT NULL  -- 'boolean' | 'number'
);

-- Tabel centangan (data offline)
CREATE TABLE attendance_records (
    local_id           TEXT PRIMARY KEY,  -- UUID lokal
    user_id            INTEGER NOT NULL,
    date               TEXT NOT NULL,     -- 'YYYY-MM-DD'
    item_id            INTEGER NOT NULL,
    checked            INTEGER NOT NULL DEFAULT 0,  -- 0=false, 1=true
    value              REAL,
    offline_created_at TEXT NOT NULL,     -- ISO 8601
    sync_status        TEXT NOT NULL DEFAULT 'pending', -- 'pending'|'synced'|'skipped'|'failed'
    sync_error         TEXT,
    synced_at          TEXT,
    FOREIGN KEY(user_id)  REFERENCES prajurits(id),
    FOREIGN KEY(item_id)  REFERENCES life_items(id)
);

-- Tabel config
CREATE TABLE app_config (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL
);
-- Contoh: INSERT INTO app_config VALUES ('form_open_time', '06:00:00');
--         INSERT INTO app_config VALUES ('last_bootstrap', '2026-09-27T14:30:00+07:00');
```

---

## QR Code Format

QR code pada kartu prajurit berisi **integer user_id sebagai string**.

Contoh: User ID = `336` → QR code berisi string `"336"`

**Cara decode di Android:**
```kotlin
fun onQrScanned(rawQrValue: String) {
    val userId = rawQrValue.trim().toIntOrNull()
    if (userId == null) {
        showError("QR code tidak valid")
        return
    }
    
    // Cari prajurit dari DB lokal (offline-first)
    val prajurit = db.prajuritDao().findByQrPayload(rawQrValue)
    if (prajurit == null) {
        showError("Prajurit tidak ditemukan di data lokal. Lakukan bootstrap terlebih dahulu.")
        return
    }
    
    showJurnalModal(prajurit)
}
```

---

## Lifecycle Sinkronisasi

```
App Start
    │
    ├─ Cek koneksi internet ─────────────────────────────────────────────┐
    │                                                                     │
    │  Online                                                             │
    │   ├── 1. GET /bootstrap → update DB lokal (jika data berubah)     │
    │   ├── 2. POST /sync     → push semua pending_syncs                │
    │   └── 3. GET /today-snapshot → refresh tampilan daftar            │
    │                                                                     │
    │  Offline                                                            │
    │   └── Lanjut dari data lokal terakhir                              │
    │                                                                     │
    └─────────────────────────────────────────────────────────────────────┘
    
Saat Scan QR (selalu bisa offline)
    │
    ├── Decode QR → user_id
    ├── Query DB lokal → prajurit data
    ├── Tampilkan modal jurnal
    │     ├── Checkbox tiap life_item
    │     └── Input number untuk item tipe 'number'
    │
    └── Submit (centang berubah)
          │
          ├── Simpan ke attendance_records (sync_status='pending')
          │
          └── Cek internet
                ├── Online: langsung POST /save → tandai 'synced'
                └── Offline: tunggu sync service berikutnya
```

---

## Konfigurasi APK

Tambahkan di `BuildConfig` atau `local.properties`:

```
# production
BASE_URL=https://studycenter.nanoprojectdevindonesia.com/api
BOOTSTRAP_INTERVAL_HOURS=6     # refresh bootstrap setiap 6 jam saat online
SYNC_RETRY_MAX=3                # maksimal percobaan sync ulang
SYNC_BATCH_SIZE=100             # jumlah record per batch sync
```

---

## Error Handling

### HTTP Status Codes

| Code | Deskripsi |
|------|-----------|
| `200` | Berhasil |
| `401` | Token tidak valid / expired — login ulang |
| `403` | Tidak punya akses (bukan admin/mentor) |
| `404` | Resource tidak ditemukan |
| `422` | Validasi gagal — lihat `errors` di respons |
| `429` | Rate limit — tunggu sebelum retry |
| `500` | Server error — log dan coba lagi nanti |

### Contoh respons error validasi (`422`):
```json
{
  "message": "The user id field is required.",
  "errors": {
    "user_id": ["The user id field is required."]
  }
}
```

---

## Contoh Implementasi Lengkap (Kotlin)

### Data Classes

```kotlin
data class BootstrapResponse(
    @JsonProperty("server_time")    val serverTime: String,
    @JsonProperty("server_date")    val serverDate: String,
    @JsonProperty("config")         val config: ConfigData,
    @JsonProperty("prajurits")      val prajurits: List<PrajuritData>,
    @JsonProperty("life_items")     val lifeItems: List<LifeItemData>,
    @JsonProperty("total_prajurits") val totalPrajurits: Int,
    @JsonProperty("total_items")    val totalItems: Int
)

data class PrajuritData(
    val id: Int,
    val name: String,
    val username: String?,
    val kelas: String?,
    val avatar: String?,
    @JsonProperty("qr_payload") val qrPayload: String
)

data class LifeItemData(
    val id: Int,
    val label: String,
    @JsonProperty("response_type") val responseType: String  // "boolean" | "number"
)

data class SyncRequest(
    @JsonProperty("device_id") val deviceId: String,
    val records: List<SyncRecord>
)

data class SyncRecord(
    @JsonProperty("local_id")           val localId: String,
    @JsonProperty("user_id")            val userId: Int,
    val date: String,
    @JsonProperty("item_id")            val itemId: Int,
    val checked: Boolean,
    val value: Double?,
    @JsonProperty("offline_created_at") val offlineCreatedAt: String
)

data class SyncResponse(
    val synced: Int,
    val skipped: Int,
    val failed: Int,
    val results: List<SyncResult>
)

data class SyncResult(
    @JsonProperty("local_id") val localId: String,
    val status: String,   // "ok" | "skipped" | "failed"
    val reason: String?
)
```

### Retrofit Interface

```kotlin
interface PrajuritOfflineApi {

    @GET("admin/prajurit-offline/bootstrap")
    suspend fun bootstrap(
        @Header("Authorization") token: String
    ): BootstrapResponse

    @POST("admin/prajurit-offline/scan")
    suspend fun scan(
        @Header("Authorization") token: String,
        @Body body: ScanRequest
    ): ScanResponse

    @POST("admin/prajurit-offline/sync")
    suspend fun sync(
        @Header("Authorization") token: String,
        @Body body: SyncRequest
    ): SyncResponse

    @GET("admin/prajurit-offline/today-snapshot")
    suspend fun todaySnapshot(
        @Header("Authorization") token: String,
        @Query("date") date: String? = null
    ): TodaySnapshotResponse

    @POST("admin/prajurit-offline/save")
    suspend fun save(
        @Header("Authorization") token: String,
        @Body body: SaveRequest
    ): SaveResponse

    @GET("admin/prajurit-offline/history/{userId}")
    suspend fun history(
        @Header("Authorization") token: String,
        @Path("userId") userId: Int,
        @Query("from") from: String,
        @Query("to") to: String
    ): HistoryResponse
}
```

---

## Kontak & Support

Jika ada pertanyaan terkait API ini, hubungi developer backend Study Center Nias.

**Repository:** `/var/www/study-center-nias`  
**Controller:** `app/Http/Controllers/Api/Admin/PrajuritOfflineApiController.php`  
**Routes:** `routes/api.php` (prefix: `admin/prajurit-offline`)
