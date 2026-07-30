<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeBibleSchedule extends Model
{
    protected $table = 'college_bible_schedules';

    protected $fillable = ['name', 'description'];

    public function items()
    {
        return $this->hasMany(CollegeBibleItem::class, 'schedule_id');
    }
}
