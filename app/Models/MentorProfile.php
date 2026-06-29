<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorProfile extends Model
{
    protected $fillable = [
        'user_id', 'expertise', 'bio', 'education', 'experience_years',
        'hourly_rate', 'is_available',
    ];

    protected $casts = [
        'is_available'  => 'boolean',
        'hourly_rate'   => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
