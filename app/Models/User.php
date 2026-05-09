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
        'avatar', 'bio', 'cabang_id',
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
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

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function mentorProfile()
    {
        return $this->hasOne(MentorProfile::class);
    }

    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function hasPermission(string $name): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $name))
            ->exists();
    }

    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name')->all();
    }

    public function getPrimaryRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }
}
