<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $fillable = ['nama', 'slug', 'alamat', 'kontak', 'foto_wajib', 'pendaftaran_buka', 'whatsapp_link', 'kelas_min', 'kelas_max', 'mata_pelajaran', 'bible_schedule_id'];

    protected $casts = ['foto_wajib' => 'boolean', 'pendaftaran_buka' => 'boolean', 'mata_pelajaran' => 'array'];

    public function bibleSchedule()
    {
        return $this->belongsTo(\App\Models\CollegeBibleSchedule::class, 'bible_schedule_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
