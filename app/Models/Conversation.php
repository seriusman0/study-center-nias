<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = ['type', 'name', 'avatar', 'created_by', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['role', 'joined_at', 'last_read_at']);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Cari atau buat private conversation antara 2 user.
     */
    public static function findOrCreatePrivate(int $userA, int $userB): self
    {
        // Cari conversation private yang memiliki tepat kedua user
        $convIds = \DB::table('conversation_participants')
            ->where('user_id', $userA)
            ->pluck('conversation_id');

        $existing = self::where('type', 'private')
            ->whereIn('id', $convIds)
            ->whereHas('participants', fn($q) => $q->where('user_id', $userB))
            ->first();

        if ($existing) return $existing;

        $conv = self::create(['type' => 'private', 'created_by' => $userA]);
        $conv->participants()->attach([
            $userA => ['role' => 'member'],
            $userB => ['role' => 'member'],
        ]);

        return $conv;
    }

    public function unreadCount(int $userId): int
    {
        $participant = \DB::table('conversation_participants')
            ->where('conversation_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        if (!$participant) return 0;

        $lastRead = $participant->last_read_at;

        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->when($lastRead, fn($q) => $q->where('created_at', '>', $lastRead))
            ->count();
    }
}
