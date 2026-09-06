<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        "admin_user_id", "type", "message", "data", "read_at",
    ];

    protected $casts = [
        "data"    => "array",
        "read_at" => "datetime",
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, "admin_user_id");
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull("read_at");
    }
}
