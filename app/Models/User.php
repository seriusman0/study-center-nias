<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'google_id', 'name', 'username', 'email', 'password',
        'avatar', 'bio', 'role_id', 'cabang_id',
        'is_active', 'profile_public', 'cv_enabled',
        'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'profile_public' => 'boolean',
            'cv_enabled' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function socialLinks()
    {
        return $this->hasMany(UserSocialLink::class);
    }

    public function cvData()
    {
        return $this->hasOne(CvData::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role?->name === $roles;
        }
        return in_array($this->role?->name, $roles);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
