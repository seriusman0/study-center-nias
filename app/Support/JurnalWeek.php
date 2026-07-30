<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class JurnalWeek
{
    public const TZ = 'Asia/Jakarta';

    public static function current(?CarbonInterface $date = null): array
    {
        $d = $date ? $date->copy()->setTimezone(self::TZ) : Carbon::now(self::TZ);
        $week = (int) floor(((int) $d->day - 1) / 7) + 1;
        $week = min(4, $week);

        return [
            'tahun'  => (int) $d->year,
            'bulan'  => (int) $d->month,
            'minggu' => $week,
            'key'    => sprintf('%04d-%02d-%d', $d->year, $d->month, $week),
        ];
    }

    public static function weekKeyFor(string|CarbonInterface $date): string
    {
        $d = $date instanceof CarbonInterface
            ? $date->copy()->setTimezone(self::TZ)
            : Carbon::parse($date, self::TZ);

        // Week starts Sunday, ends Saturday — use that Sunday's date as the key.
        return $d->copy()->startOfWeek(CarbonInterface::SUNDAY)->format('Y-m-d');
    }

    public static function today(): Carbon
    {
        return Carbon::today(self::TZ);
    }

    public static function parseDate(string $date): Carbon
    {
        return Carbon::parse($date, self::TZ)->startOfDay();
    }
}
