<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id', 'student_number', 'birth_date', 'birth_place', 'gender',
        'address', 'guardian_name', 'guardian_phone', 'student_phone', 'school_name', 'grade_class', 'entry_year',
        'campus_name', 'current_semester', 'photo', 'note', 'is_pending', 'status', 'catatan_admin',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getGuardianWhatsappAttribute(): ?string
    {
        if (! $this->guardian_phone) return null;
        return 'https://wa.me/' . ltrim($this->guardian_phone, '+');
    }

    public function getStudentWhatsappAttribute(): ?string
    {
        if (! $this->student_phone) return null;
        return 'https://wa.me/' . ltrim($this->student_phone, '+');
    }
}
