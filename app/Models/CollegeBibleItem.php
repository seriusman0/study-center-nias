<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeBibleItem extends Model
{
    protected $table = 'college_bible_items';

    protected $fillable = ['day_no', 'pl_text', 'pb_text'];

    public static function forDayNo(int $dayNo): ?self
    {
        return self::where('day_no', $dayNo)->first();
    }
}
