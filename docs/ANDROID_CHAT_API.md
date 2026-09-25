# 📱 Panduan Pengembang Android — Fitur Chat
## Study Center Nias · Dokumen Teknis Lengkap

> **Versi:** 1.0.0 · **Tanggal:** 25 September 2026
> **Penulis:** Tim Backend Study Center Nias
> **File ini adalah sumber tunggal kebenaran (single source of truth) untuk integrasi fitur Chat di Android.**

---

## Daftar Isi

1. [Gambaran Arsitektur](#1-gambaran-arsitektur)
2. [Autentikasi](#2-autentikasi)
3. [Real-time WebSocket (Reverb)](#3-real-time-websocket-reverb)
4. [REST API — Chat Endpoints](#4-rest-api--chat-endpoints)
5. [Format Objek Data](#5-format-objek-data)
6. [Upload File & Foto](#6-upload-file--foto)
7. [Alur Lengkap Per Fitur](#7-alur-lengkap-per-fitur)
8. [Error Handling](#8-error-handling)
9. [Checklist Integrasi](#9-checklist-integrasi)
10. [Tips Implementasi Android](#10-tips-implementasi-android)

---

## 1. Gambaran Arsitektur

```
┌─────────────────────────────────────────────────────────────────┐
│  ANDROID APP                                                    │
│                                                                 │
│  ┌──────────────┐      REST API       ┌──────────────────────┐ │
│  │ ChatRepository├───────────────────►│ Laravel API          │ │
│  │ (Retrofit2)   │◄───────────────────│ /api/chat/*          │ │
│  └──────┬───────┘                     └──────────────────────┘ │
│         │                                                       │
│  ┌──────▼────────┐    WebSocket WSS   ┌──────────────────────┐ │
│  │ ReverbSocket  ├───────────────────►│ Laravel Reverb       │ │
│  │ (OkHttp WS)   │◄───────────────────│ ws://domain/app/*    │ │
│  └───────────────┘                     └──────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

**Stack Server:**
- **Backend:** Laravel 13 (PHP 8.4)
- **WebSocket:** Laravel Reverb (self-hosted, kompatibel Pusher Protocol)
- **Auth:** Laravel Sanctum (Bearer Token)
- **Storage:** Local disk → `/storage/chat/images/`, `/storage/chat/files/`
- **Database:** MySQL 8

**Base URL Production:**
```
https://studycenter.nanoprojectdevindonesia.com
```

---

## 2. Autentikasi

Semua endpoint chat memerlukan autentikasi via **Bearer Token** (Laravel Sanctum).

### Login & Dapatkan Token

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response sukses (200):**
```json
{
  "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ",
  "user": {
    "id": 42,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "username": "budisantoso",
    "avatar": null
  }
}
```

### Gunakan Token di Semua Request

```
Authorization: Bearer 1|aBcDeFgHiJkLmNoPqRsTuVwXyZ
Accept: application/json
```

### Retrofit2 Setup (Kotlin)

```kotlin
// ApiService.kt
interface ChatApiService {
    // Conversations
    @GET("api/chat/conversations")
    suspend fun getConversations(): Response<ConversationsResponse>

    @GET("api/chat/conversations/{id}/messages")
    suspend fun getMessages(
        @Path("id") convId: Long,
        @Query("page") page: Int = 1
    ): Response<MessagesResponse>

    @POST("api/chat/private/{userId}")
    suspend fun startPrivateChat(@Path("userId") userId: Long): Response<StartChatResponse>

    @POST("api/chat/group")
    @Headers("Content-Type: application/json")
    suspend fun createGroup(@Body body: CreateGroupRequest): Response<StartChatResponse>

    @Multipart
    @POST("api/chat/conversations/{id}/messages")
    suspend fun sendMessage(
        @Path("id") convId: Long,
        @Part("type") type: RequestBody,
        @Part("body") body: RequestBody?,
        @Part("reply_to_id") replyToId: RequestBody?,
        @Part attachment: MultipartBody.Part?
    ): Response<SendMessageResponse>

    @DELETE("api/chat/messages/{id}")
    suspend fun deleteMessage(@Path("id") msgId: Long): Response<OkResponse>

    @POST("api/chat/conversations/{id}/read")
    suspend fun markRead(@Path("id") convId: Long): Response<OkResponse>

    @GET("api/chat/unread-count")
    suspend fun getUnreadCount(): Response<UnreadCountResponse>
}
```

---

## 3. Real-time WebSocket (Reverb)

Gunakan **Pusher Android SDK** karena Reverb 100% kompatibel dengan protokol Pusher.

### Dependency (build.gradle)

```gradle
implementation 'com.pusher:pusher-java-client:2.4.4'
// atau via Maven: com.pusher:pusher-java-client
```

### Konfigurasi Koneksi

```kotlin
// ReverbConfig.kt
object ReverbConfig {
    const val APP_KEY    = "scnias_reverb_key_2026"
    const val HOST       = "studycenter.nanoprojectdevindonesia.com"
    const val PORT       = 443
    const val USE_TLS    = true
    const val AUTH_URL   = "https://studycenter.nanoprojectdevindonesia.com/broadcasting/auth"
}
```

### Setup Pusher Client (Kotlin)

```kotlin
// ChatSocket.kt
class ChatSocket(private val authToken: String) {

    private var pusher: Pusher? = null

    fun connect(onConnected: () -> Unit = {}, onError: (String) -> Unit = {}) {
        val options = PusherOptions().apply {
            setHost(ReverbConfig.HOST)
            setWsPort(ReverbConfig.PORT)
            setWssPort(ReverbConfig.PORT)
            isUseTLS = ReverbConfig.USE_TLS
            setChannelAuthorizer { channelName, socketId, callback ->
                // Otorisasi private channel ke backend
                val client = OkHttpClient()
                val body   = FormBody.Builder()
                    .add("socket_id", socketId)
                    .add("channel_name", channelName)
                    .build()
                val request = Request.Builder()
                    .url(ReverbConfig.AUTH_URL)
                    .post(body)
                    .addHeader("Authorization", "Bearer $authToken")
                    .addHeader("Accept", "application/json")
                    .build()
                try {
                    val response = client.newCall(request).execute()
                    if (response.isSuccessful) {
                        callback.onSuccess(response.body!!.string())
                    } else {
                        callback.onFailure(Exception("Auth failed: ${response.code}"))
                    }
                } catch (e: Exception) {
                    callback.onFailure(e)
                }
            }
        }

        pusher = Pusher(ReverbConfig.APP_KEY, options)
        pusher!!.connect(object : ConnectionEventListener {
            override fun onConnectionStateChange(change: ConnectionStateChange) {
                if (change.currentState == ConnectionState.CONNECTED) onConnected()
            }
            override fun onError(msg: String, code: String?, e: Exception?) {
                onError(msg)
            }
        }, ConnectionState.ALL)
    }

    /**
     * Subscribe ke channel private conversation
     * @param convId  ID conversation
     * @param onMessage  callback saat ada pesan baru
     * @param onRead     callback saat pesan dibaca orang lain
     */
    fun subscribeConversation(
        convId: Long,
        onMessage: (MessageDto) -> Unit,
        onRead: (Int, String) -> Unit = { _, _ -> }
    ): Channel {
        val channel = pusher!!.subscribePrivate("private-conversation.$convId")

        val gson = com.google.gson.GsonBuilder()
            .setFieldNamingPolicy(com.google.gson.FieldNamingPolicy.LOWER_CASE_WITH_UNDERSCORES)
            .create()

        channel.bind("MessageSent") { event ->
            val msg = gson.fromJson(event.data, MessageDto::class.java)
            onMessage(msg)
        }

        channel.bind("MessageRead") { event ->
            val data = JSONObject(event.data)
            onRead(data.getInt("user_id"), data.getString("read_at"))
        }

        return channel
    }

    /**
     * Kirim typing indicator (client event — tidak butuh PHP event)
     */
    fun sendTyping(channel: Channel, userId: Int, userName: String) {
        if (channel is PrivateChannel) {
            val data = JSONObject().apply {
                put("user_id", userId)
                put("name", userName)
            }
            (channel as PrivateChannel).trigger("client-typing", data.toString())
        }
    }

    fun disconnect() {
        pusher?.disconnect()
    }
}
```

### Event yang Diterima dari Server

#### `MessageSent` — Pesan Baru

```json
{
  "id": 42,
  "conversation_id": 7,
  "user_id": 15,
  "user_name": "Budi Santoso",
  "type": "text",
  "body": "Halo semua!",
  "attachment_url": null,
  "attachment_name": null,
  "attachment_mime": null,
  "attachment_size": null,
  "thumbnail_url": null,
  "reply_to": null,
  "deleted_at": null,
  "created_at": "2026-09-25T10:30:00.000000Z"
}
```

> ⚠️ **Event name di client:** `MessageSent` (bukan `.message.sent`).
> Format Reverb: event name yang kamu bind adalah nama class PHP → `MessageSent`.

#### `MessageRead` — Tanda Dibaca

```json
{
  "user_id": 15,
  "read_at": "2026-09-25T10:31:00.000000Z"
}
```

#### `client-typing` — Sedang Mengetik (client event)

```json
{
  "user_id": 15,
  "name": "Budi Santoso"
}
```

> ⚠️ Client events (`client-*`) hanya dikirim ke anggota lain dalam channel, TIDAK disimpan ke server.

---

## 4. REST API — Chat Endpoints

**Base:** `https://studycenter.nanoprojectdevindonesia.com`
**Prefix:** `/api/chat`
**Auth:** `Authorization: Bearer {token}` (semua endpoint)

---

### 4.1 GET `/api/chat/conversations`

Daftar semua conversation user (pribadi & group).

**Request:**
```http
GET /api/chat/conversations
Authorization: Bearer {token}
Accept: application/json
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 7,
      "type": "private",
      "name": null,
      "avatar": null,
      "display_name": "Rina Hutabarat",
      "unread_count": 3,
      "last_message_at": "2026-09-25T10:30:00.000000Z",
      "last_message": {
        "id": 42,
        "type": "text",
        "body": "Oke siap!",
        "user": { "id": 15, "name": "Rina Hutabarat" },
        "created_at": "2026-09-25T10:30:00.000000Z"
      },
      "participants": [
        { "id": 10, "name": "Budi Santoso" },
        { "id": 15, "name": "Rina Hutabarat" }
      ]
    },
    {
      "id": 8,
      "type": "group",
      "name": "Tim Alpha",
      "display_name": "Tim Alpha",
      "unread_count": 0,
      "last_message_at": "2026-09-25T09:00:00.000000Z",
      "last_message": { ... }
    }
  ]
}
```

---

### 4.2 GET `/api/chat/conversations/{id}/messages`

Load pesan dalam conversation (paginated, 40 per halaman, terbaru dulu).

**Request:**
```http
GET /api/chat/conversations/7/messages?page=1
Authorization: Bearer {token}
Accept: application/json
```

**Response (200):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 42,
      "conversation_id": 7,
      "user_id": 15,
      "reply_to_id": null,
      "type": "text",
      "body": "Halo!",
      "attachment_path": null,
      "attachment_name": null,
      "attachment_mime": null,
      "attachment_size": null,
      "thumbnail_path": null,
      "deleted_at": null,
      "created_at": "2026-09-25T10:30:00.000000Z",
      "updated_at": "2026-09-25T10:30:00.000000Z",
      "attachment_url": null,
      "thumbnail_url": null,
      "user": { "id": 15, "name": "Rina Hutabarat" },
      "reply_to": null
    },
    {
      "id": 41,
      "conversation_id": 7,
      "user_id": 10,
      "type": "image",
      "body": null,
      "attachment_name": "foto_kegiatan.jpg",
      "attachment_mime": "image/jpeg",
      "attachment_size": 524288,
      "deleted_at": null,
      "created_at": "2026-09-25T10:25:00.000000Z",
      "attachment_url": "https://studycenter.nanoprojectdevindonesia.com/storage/chat/images/uuid.jpg",
      "thumbnail_url": "https://studycenter.nanoprojectdevindonesia.com/storage/chat/thumbs/uuid_thumb.jpg"
    }
  ],
  "last_page": 5,
  "per_page": 40,
  "total": 187,
  "prev_page_url": null,
  "next_page_url": "https://.../api/chat/conversations/7/messages?page=2"
}
```

> **Infinite scroll:** Muat `page=1` pertama (pesan terbaru). Untuk load lebih lama, tambah `page=2`, `page=3`, dst. Tampilkan dalam urutan terbalik di UI.

---

### 4.3 POST `/api/chat/private/{userId}`

Mulai atau buka kembali private chat dengan user tertentu.

**Request:**
```http
POST /api/chat/private/15
Authorization: Bearer {token}
Accept: application/json
```

**Response (200):**
```json
{
  "conversation_id": 7
}
```

> Jika conversation sudah ada, hanya mengembalikan `conversation_id` existing. Tidak membuat duplikat.

---

### 4.4 POST `/api/chat/group`

Buat group chat baru.

**Request:**
```http
POST /api/chat/group
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json

{
  "name": "Tim Pengajian",
  "user_ids": [2, 5, 8, 12]
}
```

**Validasi:**
- `name`: wajib, max 100 karakter
- `user_ids`: wajib, array, min 1 user, setiap ID harus ada di tabel users

**Response (200):**
```json
{
  "conversation_id": 9
}
```

---

### 4.5 POST `/api/chat/conversations/{id}/messages`

Kirim pesan (teks, gambar, atau file).

**Request — Teks:**
```http
POST /api/chat/conversations/7/messages
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data

type=text
body=Halo ini pesan teks
reply_to_id=41   (optional)
```

**Request — Gambar:**
```http
POST /api/chat/conversations/7/messages
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data

type=image
attachment=@/path/to/photo.jpg   (file binary)
body=Caption foto (opsional)
```

**Request — File/Dokumen:**
```http
POST /api/chat/conversations/7/messages
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data

type=file
attachment=@/path/to/document.pdf   (file binary)
```

**Validasi:**
- `type`: wajib, salah satu `text | image | file`
- `body`: wajib jika `type=text`, optional untuk image/file (caption)
- `attachment`: wajib untuk `type=image` atau `type=file`
- `attachment` max size: **50 MB**
- `reply_to_id`: optional, ID pesan yang dibalas

**Format yang didukung:**
```
Gambar: image/jpeg, image/png, image/gif, image/webp
Dokumen: application/pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx
Arsip: .zip, .rar
Teks: .txt
Media: .mp4, .mp3
```

**Response (201):**
```json
{
  "message": {
    "id": 43,
    "conversation_id": 7,
    "user_id": 10,
    "type": "text",
    "body": "Halo ini pesan teks",
    "attachment_url": null,
    "thumbnail_url": null,
    "reply_to": null,
    "created_at": "2026-09-25T10:35:00.000000Z",
    "user": { "id": 10, "name": "Budi Santoso" }
  }
}
```

> ⚠️ **Thumbnail gambar:** dibuat secara async oleh queue worker. `thumbnail_url` mungkin `null` saat pertama kali diterima. Setelah beberapa detik, thumbnail sudah tersedia. Tampilkan gambar asli (`attachment_url`) jika thumbnail belum ada.

---

### 4.6 DELETE `/api/chat/messages/{id}`

Hapus pesan milik sendiri (soft delete — pesan tetap ada sebagai "pesan dihapus").

**Request:**
```http
DELETE /api/chat/messages/43
Authorization: Bearer {token}
Accept: application/json
```

**Response (200):**
```json
{
  "ok": true
}
```

**Error (403):** jika bukan pemilik pesan.

---

### 4.7 POST `/api/chat/conversations/{id}/read`

Tandai semua pesan di conversation sebagai sudah dibaca.

**Request:**
```http
POST /api/chat/conversations/7/read
Authorization: Bearer {token}
Accept: application/json
```

**Response (200):**
```json
{
  "ok": true
}
```

> Panggil ini setiap kali user **membuka** conversation, dan setiap kali **menerima pesan baru** via WebSocket saat conversation sedang terbuka.

---

### 4.8 GET `/api/chat/unread-count`

Jumlah total pesan belum dibaca dari semua conversation.

**Request:**
```http
GET /api/chat/unread-count
Authorization: Bearer {token}
Accept: application/json
```

**Response (200):**
```json
{
  "count": 12
}
```

> Gunakan ini untuk menampilkan badge di tab/icon Chat di bottom navigation bar Android.

---

## 5. Format Objek Data

### ConversationDto

```kotlin
data class ConversationDto(
    val id: Long,
    val type: String,          // "private" | "group"
    val name: String?,
    val avatar: String?,
    val displayName: String,   // nama lawan bicara (private) atau nama group
    val unreadCount: Int,
    val lastMessageAt: String?,
    val lastMessage: MessageDto?,
    val participants: List<UserDto>
)
```

### MessageDto

```kotlin
data class MessageDto(
    val id: Long,
    val conversationId: Long,
    val userId: Long,
    val replyToId: Long?,
    val type: String,           // "text" | "image" | "file" | "system"
    val body: String?,
    val attachmentUrl: String?,
    val attachmentName: String?,
    val attachmentMime: String?,
    val attachmentSize: Long?,
    val thumbnailUrl: String?,
    val replyTo: ReplyToDto?,
    val deletedAt: String?,     // null = belum dihapus
    val createdAt: String,
    val user: UserDto?
)

data class ReplyToDto(
    val id: Long,
    val body: String?,
    val userId: Long
)
```

### UserDto

```kotlin
data class UserDto(
    val id: Long,
    val name: String,
    val avatar: String?
)
```

### Gson field mapping

Field dari server menggunakan `snake_case`. Gunakan GsonBuilder dengan `FieldNamingPolicy.LOWER_CASE_WITH_UNDERSCORES`:

```kotlin
val gson = GsonBuilder()
    .setFieldNamingPolicy(FieldNamingPolicy.LOWER_CASE_WITH_UNDERSCORES)
    .create()
```

---

## 6. Upload File & Foto

### Contoh upload foto (Kotlin + Retrofit2 + OkHttp)

```kotlin
suspend fun sendImageMessage(
    context: Context,
    convId: Long,
    uri: Uri,
    caption: String? = null,
    replyToId: Long? = null
): Result<SendMessageResponse> {
    return withContext(Dispatchers.IO) {
        try {
            val inputStream = context.contentResolver.openInputStream(uri)!!
            val mimeType    = context.contentResolver.getType(uri) ?: "image/jpeg"
            val ext         = MimeTypeMap.getSingleton()
                .getExtensionFromMimeType(mimeType) ?: "jpg"
            val fileName    = "photo_${System.currentTimeMillis()}.$ext"
            val bytes       = inputStream.readBytes()

            val requestFile   = bytes.toRequestBody(mimeType.toMediaTypeOrNull())
            val attachmentPart = MultipartBody.Part.createFormData("attachment", fileName, requestFile)
            val typePart      = "image".toRequestBody("text/plain".toMediaTypeOrNull())
            val bodyPart      = caption?.toRequestBody("text/plain".toMediaTypeOrNull())
            val replyPart     = replyToId?.toString()?.toRequestBody("text/plain".toMediaTypeOrNull())

            val response = chatApiService.sendMessage(convId, typePart, bodyPart, replyPart, attachmentPart)
            if (response.isSuccessful) Result.success(response.body()!!)
            else Result.failure(Exception("Upload gagal: ${response.code()}"))
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}
```

### Batas Upload

| Tipe | Batas |
|---|---|
| Gambar (image/*) | 50 MB |
| Dokumen (PDF, Office) | 50 MB |
| Arsip (ZIP, RAR) | 50 MB |
| Video (MP4) | 50 MB |

---

## 7. Alur Lengkap Per Fitur

### 7.1 Buka Halaman Chat

```
1. GET /api/chat/conversations         → Tampilkan daftar
2. Hubungkan WebSocket (Pusher client)
3. Untuk setiap conv, tampilkan badge jika unread_count > 0
```

### 7.2 Buka Conversation

```
1. GET /api/chat/conversations/{id}/messages?page=1
   → Tampilkan pesan (urut lama → baru dari bawah)
2. POST /api/chat/conversations/{id}/read
   → Tandai semua sudah dibaca
3. Subscribe WebSocket: "private-conversation.{id}"
   → Listen event "MessageSent", "MessageRead", "client-typing"
```

### 7.3 Kirim Pesan Teks

```
1. POST /api/chat/conversations/{id}/messages
   body: { type: "text", body: "..." }
2. Tampilkan pesan secara optimistic di UI
3. Saat response 201 kembali, update ID pesan
4. User lain menerima via WebSocket event "MessageSent" secara real-time
```

### 7.4 Kirim Foto

```
1. User pilih foto dari galeri (Intent.ACTION_PICK)
2. Compress gambar jika > 5 MB (opsional, tapi disarankan)
3. POST /api/chat/conversations/{id}/messages (multipart)
   form: type=image, attachment=<file>
4. Tampilkan thumbnail sementara dari file lokal
5. Update ke thumbnail_url server setelah response 201
6. thumbnail_url mungkin null awalnya → fallback ke attachment_url
```

### 7.5 Kirim File/Dokumen

```
1. User pilih file (Intent.ACTION_OPEN_DOCUMENT)
2. POST /api/chat/conversations/{id}/messages (multipart)
   form: type=file, attachment=<file>
3. Tampilkan nama file + ukuran + ikon download
```

### 7.6 Infinite Scroll (Load Pesan Lebih Lama)

```
1. Halaman 1 = pesan terbaru
2. Saat user scroll ke atas → GET ...?page=2, page=3, dst
3. Prepend pesan lebih lama ke atas RecyclerView
4. Stop jika page >= last_page
```

### 7.7 Typing Indicator

```
1. User mulai ketik → kirim client event "client-typing"
   via Pusher channel.trigger("client-typing", data)
2. Tampilkan "Nama sedang mengetik…" selama 2.5 detik
3. Hilangkan jika tidak ada event baru selama 2.5 detik
```

### 7.8 Mulai Private Chat dari Profile User

```
1. POST /api/chat/private/{userId}
   → Server return { conversation_id: 7 }
2. Navigasi ke layar chat dengan conversation_id tersebut
3. Load messages, subscribe WebSocket
```

### 7.9 Hapus Pesan

```
1. User long-press pesan → tampilkan opsi "Hapus"
2. DELETE /api/chat/messages/{id}
3. Update UI: ganti teks jadi "🚫 Pesan dihapus"
   (deleted_at tidak null, body disembunyikan)
```

---

## 8. Error Handling

### HTTP Status Code

| Code | Arti | Tindakan |
|---|---|---|
| `200` | OK | Sukses |
| `201` | Created | Pesan berhasil dikirim |
| `302` | Redirect | Token tidak valid → redirect login |
| `401` | Unauthorized | Token expired → refresh/logout |
| `403` | Forbidden | Bukan peserta conversation atau bukan pemilik pesan |
| `404` | Not Found | Conversation/pesan tidak ditemukan |
| `422` | Validation Error | Input tidak valid |
| `429` | Rate Limited | Terlalu banyak request |
| `500` | Server Error | Hubungi tim backend |

### Format Error Response

```json
{
  "message": "Deskripsi error",
  "errors": {
    "body": ["Pesan tidak boleh kosong"],
    "attachment": ["File terlalu besar, maksimal 50MB"]
  }
}
```

### Handling di Kotlin

```kotlin
when (response.code()) {
    200, 201 -> handleSuccess(response.body())
    401      -> navigateToLogin()
    403      -> showError("Akses ditolak")
    422      -> {
        val errorBody = response.errorBody()?.string()
        val errors    = Gson().fromJson(errorBody, ApiErrorResponse::class.java)
        showValidationErrors(errors)
    }
    else     -> showError("Terjadi kesalahan (${response.code()})")
}
```

---

## 9. Checklist Integrasi

- [ ] **Auth:** Bearer token dikirim di semua request chat
- [ ] **Conversation list:** Tampilkan dengan unread badge
- [ ] **Message list:** Paginated, infinite scroll ke atas untuk history
- [ ] **Send text:** POST multipart dengan type=text
- [ ] **Send image:** POST multipart, tampilkan preview lokal dulu
- [ ] **Send file:** POST multipart, tampilkan nama + ukuran
- [ ] **Mark read:** Panggil setiap kali conversation dibuka
- [ ] **WebSocket connect:** Setelah berhasil login
- [ ] **Subscribe channel:** Saat membuka conversation
- [ ] **Terima MessageSent:** Append ke RecyclerView jika conversation terbuka
- [ ] **Typing indicator:** Kirim saat user ketik, tampilkan saat terima
- [ ] **Delete message:** Soft delete, tampilkan placeholder teks
- [ ] **Reply:** Kirim reply_to_id, tampilkan quoted message
- [ ] **Unread badge:** Update via GET /unread-count atau dari WS event
- [ ] **Disconnect WS:** Saat app background / logout
- [ ] **Error handling:** Semua HTTP status code ditangani

---

## 10. Tips Implementasi Android

### RecyclerView untuk Chat

Gunakan `LinearLayoutManager` dengan `stackFromEnd = true` agar scroll selalu ke bawah:

```kotlin
val layoutManager = LinearLayoutManager(context).apply {
    stackFromEnd = true
}
recyclerView.layoutManager = layoutManager
recyclerView.scrollToPosition(adapter.itemCount - 1)
```

### Image Loading (Glide)

```kotlin
Glide.with(context)
    .load(message.thumbnailUrl ?: message.attachmentUrl)
    .placeholder(R.drawable.ic_image_loading)
    .error(R.drawable.ic_image_error)
    .centerCrop()
    .into(imageView)
```

### Deteksi Jenis File dari MIME

```kotlin
fun getFileIcon(mime: String?): Int = when {
    mime == null                       -> R.drawable.ic_file_generic
    mime.startsWith("image/")          -> R.drawable.ic_file_image
    mime == "application/pdf"          -> R.drawable.ic_file_pdf
    mime.contains("word")             -> R.drawable.ic_file_word
    mime.contains("excel") || mime.contains("sheet") -> R.drawable.ic_file_excel
    mime.contains("powerpoint") || mime.contains("presentation") -> R.drawable.ic_file_ppt
    mime.contains("zip") || mime.contains("rar") -> R.drawable.ic_file_zip
    else                               -> R.drawable.ic_file_generic
}
```

### Tanggal Pesan (hindari UTC shift)

```kotlin
fun formatMessageTime(isoString: String): String {
    val sdf   = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss", Locale.getDefault())
    sdf.timeZone = TimeZone.getTimeZone("UTC")
    val date  = sdf.parse(isoString) ?: return ""
    val local = Calendar.getInstance().apply { time = date }
    val now   = Calendar.getInstance()

    return if (local.get(Calendar.DATE) == now.get(Calendar.DATE) &&
               local.get(Calendar.MONTH) == now.get(Calendar.MONTH)) {
        SimpleDateFormat("HH:mm", Locale.getDefault()).format(date)
    } else {
        SimpleDateFormat("dd MMM", Locale("id")).format(date)
    }
}
```

### Compress Gambar Sebelum Upload

```kotlin
fun compressImage(uri: Uri, context: Context, maxSizeKB: Int = 1024): ByteArray {
    val bitmap = BitmapFactory.decodeStream(context.contentResolver.openInputStream(uri))
    val stream = ByteArrayOutputStream()
    var quality = 90
    do {
        stream.reset()
        bitmap.compress(Bitmap.CompressFormat.JPEG, quality, stream)
        quality -= 10
    } while (stream.size() / 1024 > maxSizeKB && quality > 10)
    return stream.toByteArray()
}
```

### Battery Optimization — WebSocket Background

```kotlin
// Di onPause/onStop: pertahankan koneksi WS hanya jika ada unread
// Di onDestroy: disconnect WS
// Gunakan WorkManager untuk poll /unread-count setiap 5 menit saat background

class ChatSyncWorker(ctx: Context, params: WorkerParameters) : CoroutineWorker(ctx, params) {
    override suspend fun doWork(): Result {
        val count = chatRepository.getUnreadCount()
        if (count > 0) {
            showNotification("$count pesan belum dibaca")
        }
        return Result.success()
    }
}

// Schedule di Application.onCreate():
PeriodicWorkRequestBuilder<ChatSyncWorker>(5, TimeUnit.MINUTES)
    .build()
    .also { WorkManager.getInstance(this).enqueueUniquePeriodicWork("chat_sync", ..., it) }
```

### Notifikasi Push (Opsional, Fase 2)

Untuk notifikasi saat app tertutup, integrasikan **Firebase Cloud Messaging (FCM)**:
- Daftarkan FCM token ke server via endpoint yang perlu ditambahkan: `POST /api/fcm/register`
- Backend akan kirim FCM notification saat ada pesan baru (belum diimplementasi — scope fase 2)

---

## Changelog

| Tanggal | Versi | Perubahan |
|---|---|---|
| 2026-09-25 | 1.0.0 | Implementasi awal — fitur chat lengkap |

---

*Hubungi tim backend untuk pertanyaan: admin@studycenter.com*
