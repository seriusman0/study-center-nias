<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $fillable = ['nama', 'slug', 'alamat', 'kontak', 'foto_wajib'];

    protected $casts = ['foto_wajib' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
