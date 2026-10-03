<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectedEmail extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'invited_at',
        'invited_by',
        'invite_note',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
    ];

    // ── Relasi ───────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitedByUser()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // ── Accessor / Helper ────────────────────────────
    /** Sudah ditandai diundang ke Google Play Tester? */
    public function isInvited(): bool
    {
        return !is_null($this->invited_at);
    }
}
