<?php

namespace App\Models;

use Carbon\Carbon;
use App\Support\JurnalWeek;
use Illuminate\Database\Eloquent\Model;

class CollegeConfig extends Model
{
    protected $table = 'college_bible_config';

    protected $fillable = [
        'active_schedule_id',
        'anchor_day_no', 'anchor_date',
        'form_open_time', 'form_close_time',
        'updated_by',
    ];

    protected $casts = [
        'anchor_date' => 'date',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'anchor_day_no'   => 187,
            'anchor_date'     => '2026-07-06',
            'form_open_time'  => '06:00:00',
            'form_close_time' => '23:59:00',
        ]);
    }

    public function todayDayNo(): int
    {
        return $this->dayNoFor(JurnalWeek::today());
    }

    public function dayNoFor(Carbon $date): int
    {
        $anchorStr = $this->anchor_date instanceof Carbon
            ? $this->anchor_date->toDateString()
            : (string) $this->anchor_date;

        $anchor = Carbon::createFromFormat('Y-m-d', $anchorStr)->startOfDay();
        $target = Carbon::createFromFormat('Y-m-d', $date->toDateString())->startOfDay();
        $diff   = $anchor->diffInDays($target, false);
        // diffInDays($other, false): negative if $other < $this, positive if $other > $this
        $dayNo  = (($this->anchor_day_no - 1 + $diff) % 366) + 1;
        return $dayNo < 1 ? $dayNo + 366 : $dayNo;
    }

    public function isFormOpen(): bool
    {
        $now   = Carbon::now(JurnalWeek::TZ);
        $open  = Carbon::createFromTimeString($this->form_open_time, JurnalWeek::TZ);
        $close = Carbon::createFromTimeString($this->form_close_time, JurnalWeek::TZ);
        return $now->between($open, $close);
    }

    public function activeSchedule()
    {
        return $this->belongsTo(CollegeBibleSchedule::class, 'active_schedule_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
