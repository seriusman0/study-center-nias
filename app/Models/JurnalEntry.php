<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JurnalEntry extends Model
{
    protected $table = 'jurnal_entries';

    protected $fillable = [
        'student_id', 'cabang_id', 'tanggal',
        'pl_checked', 'pb_checked', 'verse_week_key',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'pl_checked'  => 'boolean',
        'pb_checked'  => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function scopeForStudent(Builder $q, int $studentId): Builder
    {
        return $q->where('student_id', $studentId);
    }

    public function scopeForDate(Builder $q, string $date): Builder
    {
        return $q->whereDate('tanggal', $date);
    }

    public function scopeBetween(Builder $q, string $from, string $to): Builder
    {
        return $q->whereBetween('tanggal', [$from, $to]);
    }
}
