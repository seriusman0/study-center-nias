# 📱 Panduan Integrasi Chat — Android Developer
## Study Center Nias · Dokumen Teknis Lengkap

> **Versi:** 2.0.0 · **Tanggal Update:** 25 September 2026
> **Penulis:** Tim Backend Study Center Nias
> **Status:** ✅ Production-ready — semua endpoint telah diuji dan aktif di server
>
> ⚠️ Dokumen ini menggantikan versi 1.0.0 sebelumnya. Ada **perubahan breaking** pada konfigurasi WebSocket.

---

## Daftar Isi

1. [Gambaran Arsitektur](#1-gambaran-arsitektur)
2. [Autentikasi (Sanctum)](#2-autentikasi)
3. [Real-time WebSocket — Laravel Reverb](#3-real-time-websocket)
4. [REST API — Semua Endpoint Chat](#4-rest-api)
5. [Format Data (DTO)](#5-format-data-dto)
6. [Upload Foto & File](#6-upload-foto--file)
7. [Alur Lengkap Per Fitur](#7-alur-per-fitur)
8. [Error Handling](#8-error-handling)
9. [Contoh Kode Kotlin Lengkap](#9-contoh-kode-kotlin)
10. [Checklist Integrasi](#10-checklist-integrasi)
11. [FAQ & Pitfalls](#11-faq--pitfalls)

---

## 1. Gambaran Arsitektur

```
ANDROID APP
    │
    ├── [REST] Retrofit2 ──────► POST/GET/DELETE https://studycenter.../api/chat/*
    │                                              (Kirim pesan, load history, dll)
    │
    └── [WS] Pusher SDK ───────► WSS wss://studycenter.../app/{APP_KEY}
                                   (Terima pesan real-time, typing indicator)
```

**Stack Server:**

| Komponen | Teknologi | Keterangan |
|---|---|---|
| Backend | Laravel 13 (PHP 8.4) | REST API + broadcast |
| WebSocket | Laravel Reverb | Self-hosted, protokol Pusher-compatible |
| Auth | Laravel Sanctum | Bearer Token |
| Database | MySQL 8 | conversations, messages |
| Queue | Database driver | Proses thumbnail async |
| Storage | Local disk → HTTPS | Foto & file attachment |

**URL Production:**
```
Base REST : https://studycenter.nanoprojectdevindonesia.com
WebSocket : wss://studycenter.nanoprojectdevindonesia.com/app/{APP_KEY}
```

**WebSocket App Key:**
```
APP_KEY = scnias_reverb_key_2026
```

---

## 2. Autentikasi

Semua endpoint chat memerlukan **Bearer Token** dari Laravel Sanctum.

### 2.1 Login & Dapat Token

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
  "user": {
    "id": 42,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "username": "budisantoso"
  }
}
```

### 2.2 Gunakan Token

Tambahkan di **setiap** request:
```
Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz
Accept: application/json
```

### 2.3 OkHttp Interceptor (Kotlin)

```kotlin
// AuthInterceptor.kt
class AuthInterceptor(private val tokenProvider: () -> String?) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val token = tokenProvider() ?: return chain.proceed(chain.request())
        val request = chain.request().newBuilder()
            .addHeader("Authorization", "Bearer $token")
            .addHeader("Accept", "application/json")
            .build()
        return chain.proceed(request)
    }
}

// Retrofit setup
val retrofit = Retrofit.Builder()
    .baseUrl("https://studycenter.nanoprojectdevindonesia.com/")
    .client(OkHttpClient.Builder()
        .addInterceptor(AuthInterceptor { sessionManager.getToken() })
        .build())
    .addConverterFactory(GsonConverterFactory.create(
        GsonBuilder().setFieldNamingPolicy(FieldNamingPolicy.LOWER_CASE_WITH_UNDERSCORES).create()
    ))
    .build()
```

---

## 3. Real-time WebSocket

Gunakan **Pusher Android SDK** — Laravel Reverb 100% kompatibel dengan protokol Pusher.

### 3.1 Dependency

```gradle
// build.gradle (app)
implementation 'com.pusher:pusher-java-client:2.4.4'
```

### 3.2 Konfigurasi Koneksi

```kotlin
// ReverbConfig.kt
object ReverbConfig {
    const val APP_KEY  = "scnias_reverb_key_2026"
    const val HOST     = "studycenter.nanoprojectdevindonesia.com"
    const val PORT     = 443
    const val USE_TLS  = true
    // Endpoint otorisasi private channel (butuh Bearer token)
    const val AUTH_URL = "https://studycenter.nanoprojectdevindonesia.com/broadcasting/auth"
}
```

### 3.3 Setup & Connect

```kotlin
// ChatSocketManager.kt
class ChatSocketManager(private val authToken: String) {

    private var pusher: Pusher? = null
    private val activeChannels = mutableMapOf<Long, PrivateChannel>()

    fun connect(
        onConnected: (socketId: String) -> Unit = {},
        onError: (msg: String) -> Unit = {}
    ) {
        val options = PusherOptions().apply {
            setHost(ReverbConfig.HOST)
            setWsPort(ReverbConfig.PORT)
            setWssPort(ReverbConfig.PORT)
            isUseTLS = ReverbConfig.USE_TLS

            // Otorisasi private channel via HTTP ke backend
            setChannelAuthorizer { channelName, socketId, callback ->
                Thread {
                    try {
                        val body = FormBody.Builder()
                            .add("socket_id", socketId)
                            .add("channel_name", channelName)
                            .build()
                        val req = Request.Builder()
                            .url(ReverbConfig.AUTH_URL)
                            .post(body)
                            .addHeader("Authorization", "Bearer $authToken")
                            .addHeader("Accept", "application/json")
                            .build()
                        val resp = OkHttpClient().newCall(req).execute()
                        if (resp.isSuccessful) callback.onSuccess(resp.body!!.string())
                        else callback.onFailure(Exception("Auth ${resp.code}"))
                    } catch (e: Exception) {
                        callback.onFailure(e)
                    }
                }.start()
            }
        }

        pusher = Pusher(ReverbConfig.APP_KEY, options)
        pusher!!.connect(object : ConnectionEventListener {
            override fun onConnectionStateChange(change: ConnectionStateChange) {
                if (change.currentState == ConnectionState.CONNECTED) {
                    onConnected(pusher!!.connection.socketId ?: "")
                }
            }
            override fun onError(msg: String, code: String?, e: Exception?) {
                onError(msg)
            }
        }, ConnectionState.ALL)
    }

    /**
     * Subscribe ke private channel conversation.
     * Panggil ini setiap kali user MEMBUKA conversation.
     */
    fun subscribeConversation(
        convId: Long,
        onMessageReceived: (MessageDto) -> Unit,
        onMessageRead: (userId: Long, readAt: String) -> Unit = { _, _ -> },
        onTyping: (userId: Long, name: String) -> Unit = { _, _ -> }
    ) {
        // Unsubscribe channel lama jika ada
        activeChannels[convId]?.unsubscribe()

        val channel = pusher!!.subscribePrivate(
            "private-conversation.$convId",
            object : PrivateChannelEventListener {
                override fun onEvent(event: PusherEvent) { /* handled below */ }
                override fun onSubscriptionSucceeded(channelName: String) {}
                override fun onAuthenticationFailure(msg: String, e: Exception) {}
            }
        )

        // Event: pesan baru masuk
        channel.bind("MessageSent") { event ->
            val msg = Gson().fromJson(event.data, MessageDto::class.java)
            onMessageReceived(msg)
        }

        // Event: pesan sudah dibaca orang lain
        channel.bind("MessageRead") { event ->
            val obj = JSONObject(event.data)
            onMessageRead(obj.getLong("user_id"), obj.getString("read_at"))
        }

        // Client event: typing indicator (tidak butuh server PHP)
        channel.bind("client-typing") { event ->
            val obj = JSONObject(event.data)
            onTyping(obj.getLong("user_id"), obj.getString("name"))
        }

        activeChannels[convId] = channel
    }

    /** Unsubscribe dari channel saat user meninggalkan conversation */
    fun unsubscribeConversation(convId: Long) {
        activeChannels[convId]?.unsubscribe()
        activeChannels.remove(convId)
    }

    /** Kirim typing indicator (client event — gratis, tidak ke server PHP) */
    fun sendTyping(convId: Long, userId: Long, userName: String) {
        val channel = activeChannels[convId] ?: return
        val data = JSONObject().apply {
            put("user_id", userId)
            put("name", userName)
        }
        channel.trigger("client-typing", data.toString())
    }

    fun disconnect() {
        activeChannels.clear()
        pusher?.disconnect()
    }
}
```

### 3.4 Events dari Server

#### `MessageSent` — Ada Pesan Baru

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

#### `MessageRead` — Pesan Sudah Dibaca

```json
{
  "user_id": 15,
  "read_at": "2026-09-25T10:31:00.000000Z"
}
```

#### `client-typing` — Sedang Mengetik

```json
{
  "user_id": 15,
  "name": "Budi Santoso"
}
```

> ⚠️ **Penting:** `client-typing` adalah **client event** (prefix `client-`). Dikirim langsung antar client via Reverb, **tidak disimpan ke database**, **tidak melalui PHP**. Gunakan `channel.trigger("client-typing", data)` bukan `broadcast()`.

---

## 4. REST API

**Base URL:** `https://studycenter.nanoprojectdevindonesia.com`
**Prefix:** `/api/chat`
**Headers wajib semua request:**
```
Authorization: Bearer {token}
Accept: application/json
```

---

### 4.1 GET `/api/chat/conversations`

Ambil semua conversation user (private + group), diurutkan terbaru.

**Response 200:**
```json
{
  "data": [
    {
      "id": 7,
      "type": "private",
      "name": null,
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
      "last_message": { "..." : "..." }
    }
  ]
}
```

**Catatan:**
- `display_name` untuk private = nama lawan bicara. Untuk group = nama group.
- `unread_count` = jumlah pesan belum dibaca di conversation tersebut.

---

### 4.2 GET `/api/chat/conversations/{id}/messages?page=1`

Load pesan dalam conversation. **Paginated, 40 per halaman, terbaru dulu.**

| Parameter | Tipe | Default | Keterangan |
|---|---|---|---|
| `page` | integer | 1 | Halaman (1 = paling baru) |

**Response 200:**
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
      "type": "image",
      "body": "Ini fotonya!",
      "attachment_name": "foto.jpg",
      "attachment_mime": "image/jpeg",
      "attachment_size": 524288,
      "attachment_url": "https://studycenter.nanoprojectdevindonesia.com/storage/chat/images/uuid.jpg",
      "thumbnail_url": "https://studycenter.nanoprojectdevindonesia.com/storage/chat/thumbs/uuid_thumb.jpg",
      "deleted_at": null,
      "created_at": "2026-09-25T10:25:00.000000Z"
    },
    {
      "id": 40,
      "type": "text",
      "body": null,
      "deleted_at": "2026-09-25T09:00:00.000000Z"
    }
  ],
  "last_page": 5,
  "per_page": 40,
  "total": 187,
  "next_page_url": "https://.../api/chat/conversations/7/messages?page=2",
  "prev_page_url": null
}
```

**Cara render:**
1. `page=1` → pesan terbaru → tampilkan dari **bawah** RecyclerView
2. User scroll ke atas → `page=2`, `page=3` → prepend di atas
3. Jika `deleted_at != null` → tampilkan *"Pesan dihapus"*, sembunyikan body
4. `thumbnail_url` bisa `null` saat pertama diterima (thumbnail diproses async). Gunakan `attachment_url` sebagai fallback.

---

### 4.3 POST `/api/chat/private/{userId}`

Mulai atau buka kembali private chat dengan user tertentu. Idempotent — tidak membuat duplikat.

**Response 200:**
```json
{
  "conversation_id": 7
}
```

---

### 4.4 POST `/api/chat/group`

Buat group chat baru.

**Request Body (JSON):**
```json
{
  "name": "Tim Pengajian",
  "user_ids": [2, 5, 8, 12]
}
```

| Field | Validasi |
|---|---|
| `name` | wajib, string, max 100 |
| `user_ids` | wajib, array, min 1 elemen |
| `user_ids.*` | harus ID user yang ada di database |

**Response 200:**
```json
{
  "conversation_id": 9
}
```

---

### 4.5 POST `/api/chat/conversations/{id}/messages`

Kirim pesan. **Selalu gunakan `multipart/form-data`** — bahkan untuk teks biasa.

#### Kirim Teks

```
POST /api/chat/conversations/7/messages
Content-Type: multipart/form-data

type     = text
body     = Halo, apa kabar?
reply_to_id = 41   ← opsional, ID pesan yang dibalas
```

#### Kirim Foto/Gambar

```
POST /api/chat/conversations/7/messages
Content-Type: multipart/form-data

type       = image
attachment = <file binary>
body       = Caption foto ini   ← opsional
```

#### Kirim File/Dokumen

```
POST /api/chat/conversations/7/messages
Content-Type: multipart/form-data

type       = file
attachment = <file binary>
```

| Field | Validasi |
|---|---|
| `type` | wajib: `text` \| `image` \| `file` |
| `body` | wajib jika `type=text`; opsional untuk image/file (caption) |
| `attachment` | wajib untuk type image/file; max **50 MB** |
| `reply_to_id` | opsional; ID pesan yang dibalas |

**Format file yang didukung:**
```
Gambar : image/jpeg, image/png, image/gif, image/webp, image/heic
Dokumen: application/pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx
Arsip  : .zip, .rar, .7z
Media  : .mp4, .mp3, .m4a
Teks   : .txt, .csv
```

**Response 201:**
```json
{
  "message": {
    "id": 43,
    "conversation_id": 7,
    "user_id": 10,
    "type": "image",
    "body": "Caption foto ini",
    "attachment_url": "https://studycenter.nanoprojectdevindonesia.com/storage/chat/images/uuid.jpg",
    "thumbnail_url": null,
    "attachment_name": "foto.jpg",
    "attachment_mime": "image/jpeg",
    "attachment_size": 524288,
    "reply_to": null,
    "deleted_at": null,
    "created_at": "2026-09-25T10:35:00.000000Z",
    "user": { "id": 10, "name": "Budi Santoso" }
  }
}
```

> ⚠️ **`thumbnail_url` awalnya null** untuk gambar. Server memproses thumbnail secara async via queue. Setelah beberapa detik tersedia. Selalu gunakan `attachment_url` sebagai fallback jika `thumbnail_url` null.

---

### 4.6 DELETE `/api/chat/messages/{id}`

Hapus pesan milik sendiri (soft delete — tetap terlihat sebagai "pesan dihapus").

**Response 200:**
```json
{ "ok": true }
```

**Error 403** jika bukan pemilik pesan.

---

### 4.7 POST `/api/chat/conversations/{id}/read`

Tandai semua pesan di conversation sebagai sudah dibaca.

**Kapan dipanggil:**
- Setiap kali user **membuka** conversation
- Setiap kali menerima pesan baru via WebSocket **saat conversation sedang aktif terbuka**

**Response 200:**
```json
{ "ok": true }
```

---

### 4.8 GET `/api/chat/unread-count`

Total pesan belum dibaca dari semua conversation.

**Response 200:**
```json
{ "count": 12 }
```

**Gunakan untuk:** badge di icon Chat di bottom navigation bar.

---

## 5. Format Data (DTO)

### Kotlin Data Classes

```kotlin
// ── Conversation ──────────────────────────────────────────────────────
data class ConversationDto(
    val id: Long,
    val type: String,           // "private" | "group"
    val name: String?,
    val displayName: String,    // nama lawan bicara (private) atau nama grup
    val unreadCount: Int,
    val lastMessageAt: String?,
    val lastMessage: MessageDto?,
    val participants: List<UserDto>
)

// ── Message ───────────────────────────────────────────────────────────
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
    val thumbnailUrl: String?,  // null awalnya, tersedia setelah queue selesai
    val replyTo: ReplyToDto?,
    val deletedAt: String?,     // null = belum dihapus; non-null = tampilkan placeholder
    val createdAt: String,
    val user: UserDto?
)

data class ReplyToDto(
    val id: Long,
    val body: String?,
    val userId: Long
)

data class UserDto(
    val id: Long,
    val name: String
)

// ── Responses ─────────────────────────────────────────────────────────
data class ConversationsResponse(val data: List<ConversationDto>)
data class MessagesResponse(
    val currentPage: Int,
    val data: List<MessageDto>,
    val lastPage: Int,
    val perPage: Int,
    val total: Int,
    val nextPageUrl: String?,
    val prevPageUrl: String?
)
data class StartChatResponse(val conversationId: Long)
data class SendMessageResponse(val message: MessageDto)
data class UnreadCountResponse(val count: Int)
data class OkResponse(val ok: Boolean)
```

### Gson Config

```kotlin
val gson = GsonBuilder()
    .setFieldNamingPolicy(FieldNamingPolicy.LOWER_CASE_WITH_UNDERSCORES)
    .create()
```

---

## 6. Upload Foto & File

### Upload Gambar dari Galeri

```kotlin
suspend fun uploadImage(
    context: Context,
    convId: Long,
    uri: Uri,
    caption: String? = null,
    replyToId: Long? = null
): Result<SendMessageResponse> = withContext(Dispatchers.IO) {
    runCatching {
        val mimeType = context.contentResolver.getType(uri) ?: "image/jpeg"
        val ext      = MimeTypeMap.getSingleton().getExtensionFromMimeType(mimeType) ?: "jpg"
        val bytes    = context.contentResolver.openInputStream(uri)!!.readBytes()
        val fileName = "photo_${System.currentTimeMillis()}.$ext"

        val requestFile   = bytes.toRequestBody(mimeType.toMediaTypeOrNull())
        val filePart      = MultipartBody.Part.createFormData("attachment", fileName, requestFile)
        val typePart      = "image".toRequestBody("text/plain".toMediaTypeOrNull())
        val bodyPart      = caption?.toRequestBody("text/plain".toMediaTypeOrNull())
        val replyPart     = replyToId?.toString()?.toRequestBody("text/plain".toMediaTypeOrNull())

        chatApiService.sendMessage(convId, typePart, bodyPart, replyPart, filePart)
            .let { if (it.isSuccessful) it.body()!! else throw Exception("Upload failed: ${it.code()}") }
    }
}
```

### Retrofit Interface

```kotlin
interface ChatApiService {

    @GET("api/chat/conversations")
    suspend fun getConversations(): Response<ConversationsResponse>

    @GET("api/chat/conversations/{id}/messages")
    suspend fun getMessages(
        @Path("id") convId: Long,
        @Query("page") page: Int = 1
    ): Response<MessagesResponse>

    @POST("api/chat/private/{userId}")
    suspend fun startPrivateChat(
        @Path("userId") userId: Long
    ): Response<StartChatResponse>

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
    suspend fun deleteMessage(@Path("id") id: Long): Response<OkResponse>

    @POST("api/chat/conversations/{id}/read")
    suspend fun markRead(@Path("id") convId: Long): Response<OkResponse>

    @GET("api/chat/unread-count")
    suspend fun getUnreadCount(): Response<UnreadCountResponse>
}

data class CreateGroupRequest(val name: String, @SerializedName("user_ids") val userIds: List<Long>)
```

---

## 7. Alur Per Fitur

### 7.1 Inisialisasi App (saat login berhasil)

```
1. Simpan token dari POST /api/auth/login
2. Connect WebSocket: ChatSocketManager.connect()
3. GET /api/chat/unread-count → update badge ikon Chat
4. Mulai polling unread count setiap 30 detik (WorkManager)
```

### 7.2 Buka Halaman Chat (list conversation)

```
1. GET /api/chat/conversations
2. Render RecyclerView dengan unread badge per item
3. Untuk private conv: display_name = nama lawan; untuk group: nama grup
```

### 7.3 Buka Conversation (chat screen)

```
1. GET /api/chat/conversations/{id}/messages?page=1
   → Render dari bawah (stackFromEnd = true di LinearLayoutManager)
2. POST /api/chat/conversations/{id}/read   ← tandai dibaca
3. ChatSocketManager.subscribeConversation(convId, ...)
   → onMessageReceived: append pesan ke RecyclerView + scroll bawah
   → onTyping: tampilkan "Nama mengetik..." selama ≤ 2.5 detik
```

### 7.4 Kirim Pesan Teks

```
1. User ketik di EditText
2. Setiap keystroke → ChatSocketManager.sendTyping(convId, userId, name)
3. Klik kirim:
   a. Tampilkan optimistic bubble (pending state)
   b. POST multipart: type=text, body=...
   c. Response 201 → update bubble dengan ID asli
   d. Response error → hapus bubble, tampilkan toast error
```

### 7.5 Kirim Foto

```
1. Intent.ACTION_PICK → pilih dari galeri
2. Compress jika > 2MB (lihat compressImage() di bagian 9)
3. Tampilkan preview lokal di bubble sementara
4. POST multipart: type=image, attachment=<file>
5. Response 201:
   - attachment_url: URL foto asli (langsung bisa ditampilkan)
   - thumbnail_url: mungkin null (proses async), gunakan attachment_url dulu
6. Update bubble dengan URL dari server
```

### 7.6 Kirim File/Dokumen

```
1. Intent.ACTION_OPEN_DOCUMENT → pilih dokumen
2. POST multipart: type=file, attachment=<file>
3. Tampilkan bubble dengan ikon file, nama, dan ukuran
4. Klik bubble → Intent(ACTION_VIEW) atau download
```

### 7.7 Infinite Scroll (History Pesan)

```
1. Halaman 1 = pesan TERBARU (di bawah)
2. User scroll ke atas → panggil page=2, page=3, dst.
3. Prepend ke atas RecyclerView
4. Stop jika current_page >= last_page
5. Tampilkan loading indicator di atas saat fetch
```

### 7.8 Reply (Balas) Pesan

```
1. Long-press pesan → tampilkan opsi "Balas"
2. Tampilkan reply preview bar di atas input
3. Kirim dengan reply_to_id = <id pesan yang dibalas>
4. Tampilkan quoted preview di dalam bubble
```

### 7.9 Hapus Pesan

```
1. Long-press pesan milik sendiri → opsi "Hapus"
2. DELETE /api/chat/messages/{id}
3. Update RecyclerView: ganti body dengan "Pesan dihapus"
   (deleted_at akan menjadi non-null)
```

### 7.10 Mulai Chat dari Profil User

```
1. Dari halaman profil user: klik tombol "Chat"
2. POST /api/chat/private/{userId}
3. Navigasi ke chat screen dengan conversation_id yang dikembalikan
```

---

## 8. Error Handling

### HTTP Status Codes

| Code | Arti | Tindakan |
|---|---|---|
| `200` / `201` | Sukses | Handle response |
| `401` | Token tidak valid / expired | Redirect ke login |
| `403` | Akses ditolak (bukan peserta / bukan pemilik pesan) | Tampilkan pesan error |
| `404` | Conversation / pesan tidak ditemukan | Tampilkan pesan error |
| `422` | Validasi gagal | Tampilkan field errors |
| `429` | Rate limited | Retry setelah delay |
| `500` | Server error | Log + tampilkan pesan generik |

### Format Error 422

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "body": ["Pesan tidak boleh kosong."],
    "attachment": ["Ukuran file maksimal 50MB."]
  }
}
```

### Kotlin Error Handler

```kotlin
suspend fun <T> safeApiCall(call: suspend () -> Response<T>): ApiResult<T> {
    return try {
        val resp = call()
        when {
            resp.isSuccessful -> ApiResult.Success(resp.body()!!)
            resp.code() == 401 -> ApiResult.Unauthorized
            resp.code() == 403 -> ApiResult.Forbidden
            resp.code() == 422 -> {
                val err = Gson().fromJson(resp.errorBody()?.string(), ApiErrorResponse::class.java)
                ApiResult.ValidationError(err.errors)
            }
            else -> ApiResult.ServerError(resp.code())
        }
    } catch (e: IOException) {
        ApiResult.NetworkError
    }
}

sealed class ApiResult<out T> {
    data class Success<T>(val data: T) : ApiResult<T>()
    object Unauthorized : ApiResult<Nothing>()
    object Forbidden : ApiResult<Nothing>()
    object NetworkError : ApiResult<Nothing>()
    data class ValidationError(val errors: Map<String, List<String>>) : ApiResult<Nothing>()
    data class ServerError(val code: Int) : ApiResult<Nothing>()
}
```

---

## 9. Contoh Kode Kotlin

### RecyclerView Chat Setup

```kotlin
// ChatAdapter.kt (ViewType)
const val VIEW_TYPE_ME    = 1
const val VIEW_TYPE_OTHER = 2
const val VIEW_TYPE_IMAGE = 3
const val VIEW_TYPE_FILE  = 4
const val VIEW_TYPE_DEL   = 5

override fun getItemViewType(position: Int): Int {
    val msg = messages[position]
    if (msg.deletedAt != null) return VIEW_TYPE_DEL
    if (msg.userId == currentUserId) return VIEW_TYPE_ME
    if (msg.type == "image") return VIEW_TYPE_IMAGE
    if (msg.type == "file") return VIEW_TYPE_FILE
    return VIEW_TYPE_OTHER
}

// Layout Manager — pesan terbaru di bawah
val layoutManager = LinearLayoutManager(context).apply {
    stackFromEnd = true
}
recyclerView.layoutManager = layoutManager

// Auto-scroll ke bawah saat pesan baru masuk
fun appendMessage(msg: MessageDto) {
    messages.add(msg)
    adapter.notifyItemInserted(messages.size - 1)
    recyclerView.scrollToPosition(messages.size - 1)
}
```

### Load Gambar dengan Glide

```kotlin
Glide.with(context)
    .load(message.thumbnailUrl ?: message.attachmentUrl)
    .placeholder(R.drawable.ic_image_placeholder)
    .error(R.drawable.ic_image_error)
    .centerCrop()
    .into(imageView)
```

### Format Timestamp (hindari UTC shift)

```kotlin
fun formatMessageTime(isoString: String): String {
    // BENAR: parse sebagai UTC lalu format dengan timezone lokal
    val sdf = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss", Locale.getDefault())
    sdf.timeZone = TimeZone.getTimeZone("UTC")
    val date = sdf.parse(isoString) ?: return ""
    val now  = Calendar.getInstance()
    val then = Calendar.getInstance().apply { time = date }

    return if (then.get(Calendar.DATE)    == now.get(Calendar.DATE)   &&
               then.get(Calendar.MONTH)   == now.get(Calendar.MONTH)  &&
               then.get(Calendar.YEAR)    == now.get(Calendar.YEAR)) {
        // Hari ini → tampilkan jam
        SimpleDateFormat("HH:mm", Locale.getDefault()).format(date)
    } else {
        // Hari lain → tampilkan tanggal
        SimpleDateFormat("dd MMM", Locale("id")).format(date)
    }
}

// ⚠️ JANGAN gunakan ini (timezone shift bug):
// val wrongDate = SimpleDateFormat("HH:mm").format(Date(isoString))
```

### Compress Gambar Sebelum Upload

```kotlin
fun compressImage(context: Context, uri: Uri, maxSizeKB: Int = 1500): ByteArray {
    val input  = context.contentResolver.openInputStream(uri)!!
    val bitmap = BitmapFactory.decodeStream(input)
    val output = ByteArrayOutputStream()
    var quality = 95
    do {
        output.reset()
        bitmap.compress(Bitmap.CompressFormat.JPEG, quality, output)
        quality -= 10
    } while (output.size() / 1024 > maxSizeKB && quality > 20)
    bitmap.recycle()
    return output.toByteArray()
}
```

### Typing Indicator dengan Debounce

```kotlin
private var typingJob: Job? = null

editText.addTextChangedListener(object : TextWatcher {
    override fun afterTextChanged(s: Editable?) {
        if (s.isNullOrEmpty()) return
        typingJob?.cancel()
        typingJob = scope.launch {
            chatSocketManager.sendTyping(convId, userId, userName)
            delay(2000) // Tidak kirim lagi selama 2 detik
        }
    }
    override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
    override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
})
```

### Background Sync Unread Count (WorkManager)

```kotlin
class ChatUnreadWorker(ctx: Context, params: WorkerParameters) : CoroutineWorker(ctx, params) {
    override suspend fun doWork(): Result {
        return try {
            val resp = chatApiService.getUnreadCount()
            if (resp.isSuccessful && (resp.body()?.count ?: 0) > 0) {
                showChatNotification(resp.body()!!.count)
            }
            Result.success()
        } catch (e: Exception) {
            Result.retry()
        }
    }
}

// Jadwalkan di Application.onCreate() setelah login:
fun scheduleChatSync() {
    val work = PeriodicWorkRequestBuilder<ChatUnreadWorker>(30, TimeUnit.MINUTES)
        .setConstraints(Constraints.Builder()
            .setRequiredNetworkType(NetworkType.CONNECTED)
            .build())
        .build()
    WorkManager.getInstance(context)
        .enqueueUniquePeriodicWork("chat_unread_sync", ExistingPeriodicWorkPolicy.KEEP, work)
}
```

---

## 10. Checklist Integrasi

### Setup Awal
- [ ] Tambahkan dependency `pusher-java-client:2.4.4` di build.gradle
- [ ] Setup Retrofit dengan `AuthInterceptor` dan Gson `LOWER_CASE_WITH_UNDERSCORES`
- [ ] Implement `ChatSocketManager` dengan `channelAuthorizer` ke `/broadcasting/auth`
- [ ] Setup WorkManager untuk polling unread count

### Halaman Daftar Chat
- [ ] `GET /api/chat/conversations` → render list
- [ ] Badge unread per conversation
- [ ] Tombol "+ Chat" → cari user → `POST /api/chat/private/{userId}`
- [ ] Tombol "+ Group" → pilih user → `POST /api/chat/group`

### Halaman Chat (Conversation)
- [ ] `GET /api/chat/conversations/{id}/messages?page=1` → render dari bawah
- [ ] `POST /api/chat/conversations/{id}/read` saat buka
- [ ] Subscribe WebSocket channel `private-conversation.{id}`
- [ ] Terima `MessageSent` → append + scroll bawah + call `/read`
- [ ] Kirim teks dengan `POST` multipart `type=text`
- [ ] Kirim foto dengan `type=image` + attachment
- [ ] Kirim file dengan `type=file` + attachment
- [ ] Infinite scroll (page++) saat scroll ke atas
- [ ] Typing indicator: kirim `client-typing` saat ketik, tampilkan saat terima
- [ ] Reply: simpan `reply_to_id`, tampilkan quoted bubble
- [ ] Hapus pesan: `DELETE`, update UI dengan placeholder
- [ ] Unsubscribe WebSocket saat keluar conversation

### Edge Cases
- [ ] `thumbnail_url` null → fallback ke `attachment_url`
- [ ] `deleted_at != null` → tampilkan "Pesan dihapus"
- [ ] Token 401 → redirect ke login
- [ ] Upload gagal → hapus optimistic bubble + retry option
- [ ] Offline → disable input, tampilkan banner offline

---

## 11. FAQ & Pitfalls

**Q: WebSocket connect tapi tidak menerima pesan?**
- Pastikan sudah subscribe channel: `private-conversation.{convId}` (bukan `conversation.{convId}`)
- Pastikan channel authorizer berhasil → cek response dari `/broadcasting/auth`
- Event name di bind harus **`MessageSent`** (bukan `.MessageSent` atau `message.sent`)

**Q: `thumbnail_url` selalu null?**
- Thumbnail diproses async oleh queue worker. Pertama kali kirim foto, `thumbnail_url` = null.
- Selalu gunakan `attachment_url` sebagai fallback: `thumbnailUrl ?: attachmentUrl`
- Thumbnail akan tersedia dalam 1-5 detik setelah upload

**Q: Upload 500 error?**
- Pastikan menggunakan `multipart/form-data`, bukan JSON
- Pastikan field `type` ada dan nilainya `text`, `image`, atau `file`
- Untuk teks, `body` wajib ada dan tidak boleh kosong

**Q: Mixed content / gambar tidak muncul di HTTPS?**
- Semua URL gambar dari server sudah HTTPS: `https://studycenter.nanoprojectdevindonesia.com/storage/...`
- Jangan hardcode URL dengan HTTP

**Q: Timestamp salah (beda 1 hari)?**
- Jangan gunakan `new Date("2026-09-25")` (JavaScript) atau `SimpleDateFormat` tanpa timezone — keduanya bisa shift timezone
- Gunakan contoh `formatMessageTime()` di bagian 9 yang sudah benar

**Q: Bagaimana mendeteksi tipe file dari MIME?**
```kotlin
fun getFileIcon(mime: String?): Int = when {
    mime == null                         -> R.drawable.ic_file_generic
    mime.startsWith("image/")           -> R.drawable.ic_file_image
    mime == "application/pdf"           -> R.drawable.ic_file_pdf
    mime.contains("word")              -> R.drawable.ic_file_doc
    mime.contains("excel") || mime.contains("spreadsheet") -> R.drawable.ic_file_xls
    mime.contains("zip") || mime.contains("rar") -> R.drawable.ic_file_zip
    else                                -> R.drawable.ic_file_generic
}
```

**Q: Perlu notifikasi push saat app tertutup?**
- Belum diimplementasi (scope fase 2). Untuk sementara, gunakan WorkManager polling `/api/chat/unread-count` setiap 15-30 menit dan tampilkan local notification jika ada unread baru.

---

## Changelog

| Tanggal | Versi | Perubahan |
|---|---|---|
| 2026-09-25 | **2.0.0** | **Breaking:** WebSocket URL berubah ke `wss://domain.com/app/scnias_reverb_key_2026`. Fix URL gambar ke HTTPS. Tambah fitur search user, group chat, reply, hapus pesan, typing indicator. Retrofit interface lengkap. Contoh kode Kotlin lengkap. |
| 2026-09-25 | 1.0.0 | Implementasi awal |

---

*Pertanyaan: hubungi tim backend via admin@studycenter.com*
*Dokumen disimpan di: `/var/www/study-center-nias/docs/ANDROID_CHAT_API.md`*
