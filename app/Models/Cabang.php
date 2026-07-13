<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $fillable = ['nama', 'slug', 'alamat', 'kontak', 'foto_wajib', 'pendaftaran_buka', 'whatsapp_link', 'kelas_min', 'kelas_max', 'mata_pelajaran'];

    protected $casts = ['foto_wajib' => 'boolean', 'pendaftaran_buka' => 'boolean', 'mata_pelajaran' => 'array'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
