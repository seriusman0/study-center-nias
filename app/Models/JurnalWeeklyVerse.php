<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalWeeklyVerse extends Model
{
    protected $table = 'jurnal_weekly_verses';

    protected $fillable = ['tahun', 'bulan', 'minggu', 'referensi', 'isi', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function forWeek(int $tahun, int $bulan, int $minggu): ?self
    {
        return self::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('minggu', $minggu)
            ->first();
    }
}
