<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    protected $fillable = ['user_id', 'employee_number', 'department', 'position'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
