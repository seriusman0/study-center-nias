<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id', 'student_number', 'birth_date', 'birth_place', 'gender',
        'address', 'guardian_name', 'guardian_phone', 'school_name', 'grade_class', 'entry_year',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
