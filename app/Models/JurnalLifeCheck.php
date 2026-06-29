<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JurnalLifeCheck extends Model
{
    protected $table = 'jurnal_life_checks';

    public $incrementing = false;
    protected $primaryKey = null;

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
        'checked' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function item()
    {
        return $this->belongsTo(JurnalLifeItem::class, 'life_item_id');
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
