<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Support\JurnalWeek;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $studentId = $user->id;
        $tz        = JurnalWeek::TZ;

        // Total life items assigned to user
        $totalItems = JurnalLifeItem::forStudent($studentId)->count();

        // Build 8-week windows (most recent first)
        $weeks = [];
        $today = Carbon::today($tz);

        for ($i = 0; $i < 8; $i++) {
            $sunday   = $today->copy()->subWeeks($i)->startOfWeek(Carbon::SUNDAY);
            $saturday = $sunday->copy()->addDays(6);

            $weekKey  = $sunday->format('Y-m-d');
            $label    = $sunday->translatedFormat('d M') . ' – ' . $saturday->translatedFormat('d M');

            // Count distinct life checks in this week (checked=true)
            $checks = JurnalLifeCheck::where('student_id', $studentId)
                ->where('checked', true)
                ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                ->count();

            // Count entry-based items (Baca Alkitab PL/PB) within the week
            $entryDays = JurnalEntry::where('student_id', $studentId)
                ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                ->where(fn($q) => $q->where('pl_checked', true)->orWhere('pb_checked', true))
                ->count();

            // Active days: any check or entry that week
            $activeDays = JurnalEntry::where('student_id', $studentId)
                ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                ->count()
                + JurnalLifeCheck::where('student_id', $studentId)
                    ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                    ->where('checked', true)
                    ->distinct('tanggal')
                    ->count('tanggal');

            $totalChecks = $checks + $entryDays;
            $maxChecks   = $totalItems > 0 ? $totalItems * 7 : 1;
            $pct         = min(100, round($totalChecks / max($maxChecks, 1) * 100));

            $weeks[] = [
                'label'      => $label,
                'week_key'   => $weekKey,
                'checks'     => $totalChecks,
                'max'        => $maxChecks,
                'pct'        => $pct,
                'active_days'=> min(7, $activeDays),
            ];
        }

        // Summary
        $totalActiveDays = JurnalEntry::where('student_id', $studentId)
            ->selectRaw('COUNT(DISTINCT tanggal) as cnt')
            ->value('cnt') ?? 0;

        $totalCheckDays = JurnalLifeCheck::where('student_id', $studentId)
            ->where('checked', true)
            ->selectRaw('COUNT(DISTINCT tanggal) as cnt')
            ->value('cnt') ?? 0;

        $overallActiveDays = max($totalActiveDays, $totalCheckDays);

        // Best week
        $bestWeek = collect($weeks)->sortByDesc('pct')->first();

        return view('laporan.index', compact('weeks', 'totalItems', 'overallActiveDays', 'bestWeek', 'user'));
    }
}
