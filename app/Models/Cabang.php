<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $fillable = ['nama', 'slug', 'alamat', 'kontak', 'foto_wajib', 'pendaftaran_buka', 'whatsapp_link'];

    protected $casts = ['foto_wajib' => 'boolean', 'pendaftaran_buka' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
