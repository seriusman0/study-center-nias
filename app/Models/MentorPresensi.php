<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MentorPresensi extends Model
{
    protected $table = 'mentor_presensi';

    protected $fillable = [
        'mentor_id', 'cabang_id', 'kelas_id',
        'tanggal', 'jam_datang', 'jam_pulang',
        'jumlah_murid', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function kelas()
    {
        return $this->belongsTo(KelasMaster::class, 'kelas_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function getDurasiMenitAttribute(): int
    {
        try {
            $start = Carbon::parse($this->jam_datang);
            $end = Carbon::parse($this->jam_pulang);
            return max(0, $start->diffInMinutes($end));
        } catch (\Throwable) {
            return 0;
        }
    }

    public function canEdit(): bool
    {
        if (! $this->created_at) return true;
        return $this->created_at->gte(now()->subHours(24));
    }

    public function scopeForMentor(Builder $q, int $mentorId): Builder
    {
        return $q->where('mentor_id', $mentorId);
    }

    public function scopeBetween(Builder $q, string $from, string $to): Builder
    {
        return $q->whereBetween('tanggal', [$from, $to]);
    }
}
