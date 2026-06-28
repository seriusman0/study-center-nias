<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'mentor_id', 'cabang_id', 'created_by',
        'kelas', 'kelas_id', 'tanggal', 'jam_mulai', 'jam_selesai',
        'materi', 'foto',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function kelasMaster()
    {
        return $this->belongsTo(KelasMaster::class, 'kelas_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'presensi_students', 'presensi_id', 'user_id')
            ->withPivot(['status', 'keterangan']);
    }
}
