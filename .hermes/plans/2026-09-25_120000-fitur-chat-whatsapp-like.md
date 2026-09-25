# Fitur Chat WhatsApp-Like — Implementation Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Menambahkan fitur chat real-time antar user pada Study Center Nias — mirip WhatsApp: private chat, group chat, kirim foto/file/dokumen, status online, notifikasi, dan tampilan modern.

**Architecture:**
- **Backend:** Laravel 13 (sudah ada) + Laravel Reverb (WebSocket server, gratis/built-in) + Laravel Echo (JS client) + Queue (database driver, sudah ada)
- **Frontend:** Blade + Alpine.js + Tailwind (sudah ada) — tanpa React/Vue, konsisten dengan stack existing
- **Storage:** Local disk / Laravel Storage untuk file upload (foto, dokumen)
- **Real-time:** Laravel Reverb (self-hosted WebSocket, pengganti Pusher, 100% gratis & open source)

**Tech Stack:**
- Laravel 13, Laravel Reverb (WebSocket), Laravel Echo, Alpine.js, Tailwind CSS
- MySQL (messages, conversations, participants), Redis (presence channel, queue)
- Laravel Storage (file attachment), Intervention Image (resize thumbnail foto)

---

## Ringkasan Fitur Target

| Fitur | WhatsApp | Target kita |
|---|---|---|
| Private chat (1-1) | ✅ | ✅ |
| Group chat | ✅ | ✅ |
| Kirim teks | ✅ | ✅ |
| Kirim foto | ✅ | ✅ |
| Kirim file/dokumen | ✅ | ✅ |
| Status online/offline | ✅ | ✅ |
| Tanda baca (sent/delivered/read) | ✅ | ✅ (basic: sent/read) |
| Notifikasi in-app | ✅ | ✅ |
| Pencarian pesan | ✅ | ✅ (basic) |
| Hapus pesan | ✅ | ✅ |
| Balas pesan (reply) | ✅ | ✅ |

---

## Stack & Library (semua gratis / open source)

| Library | Fungsi | Install |
|---|---|---|
| `laravel/reverb` | WebSocket server built-in Laravel | `composer require laravel/reverb` |
| `laravel/echo` | JS WebSocket client | sudah di npm ekosistem |
| `pusher-js` | Diperlukan oleh Echo sebagai transport | `npm install pusher-js` |
| `intervention/image-laravel` | Resize thumbnail foto | `composer require intervention/image-laravel` |
| Alpine.js | Reaktifitas UI minimal (sudah ada di stack) | sudah ada |
| Tailwind CSS | Styling (sudah ada) | sudah ada |

---

## Skema Database

### Tabel Baru

```sql
-- conversations: private atau group
CREATE TABLE conversations (
    id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type          ENUM('private', 'group') DEFAULT 'private',
    name          VARCHAR(255) NULL,        -- hanya untuk group
    avatar        VARCHAR(255) NULL,
    created_by    BIGINT UNSIGNED NOT NULL,
    last_message_at TIMESTAMP NULL,
    created_at    TIMESTAMP,
    updated_at    TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- conversation_participants: siapa saja anggota
CREATE TABLE conversation_participants (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    role            ENUM('admin', 'member') DEFAULT 'member',
    joined_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_read_at    TIMESTAMP NULL,        -- untuk fitur read receipt
    UNIQUE KEY uq_conv_user (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- messages: semua pesan
CREATE TABLE messages (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    reply_to_id     BIGINT UNSIGNED NULL,   -- untuk fitur reply
    type            ENUM('text','image','file','system') DEFAULT 'text',
    body            TEXT NULL,              -- konten teks
    attachment_path VARCHAR(500) NULL,      -- path file
    attachment_name VARCHAR(255) NULL,      -- nama asli file
    attachment_mime VARCHAR(100) NULL,      -- mime type
    attachment_size  BIGINT NULL,           -- ukuran bytes
    thumbnail_path  VARCHAR(500) NULL,      -- thumbnail untuk gambar
    deleted_at      TIMESTAMP NULL,         -- soft delete
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_to_id) REFERENCES messages(id) ON DELETE SET NULL
);

-- message_reads: tracking siapa sudah baca
CREATE TABLE message_reads (
    id         BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    read_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_msg_user (message_id, user_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Struktur File yang Akan Dibuat/Dimodifikasi

```
app/
├── Events/
│   ├── MessageSent.php             [BARU] - broadcast event
│   ├── UserOnlineStatus.php        [BARU] - broadcast online/offline
│   └── MessageRead.php             [BARU] - broadcast read receipt
├── Http/Controllers/
│   └── Chat/
│       ├── ChatController.php      [BARU] - halaman utama chat
│       ├── ConversationController.php [BARU] - CRUD conversation
│       └── MessageController.php   [BARU] - kirim/hapus/reply pesan
├── Jobs/
│   └── ProcessChatAttachment.php   [BARU] - resize thumbnail (queue)
├── Models/
│   ├── Conversation.php            [BARU]
│   ├── ConversationParticipant.php [BARU]
│   ├── Message.php                 [BARU]
│   └── MessageRead.php             [BARU]
├── Policies/
│   └── ConversationPolicy.php      [BARU] - otorisasi akses chat
database/
├── migrations/
│   ├── xxxx_create_conversations_table.php          [BARU]
│   ├── xxxx_create_conversation_participants_table.php [BARU]
│   ├── xxxx_create_messages_table.php               [BARU]
│   └── xxxx_create_message_reads_table.php          [BARU]
resources/
├── js/
│   ├── chat.js                     [BARU] - Alpine + Echo logic
│   └── app.js                      [MODIF] - import chat.js
├── views/
│   └── chat/
│       ├── index.blade.php         [BARU] - layout utama chat
│       ├── partials/
│       │   ├── sidebar.blade.php   [BARU] - daftar conversation
│       │   ├── message-item.blade.php [BARU] - komponen 1 pesan
│       │   └── attachment-preview.blade.php [BARU]
│       └── components/
│           └── message-input.blade.php [BARU] - input + upload
routes/
├── web.php                         [MODIF] - tambah route chat
├── channels.php                    [MODIF] - otorisasi broadcast channel
config/
├── reverb.php                      [BARU - auto dari artisan]
.env                                [MODIF] - tambah REVERB_* config
docker-compose.yml                  [MODIF] - tambah reverb service
```

---

## Task-by-Task Plan

---

### Task 1: Install & Konfigurasi Laravel Reverb

**Objective:** Setup WebSocket server (Reverb) di dalam Docker

**Files:**
- Modify: `docker-compose.yml`
- Modify: `.env`
- Create: `config/reverb.php` (auto via artisan)

**Step 1: Install Reverb**
```bash
docker compose exec -T php composer require laravel/reverb
docker compose exec -T php php artisan reverb:install
```
Saat ditanya, pilih: `reverb` sebagai driver broadcasting.

**Step 2: Install pusher-js di frontend**
```bash
npm install pusher-js laravel-echo
```

**Step 3: Tambahkan service Reverb di docker-compose.yml**

Tambahkan setelah service `queue`:
```yaml
  reverb:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: study_center_reverb
    working_dir: /var/www/html
    command: php artisan reverb:start --host=0.0.0.0 --port=8080 --debug
    volumes:
      - .:/var/www/html
    networks:
      - app_network
    depends_on:
      - redis
      - mysql
    restart: unless-stopped
    environment:
      - APP_ENV=production
```

**Step 4: Update .env**
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=study_center_chat
REVERB_APP_KEY=scnias_reverb_key_2026
REVERB_APP_SECRET=scnias_reverb_secret_2026
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**Step 5: Ekspos port Reverb via Nginx**

Tambahkan di konfigurasi Nginx (`docker/nginx/default.conf`):
```nginx
location /app/ {
    proxy_pass http://reverb:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```
> ⚠️ **PENTING:** Reverb WebSocket harus bisa diakses dari browser. Karena domain pakai HTTPS, koneksi WebSocket harus lewat WSS (Nginx proxy_pass upgrade). Port 8080 tidak perlu diekspos ke publik — cukup proxied via Nginx.

**Step 6: Update VITE_REVERB_HOST ke domain**
```env
VITE_REVERB_HOST=studycenter.nanoprojectdevindonesia.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

**Verification:**
```bash
docker compose up -d reverb
docker compose logs reverb --tail=20
# Expected: "Reverb server started" pada port 8080
```

---

### Task 2: Migrations & Models

**Objective:** Buat 4 tabel database dan model Laravel

**Files:**
- Create: `database/migrations/2026_09_25_000001_create_conversations_table.php`
- Create: `database/migrations/2026_09_25_000002_create_conversation_participants_table.php`
- Create: `database/migrations/2026_09_25_000003_create_messages_table.php`
- Create: `database/migrations/2026_09_25_000004_create_message_reads_table.php`
- Create: `app/Models/Conversation.php`
- Create: `app/Models/ConversationParticipant.php`
- Create: `app/Models/Message.php`
- Create: `app/Models/MessageRead.php`

**Step 1: Generate migrations**
```bash
docker compose exec -T php php artisan make:migration create_conversations_table
docker compose exec -T php php artisan make:migration create_conversation_participants_table
docker compose exec -T php php artisan make:migration create_messages_table
docker compose exec -T php php artisan make:migration create_message_reads_table
```

**Step 2: Isi migration conversations**
```php
// database/migrations/xxxx_create_conversations_table.php
public function up(): void
{
    Schema::create('conversations', function (Blueprint $table) {
        $table->id();
        $table->enum('type', ['private', 'group'])->default('private');
        $table->string('name')->nullable();
        $table->string('avatar')->nullable();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->timestamp('last_message_at')->nullable();
        $table->timestamps();
    });
}
```

**Step 3: Isi migration conversation_participants**
```php
public function up(): void
{
    Schema::create('conversation_participants', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->enum('role', ['admin', 'member'])->default('member');
        $table->timestamp('joined_at')->useCurrent();
        $table->timestamp('last_read_at')->nullable();
        $table->unique(['conversation_id', 'user_id']);
    });
}
```

**Step 4: Isi migration messages**
```php
public function up(): void
{
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();
        $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
        $table->text('body')->nullable();
        $table->string('attachment_path', 500)->nullable();
        $table->string('attachment_name')->nullable();
        $table->string('attachment_mime', 100)->nullable();
        $table->unsignedBigInteger('attachment_size')->nullable();
        $table->string('thumbnail_path', 500)->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
}
```

**Step 5: Isi migration message_reads**
```php
public function up(): void
{
    Schema::create('message_reads', function (Blueprint $table) {
        $table->id();
        $table->foreignId('message_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->timestamp('read_at')->useCurrent();
        $table->unique(['message_id', 'user_id']);
    });
}
```

**Step 6: Model Conversation.php**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    protected $fillable = ['type', 'name', 'avatar', 'created_by', 'last_message_at'];
    protected $casts = ['last_message_at' => 'datetime'];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
                    ->withPivot(['role', 'joined_at', 'last_read_at'])
                    ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /** Cari atau buat private conversation antara 2 user */
    public static function findOrCreatePrivate(int $userA, int $userB): self
    {
        // Cari conversation private yang memiliki tepat kedua user
        $existing = self::where('type', 'private')
            ->whereHas('participants', fn($q) => $q->where('user_id', $userA))
            ->whereHas('participants', fn($q) => $q->where('user_id', $userB))
            ->first();

        if ($existing) return $existing;

        $conv = self::create(['type' => 'private', 'created_by' => $userA]);
        $conv->participants()->attach([$userA => ['role' => 'member'], $userB => ['role' => 'member']]);
        return $conv;
    }

    public function unreadCount(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant) return 0;
        $lastRead = $participant->pivot->last_read_at;
        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->when($lastRead, fn($q) => $q->where('created_at', '>', $lastRead))
            ->count();
    }
}
```

**Step 7: Model Message.php**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'conversation_id','user_id','reply_to_id','type',
        'body','attachment_path','attachment_name','attachment_mime',
        'attachment_size','thumbnail_path'
    ];

    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function conversation(): BelongsTo { return $this->belongsTo(Conversation::class); }
    public function replyTo(): BelongsTo { return $this->belongsTo(Message::class, 'reply_to_id'); }
    public function reads(): HasMany     { return $this->hasMany(MessageRead::class); }

    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path ? asset('storage/' . $this->attachment_path) : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }
}
```

**Step 8: Jalankan migration**
```bash
docker compose exec -T php php artisan migrate
```
Expected: 4 tabel baru terbuat tanpa error.

---

### Task 3: Events & Broadcasting (Real-time)

**Objective:** Buat Laravel Events yang di-broadcast via Reverb WebSocket

**Files:**
- Create: `app/Events/MessageSent.php`
- Create: `app/Events/MessageRead.php`
- Create: `app/Events/UserTyping.php`
- Modify: `routes/channels.php`

**Step 1: MessageSent Event**
```php
<?php
// app/Events/MessageSent.php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->message->conversation_id)];
    }

    public function broadcastWith(): array
    {
        $this->message->load(['user:id,name,avatar', 'replyTo:id,body,user_id']);
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id'         => $this->message->user_id,
            'user_name'       => $this->message->user->name,
            'user_avatar'     => $this->message->user->avatar ?? null,
            'type'            => $this->message->type,
            'body'            => $this->message->body,
            'attachment_url'  => $this->message->attachment_url,
            'attachment_name' => $this->message->attachment_name,
            'attachment_mime' => $this->message->attachment_mime,
            'thumbnail_url'   => $this->message->thumbnail_url,
            'reply_to'        => $this->message->replyTo ? [
                'id'   => $this->message->replyTo->id,
                'body' => $this->message->replyTo->body,
            ] : null,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
```

**Step 2: UserTyping Event (client event — tidak perlu PHP class)**
> Laravel Echo support "client events" langsung dari JS tanpa perlu PHP event. Gunakan ini untuk typing indicator.

**Step 3: MessageRead Event**
```php
<?php
// app/Events/MessageRead.php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageRead implements ShouldBroadcastNow
{
    public function __construct(
        public int $conversationId,
        public int $userId,
        public string $readAt
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->conversationId)];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'    => $this->userId,
            'read_at'    => $this->readAt,
        ];
    }
}
```

**Step 4: Authorization channels**
```php
// routes/channels.php — tambahkan:
use App\Models\Conversation;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) return false;
    return $conversation->participants()->where('user_id', $user->id)->exists();
});
```

---

### Task 4: Controllers & API

**Objective:** Buat controller untuk chat pages + API endpoints

**Files:**
- Create: `app/Http/Controllers/Chat/ChatController.php`
- Create: `app/Http/Controllers/Chat/ConversationController.php`
- Create: `app/Http/Controllers/Chat/MessageController.php`
- Modify: `routes/web.php`

**Step 1: ChatController (halaman utama)**
```php
<?php
namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function index()
    {
        $user = Auth::user();
        $conversations = Conversation::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->with(['participants:id,name', 'lastMessage.user:id,name'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) use ($user) {
                $conv->unread_count = $conv->unreadCount($user->id);
                // Untuk private chat, ganti nama ke nama lawan bicara
                if ($conv->type === 'private') {
                    $other = $conv->participants->firstWhere('id', '!=', $user->id);
                    $conv->display_name   = $other?->name ?? 'Unknown';
                    $conv->display_avatar = $other?->avatar ?? null;
                } else {
                    $conv->display_name   = $conv->name;
                    $conv->display_avatar = $conv->avatar;
                }
                return $conv;
            });

        $users = User::where('id', '!=', $user->id)->select('id','name')->get();

        return view('chat.index', compact('conversations', 'users'));
    }
}
```

**Step 2: ConversationController**
```php
<?php
namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    /** GET /chat/conversations/{id}/messages — load pesan (paginated) */
    public function messages(Conversation $conversation)
    {
        $user = Auth::user();
        // Pastikan user adalah peserta
        abort_unless($conversation->participants()->where('user_id', $user->id)->exists(), 403);

        $messages = $conversation->messages()
            ->with(['user:id,name', 'replyTo:id,body,user_id'])
            ->withTrashed() // tampilkan "pesan dihapus"
            ->latest()
            ->paginate(30);

        // Tandai semua pesan sebagai sudah dibaca
        $conversation->participants()
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        return response()->json($messages);
    }

    /** POST /chat/private/{userId} — mulai private chat */
    public function startPrivate(int $userId)
    {
        $me   = Auth::id();
        $conv = Conversation::findOrCreatePrivate($me, $userId);
        return response()->json(['conversation_id' => $conv->id]);
    }

    /** POST /chat/group — buat group chat */
    public function createGroup(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $me   = Auth::id();
        $conv = Conversation::create([
            'type'       => 'group',
            'name'       => $request->name,
            'created_by' => $me,
        ]);

        $participants = collect($request->user_ids)
            ->push($me)
            ->unique()
            ->mapWithKeys(fn($id) => [$id => ['role' => $id === $me ? 'admin' : 'member']]);

        $conv->participants()->attach($participants);

        return response()->json(['conversation_id' => $conv->id]);
    }
}
```

**Step 3: MessageController**
```php
<?php
namespace App\Http\Controllers\Chat;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessChatAttachment;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    /** POST /chat/conversations/{id}/messages */
    public function send(Request $request, Conversation $conversation)
    {
        $user = Auth::user();
        abort_unless($conversation->participants()->where('user_id', $user->id)->exists(), 403);

        $request->validate([
            'type'        => 'required|in:text,image,file',
            'body'        => 'required_if:type,text|string|max:5000|nullable',
            'attachment'  => 'required_if:type,image|required_if:type,file|file|max:51200', // 50MB max
            'reply_to_id' => 'nullable|exists:messages,id',
        ]);

        $data = [
            'conversation_id' => $conversation->id,
            'user_id'         => $user->id,
            'type'            => $request->type,
            'body'            => $request->body,
            'reply_to_id'     => $request->reply_to_id,
        ];

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file       = $request->file('attachment');
            $ext        = $file->getClientOriginalExtension();
            $filename   = Str::uuid() . '.' . $ext;
            $subFolder  = $request->type === 'image' ? 'chat/images' : 'chat/files';
            $path       = $file->storeAs($subFolder, $filename, 'public');

            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
        }

        $message = Message::create($data);

        // Proses thumbnail di background (untuk image)
        if ($request->type === 'image') {
            ProcessChatAttachment::dispatch($message->id);
        }

        // Update last_message_at conversation
        $conversation->update(['last_message_at' => now()]);

        // Mark sebagai sudah dibaca oleh pengirim
        $conversation->participants()
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        // Broadcast ke semua peserta
        broadcast(new MessageSent($message));

        return response()->json(['message' => $message->fresh(['user:id,name'])], 201);
    }

    /** DELETE /chat/messages/{id} */
    public function destroy(Message $message)
    {
        abort_unless($message->user_id === Auth::id(), 403);
        $message->delete(); // soft delete
        return response()->json(['ok' => true]);
    }

    /** POST /chat/conversations/{id}/read — tandai sudah dibaca */
    public function markRead(Conversation $conversation)
    {
        $user = Auth::user();
        abort_unless($conversation->participants()->where('user_id', $user->id)->exists(), 403);

        $conversation->participants()
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        broadcast(new MessageRead($conversation->id, $user->id, now()->toISOString()))->toOthers();

        return response()->json(['ok' => true]);
    }
}
```

**Step 4: Routes**
```php
// routes/web.php — tambahkan grup:
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');

    // Conversation
    Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages'])->name('messages');
    Route::post('/private/{userId}', [ConversationController::class, 'startPrivate'])->name('private');
    Route::post('/group', [ConversationController::class, 'createGroup'])->name('group.create');

    // Messages
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'send'])->name('send');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('message.destroy');
    Route::post('/conversations/{conversation}/read', [MessageController::class, 'markRead'])->name('read');
});
```

---

### Task 5: Job — ProcessChatAttachment

**Objective:** Resize thumbnail gambar secara async via queue

**Files:**
- Create: `app/Jobs/ProcessChatAttachment.php`

**Step 1: Install Intervention Image**
```bash
docker compose exec -T php composer require intervention/image-laravel
```

**Step 2: Job**
```php
<?php
// app/Jobs/ProcessChatAttachment.php
namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class ProcessChatAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $messageId) {}

    public function handle(): void
    {
        $message = Message::find($this->messageId);
        if (!$message || !$message->attachment_path) return;

        $sourcePath  = Storage::disk('public')->path($message->attachment_path);
        $thumbName   = Str::uuid() . '_thumb.webp';
        $thumbPath   = 'chat/thumbs/' . $thumbName;
        $thumbFsPath = Storage::disk('public')->path($thumbPath);

        // Buat direktori jika belum ada
        @mkdir(dirname($thumbFsPath), 0755, true);

        // Resize dan konversi ke webp
        Image::read($sourcePath)
            ->scale(width: 400)
            ->toWebp(quality: 75)
            ->save($thumbFsPath);

        $message->update(['thumbnail_path' => $thumbPath]);
    }
}
```

---

### Task 6: Frontend — Alpine.js + Echo (chat.js)

**Objective:** Logic JavaScript untuk real-time chat (subscribe channel, kirim pesan, typing indicator, file upload preview)

**Files:**
- Create: `resources/js/chat.js`
- Modify: `resources/js/app.js`

**Step 1: Tambahkan Echo config ke app.js**
```js
// resources/js/app.js — tambahkan di bagian atas:
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key:         import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:      import.meta.env.VITE_REVERB_HOST,
    wsPort:      import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort:     import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS:    (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

**Step 2: chat.js Alpine component**
```js
// resources/js/chat.js
export function chatApp(config) {
    return {
        // State
        activeConvId:   config.activeConvId ?? null,
        conversations:  config.conversations ?? [],
        messages:       [],
        inputText:      '',
        replyTo:        null,
        isTyping:       {},
        typingTimeout:  null,
        isLoading:      false,
        attachmentFile: null,
        attachmentPreview: null,

        // Init
        init() {
            if (this.activeConvId) this.openConversation(this.activeConvId);
        },

        // Buka conversation
        async openConversation(convId) {
            this.activeConvId = convId;
            this.messages     = [];
            this.isLoading    = true;
            this.replyTo      = null;

            // Unsubscribe channel lama
            if (this._echoChannel) this._echoChannel.unsubscribe();

            // Load messages
            const res  = await fetch(`/chat/conversations/${convId}/messages`);
            const data = await res.json();
            this.messages = data.data.reverse();
            this.isLoading = false;

            // Scroll ke bawah
            this.$nextTick(() => this.scrollToBottom());

            // Subscribe WebSocket
            this._echoChannel = window.Echo.private(`conversation.${convId}`)
                .listen('.MessageSent', (e) => {
                    if (e.user_id !== config.userId) {
                        this.messages.push(e);
                        this.$nextTick(() => this.scrollToBottom());
                        this.markRead(convId);
                    }
                })
                .listen('.MessageRead', (e) => {
                    // Update UI read receipt jika perlu
                })
                .listenForWhisper('typing', (e) => {
                    this.isTyping[e.user_id] = e.name;
                    clearTimeout(this._typingClear);
                    this._typingClear = setTimeout(() => {
                        this.isTyping = {};
                    }, 2000);
                });

            // Mark read
            this.markRead(convId);
        },

        // Kirim pesan teks
        async sendMessage() {
            if (!this.inputText.trim() && !this.attachmentFile) return;

            const form = new FormData();
            if (this.attachmentFile) {
                const isImage = this.attachmentFile.type.startsWith('image/');
                form.append('type', isImage ? 'image' : 'file');
                form.append('attachment', this.attachmentFile);
                if (this.inputText.trim()) form.append('body', this.inputText.trim());
            } else {
                form.append('type', 'text');
                form.append('body', this.inputText.trim());
            }
            if (this.replyTo) form.append('reply_to_id', this.replyTo.id);

            const res = await fetch(`/chat/conversations/${this.activeConvId}/messages`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: form,
            });

            if (res.ok) {
                const { message } = await res.json();
                this.messages.push(message);
                this.inputText = '';
                this.replyTo   = null;
                this.clearAttachment();
                this.$nextTick(() => this.scrollToBottom());
                this.updateConvLastMessage(message);
            }
        },

        // Handle file/foto pilih
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.attachmentFile = file;
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => { this.attachmentPreview = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.attachmentPreview = null; // tampilkan nama file
            }
        },

        clearAttachment() {
            this.attachmentFile    = null;
            this.attachmentPreview = null;
        },

        // Typing indicator
        onTyping() {
            if (!this._echoChannel) return;
            this._echoChannel.whisper('typing', { user_id: config.userId, name: config.userName });
            clearTimeout(this.typingTimeout);
            this.typingTimeout = setTimeout(() => {
                this._echoChannel.whisper('typing-stop', {});
            }, 1500);
        },

        // Set reply
        setReply(msg) { this.replyTo = msg; },
        cancelReply() { this.replyTo = null; },

        // Delete message
        async deleteMessage(msgId) {
            if (!confirm('Hapus pesan ini?')) return;
            await fetch(`/chat/messages/${msgId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });
            const idx = this.messages.findIndex(m => m.id === msgId);
            if (idx !== -1) this.messages[idx].deleted_at = new Date().toISOString();
        },

        // Mark read
        async markRead(convId) {
            await fetch(`/chat/conversations/${convId}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            });
            const conv = this.conversations.find(c => c.id === convId);
            if (conv) conv.unread_count = 0;
        },

        updateConvLastMessage(message) {
            const conv = this.conversations.find(c => c.id === this.activeConvId);
            if (conv) {
                conv.last_message = message;
                // Pindah ke atas daftar
                this.conversations = [conv, ...this.conversations.filter(c => c.id !== this.activeConvId)];
            }
        },

        scrollToBottom() {
            const el = this.$refs.messageList;
            if (el) el.scrollTop = el.scrollHeight;
        },

        // Start private chat dengan user baru
        async startPrivateWith(userId) {
            const res = await fetch(`/chat/private/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });
            const { conversation_id } = await res.json();
            await this.openConversation(conversation_id);
        },

        // Format helpers
        formatTime(iso) {
            const d = new Date(iso);
            return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
        isImage(mime) { return mime?.startsWith('image/'); },
    };
}
```

---

### Task 7: UI — Blade Views

**Objective:** Buat tampilan chat yang menarik (style WhatsApp Web)

**Files:**
- Create: `resources/views/chat/index.blade.php`

**Konsep Layout:**
```
┌─────────────────────────────────────────────────┐
│  HEADER (nama app + tombol buat group)           │
├───────────────┬─────────────────────────────────┤
│               │  Nama Kontak / Group             │
│  Daftar Chat  ├─────────────────────────────────┤
│  (sidebar)    │                                  │
│               │  Bubble Pesan (scroll area)      │
│  [Avatar]     │                                  │
│  Nama         │                                  │
│  Pesan terakh │                                  │
│  ir + unread  ├─────────────────────────────────┤
│               │  [Reply preview jika ada]        │
│               │  [Attachment preview jika ada]   │
│               │  [Input] [File] [Send]           │
└───────────────┴─────────────────────────────────┘
```

**Design Notes:**
- Gunakan warna hijau (#075E54, #128C7E, #25D366) mirip WhatsApp
- Bubble pesan saya: pojok kanan, hijau muda
- Bubble pesan orang lain: pojok kiri, putih
- Tampilkan ikon file, preview foto thumbnail
- Typing indicator: "Mengetik..."
- Badge unread: angka merah/hijau di sidebar

**Step 1: Buat view**
```blade
{{-- resources/views/chat/index.blade.php --}}
@extends('layouts.app')  {{-- atau layout yang dipakai project ini --}}

@section('title', 'Chat')

@push('styles')
<style>
  /* CSS custom untuk bubble, scroll, dll */
  .chat-bubble-me    { @apply bg-green-100 rounded-tl-2xl rounded-tr-sm rounded-b-2xl; }
  .chat-bubble-other { @apply bg-white rounded-tr-2xl rounded-tl-sm rounded-b-2xl; }
  #message-list { scroll-behavior: smooth; }
</style>
@endpush

@section('content')
<div x-data="chatApp({
        conversations: @json($conversations),
        users:         @json($users),
        userId:        {{ Auth::id() }},
        userName:      '{{ Auth::user()->name }}'
    })"
    x-init="init()"
    class="flex h-[calc(100vh-64px)] bg-gray-100 overflow-hidden"
>
    {{-- SIDEBAR: daftar conversation --}}
    <div class="w-80 bg-white border-r flex flex-col flex-shrink-0">
        {{-- Header sidebar --}}
        <div class="bg-[#075E54] text-white p-4 flex justify-between items-center">
            <span class="font-bold text-lg">💬 Chat</span>
            <button @click="$dispatch('open-new-chat')" class="text-white hover:text-green-200 text-sm">
                + Mulai Chat
            </button>
        </div>

        {{-- List conversations --}}
        <div class="overflow-y-auto flex-1">
            <template x-for="conv in conversations" :key="conv.id">
                <div @click="openConversation(conv.id)"
                     :class="activeConvId === conv.id ? 'bg-gray-100' : 'hover:bg-gray-50'"
                     class="flex items-center p-3 cursor-pointer border-b">
                    {{-- Avatar --}}
                    <div class="w-11 h-11 rounded-full bg-green-500 flex items-center justify-center text-white font-bold mr-3 flex-shrink-0">
                        <span x-text="(conv.display_name ?? 'U')[0].toUpperCase()"></span>
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between">
                            <span class="font-semibold truncate" x-text="conv.display_name"></span>
                            <span class="text-xs text-gray-400" x-text="conv.last_message ? formatTime(conv.last_message.created_at) : ''"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 truncate"
                                  x-text="conv.last_message?.type === 'image' ? '📷 Foto' : conv.last_message?.type === 'file' ? '📎 File' : (conv.last_message?.body ?? '')">
                            </span>
                            <span x-show="conv.unread_count > 0"
                                  class="bg-green-500 text-white text-xs rounded-full px-1.5 py-0.5 ml-1"
                                  x-text="conv.unread_count"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- AREA CHAT UTAMA --}}
    <div class="flex-1 flex flex-col" x-show="activeConvId">
        {{-- Header chat --}}
        <div class="bg-[#075E54] text-white px-4 py-3 flex items-center shadow">
            <div class="w-9 h-9 rounded-full bg-green-400 flex items-center justify-center font-bold mr-3">
                <span x-text="(conversations.find(c=>c.id===activeConvId)?.display_name ?? 'U')[0]?.toUpperCase()"></span>
            </div>
            <div>
                <div class="font-semibold" x-text="conversations.find(c=>c.id===activeConvId)?.display_name"></div>
                <div class="text-xs text-green-200">
                    <template x-if="Object.keys(isTyping).length > 0">
                        <span>Mengetik...</span>
                    </template>
                </div>
            </div>
        </div>

        {{-- Daftar pesan --}}
        <div id="message-list" x-ref="messageList"
             class="flex-1 overflow-y-auto p-4 space-y-2"
             style="background: #E5DDD5 url('/img/chat-bg.png')">

            <div x-show="isLoading" class="text-center text-gray-400 py-4">Memuat pesan...</div>

            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.user_id === {{ Auth::id() }} ? 'flex justify-end' : 'flex justify-start'">
                    <div class="max-w-xs lg:max-w-md relative group">
                        {{-- Bubble --}}
                        <div :class="msg.user_id === {{ Auth::id() }} ? 'chat-bubble-me' : 'chat-bubble-other'"
                             class="px-3 py-2 shadow-sm">

                            {{-- Nama pengirim (untuk group) --}}
                            <template x-if="msg.user_id !== {{ Auth::id() }}">
                                <div class="text-xs font-semibold text-green-700 mb-1" x-text="msg.user_name"></div>
                            </template>

                            {{-- Reply preview --}}
                            <template x-if="msg.reply_to">
                                <div class="border-l-4 border-green-500 pl-2 mb-2 text-xs text-gray-500 bg-gray-50 rounded p-1">
                                    <span x-text="msg.reply_to.body?.substring(0, 60)"></span>
                                </div>
                            </template>

                            {{-- Pesan dihapus --}}
                            <template x-if="msg.deleted_at">
                                <span class="text-gray-400 italic text-sm">🚫 Pesan dihapus</span>
                            </template>

                            {{-- Konten pesan --}}
                            <template x-if="!msg.deleted_at">
                                <div>
                                    {{-- Gambar --}}
                                    <template x-if="msg.type === 'image'">
                                        <div>
                                            <img :src="msg.thumbnail_url || msg.attachment_url"
                                                 @click="window.open(msg.attachment_url, '_blank')"
                                                 class="rounded-lg max-w-full cursor-pointer max-h-48 object-cover" />
                                        </div>
                                    </template>

                                    {{-- File --}}
                                    <template x-if="msg.type === 'file'">
                                        <a :href="msg.attachment_url" download
                                           class="flex items-center gap-2 bg-gray-50 rounded p-2 hover:bg-gray-100">
                                            <span class="text-2xl">📎</span>
                                            <div>
                                                <div class="text-sm font-medium" x-text="msg.attachment_name"></div>
                                                <div class="text-xs text-gray-400" x-text="formatSize(msg.attachment_size)"></div>
                                            </div>
                                        </a>
                                    </template>

                                    {{-- Teks --}}
                                    <template x-if="msg.type === 'text' || msg.body">
                                        <p class="text-sm whitespace-pre-wrap" x-text="msg.body"></p>
                                    </template>
                                </div>
                            </template>

                            {{-- Timestamp --}}
                            <div class="text-right mt-1">
                                <span class="text-xs text-gray-400" x-text="formatTime(msg.created_at)"></span>
                            </div>
                        </div>

                        {{-- Action buttons (hover) --}}
                        <div class="absolute top-0 right-0 hidden group-hover:flex gap-1 bg-white rounded shadow p-1 text-xs -mt-6">
                            <button @click="setReply(msg)" class="hover:text-green-600">↩ Balas</button>
                            <template x-if="msg.user_id === {{ Auth::id() }}">
                                <button @click="deleteMessage(msg.id)" class="hover:text-red-500">🗑</button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Reply preview bar --}}
        <div x-show="replyTo" class="bg-gray-50 border-t px-4 py-2 flex justify-between items-center text-sm">
            <div>
                <span class="text-green-700 font-medium">↩ Membalas: </span>
                <span class="text-gray-600" x-text="replyTo?.body?.substring(0, 80)"></span>
            </div>
            <button @click="cancelReply" class="text-gray-400 hover:text-red-500 ml-2">✕</button>
        </div>

        {{-- Attachment preview bar --}}
        <div x-show="attachmentFile" class="bg-gray-50 border-t px-4 py-2 flex items-center gap-3">
            <template x-if="attachmentPreview">
                <img :src="attachmentPreview" class="h-16 w-16 object-cover rounded" />
            </template>
            <template x-if="!attachmentPreview && attachmentFile">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">📎</span>
                    <span class="text-sm text-gray-700" x-text="attachmentFile?.name"></span>
                </div>
            </template>
            <button @click="clearAttachment" class="text-red-400 hover:text-red-600 ml-auto">Batal ✕</button>
        </div>

        {{-- Input area --}}
        <div class="bg-white border-t px-3 py-2 flex items-end gap-2">
            {{-- File picker --}}
            <label class="cursor-pointer text-gray-500 hover:text-green-600 mb-2" title="Kirim file/foto">
                📎
                <input type="file"
                       accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt"
                       class="hidden"
                       @change="handleFileSelect($event)" />
            </label>

            {{-- Text input --}}
            <textarea x-model="inputText"
                      @keydown.enter.prevent="sendMessage"
                      @input="onTyping"
                      rows="1"
                      placeholder="Tulis pesan..."
                      class="flex-1 resize-none border rounded-2xl px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500 max-h-24"
                      style="overflow-y: auto"></textarea>

            {{-- Send button --}}
            <button @click="sendMessage"
                    class="bg-[#128C7E] text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-[#075E54] flex-shrink-0 mb-0.5">
                ➤
            </button>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="!activeConvId" class="flex-1 flex items-center justify-center bg-gray-50 text-gray-400">
        <div class="text-center">
            <div class="text-6xl mb-3">💬</div>
            <p class="text-lg">Pilih percakapan untuk mulai chat</p>
            <p class="text-sm">atau klik "+ Mulai Chat" untuk chat baru</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
import { chatApp } from './chat.js';
window.chatApp = chatApp;
</script>
@endpush
```

---

### Task 8: Navigasi & Integrasi ke App Existing

**Objective:** Tambah link Chat ke menu navigasi utama

**Files:**
- Modify: `resources/views/layouts/app.blade.php` (atau layout yang ada)
- Modify: `resources/views/components/navbar.blade.php` (jika ada)

**Step 1: Tambah link di navbar**
```blade
{{-- Di dalam menu navigasi --}}
@auth
<a href="{{ route('chat.index') }}"
   class="relative flex items-center gap-1 text-sm hover:text-green-600">
    💬 Chat
    {{-- TODO: badge unread total --}}
</a>
@endauth
```

**Step 2: Link storage (untuk file uploads)**
```bash
docker compose exec -T php php artisan storage:link
```
Expected: `public/storage -> storage/app/public` created

---

### Task 9: Vite Build & Testing

**Objective:** Build aset frontend dan verifikasi semua berjalan

**Step 1: Build aset**
```bash
npm run build
```

**Step 2: Restart semua service Docker**
```bash
docker compose restart
docker compose up -d reverb
```

**Step 3: Verifikasi Reverb berjalan**
```bash
docker compose logs reverb --tail=30
# Expected: "Reverb server started on 0.0.0.0:8080"
```

**Step 4: Test WebSocket via browser console**
```js
// Di DevTools browser:
window.Echo.private('conversation.1')
  .listen('.MessageSent', (e) => console.log('Received:', e));
// Expected: no error, channel connected
```

**Step 5: Test kirim pesan**
1. Login sebagai user A → buka `/chat`
2. Klik "+ Mulai Chat" → pilih user B
3. Ketik pesan → Enter
4. Di tab/browser lain, login sebagai user B → buka `/chat`
5. Expected: pesan muncul real-time tanpa refresh

**Step 6: Test kirim foto**
1. Klik ikon 📎 → pilih gambar
2. Preview muncul di atas input
3. Klik Send
4. Expected: gambar muncul sebagai bubble

**Step 7: Test kirim dokumen**
1. Klik 📎 → pilih file PDF
2. Kirim → muncul sebagai attachment dengan ikon dan tombol download

---

## Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Reverb port 8080 tidak bisa diakses dari browser (domain HTTPS) | WebSocket gagal | Proxy via Nginx dengan upgrade header (sudah diplan di Task 1) |
| File upload besar (50MB) memakan storage | Disk penuh | Set batas upload, tambahkan cleanup job berkala |
| Thumbnail generation gagal (library image) | Foto tanpa thumbnail | Job `ProcessChatAttachment` graceful-fail, foto tetap tampil via URL asli |
| Queue tidak jalan → thumbnail tidak diproses | Foto tanpa thumbnail | Queue sudah aktif (database driver), pastikan worker jalan |
| Banyak pesan lama memperlambat load | UX buruk | Sudah pakai pagination (30 pesan/halaman), implement infinite scroll jika perlu |
| User tidak tahu ada pesan baru di tab lain | Miss notifikasi | Bisa ditambahkan browser notification (Notification API) di fase 2 |

---

## Open Questions

1. **Layout yang dipakai?** Project ini pakai `layouts.app` atau layout lain? Perlu dicek sebelum membuat `chat/index.blade.php`.
2. **Auth middleware?** Apakah semua user (student, mentor, admin) boleh chat, atau ada pembatasan role?
3. **File storage?** Saat ini local disk — apakah perlu S3 di masa depan?
4. **Notifikasi push?** Apakah perlu notifikasi saat browser ditutup (Firebase/PWA)? — scope fase 2.
5. **Enkripsi pesan?** Saat ini plain text di DB — apakah perlu E2E encryption? — scope fase 3.

---

## Urutan Eksekusi yang Direkomendasikan

```
Task 1 → Install Reverb + Docker config
    ↓
Task 2 → Migrations + Models
    ↓
Task 3 → Events + Broadcasting
    ↓
Task 4 → Controllers + Routes
    ↓
Task 5 → Job ProcessChatAttachment
    ↓
Task 6 → Frontend JS (chat.js + app.js)
    ↓
Task 7 → Blade Views (UI)
    ↓
Task 8 → Integrasi Navbar
    ↓
Task 9 → Build & Testing end-to-end
```

**Total estimasi waktu implementasi: ~3–4 jam (secara sequential)**

---

*Plan ditulis: 2026-09-25 | Study Center Nias | Laravel 13 + Reverb*
