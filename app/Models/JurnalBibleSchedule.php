<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class JurnalBibleSchedule extends Model
{
    protected $table = 'jurnal_bible_schedules';

    protected $fillable = ['tanggal', 'pl_porsi', 'pb_porsi', 'created_by'];

    protected $casts = ['tanggal' => 'date'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function forDate(string|CarbonInterface $date): ?self
    {
        $d = $date instanceof CarbonInterface ? $date->toDateString() : $date;
        return self::whereDate('tanggal', $d)->first();
    }
}
