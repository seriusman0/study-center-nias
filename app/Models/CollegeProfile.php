<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeProfile extends Model
{
    protected $fillable = ['user_id', 'institution_name', 'position'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
