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
        'kategori', 'reset_period', 'response_type', 'label', 'is_default', 'student_id',
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

    /**
     * Scope untuk mengambil items yang boleh dilihat/diisi oleh seorang student.
     * Mencakup:
     *  - Item global template (student_id IS NULL), dikecualikan kategori 'prajurit'
     *    karena prajurit-only items tidak relevan untuk student/college/scholarship.
     *    Prajurit items disaring di level controller via whereIn('kategori', [...]).
     *  - Item khusus milik student ini (student_id = $studentId)
     *  - Item yang di-assign eksplisit via pivot jurnal_student_life_items
     */
    public function scopeForStudent(Builder $q, int $studentId): Builder
    {
        return $q->where('is_active', true)
            ->where(function ($w) use ($studentId) {
                // Item global (template untuk semua siswa), kecuali kategori prajurit
                $w->where(function ($g) {
                    $g->whereNull('student_id')
                      ->where('kategori', '!=', 'prajurit');
                })
                    // Item khusus milik student ini
                    ->orWhere('student_id', $studentId)
                    // Item yang secara eksplisit di-assign ke student ini via pivot
                    ->orWhereHas('assignedStudents', fn($a) => $a->where('users.id', $studentId));
            });
    }

    /**
     * Scope untuk prajurit — hanya item kategori 'prajurit' (global + milik user).
     */
    public function scopeForPrajurit(Builder $q, int $studentId): Builder
    {
        return $q->where('is_active', true)
            ->where(function ($w) use ($studentId) {
                $w->where(function ($g) {
                    $g->whereNull('student_id')
                      ->where('kategori', 'prajurit');
                })
                    ->orWhere('student_id', $studentId)
                    ->orWhereHas('assignedStudents', fn($a) => $a->where('users.id', $studentId));
            });
    }
}
