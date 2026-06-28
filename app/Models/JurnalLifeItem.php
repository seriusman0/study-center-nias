<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JurnalLifeItem extends Model
{
    use SoftDeletes;

    protected $table = 'jurnal_life_items';

    protected $fillable = [
        'kategori', 'label', 'is_default', 'student_id',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedStudents()
    {
        return $this->belongsToMany(User::class, 'jurnal_student_life_items', 'life_item_id', 'student_id')
            ->withTimestamps();
    }

    public function scopeTemplate(Builder $q): Builder
    {
        return $q->whereNull('student_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeForStudent(Builder $q, int $studentId): Builder
    {
        return $q->where('is_active', true)
            ->where(function ($w) use ($studentId) {
                $w->where('student_id', $studentId)
                    ->orWhereHas('assignedStudents', fn($a) => $a->where('users.id', $studentId));
            });
    }
}
