<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id', 'user_id', 'reply_to_id', 'type',
        'body', 'attachment_path', 'attachment_name', 'attachment_mime',
        'attachment_size', 'thumbnail_path',
    ];

    protected $appends = ['attachment_url', 'thumbnail_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) return null;
        // Selalu pakai HTTPS agar tidak ada mixed content warning
        return 'https://studycenter.nanoprojectdevindonesia.com/storage/' . $this->attachment_path;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail_path) return null;
        return 'https://studycenter.nanoprojectdevindonesia.com/storage/' . $this->thumbnail_path;
    }
}
