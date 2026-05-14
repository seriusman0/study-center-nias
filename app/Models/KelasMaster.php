<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelasMaster extends Model
{
    use SoftDeletes;

    protected $table = 'kelas_master';

    protected $fillable = ['nama', 'cabang_id', 'keterangan', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function mentorPresensi()
    {
        return $this->hasMany(MentorPresensi::class, 'kelas_id');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'kelas_id');
    }

    public function scopeForCabang(Builder $q, ?int $cabangId): Builder
    {
        return $cabangId ? $q->where('cabang_id', $cabangId) : $q;
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        $cab = $this->relationLoaded('cabang') ? $this->cabang?->nama : null;
        return $cab ? "{$this->nama} — {$cab}" : $this->nama;
    }
}
