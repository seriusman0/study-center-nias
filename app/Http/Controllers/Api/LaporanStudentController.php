<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Admin\JurnalReportController;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaporanStudentController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $student = $request->user();
        $today   = JurnalWeek::today();
        $from    = $today->copy()->subDays(29);

        $matrix = $this->buildMatrix($student, $from, $today);

        $streak = $this->computeStreak($student, $today);

        return response()->json([
            'from'    => $from->toDateString(),
            'to'      => $today->toDateString(),
            'pct'     => $matrix['pct'],
            'checked' => $matrix['checked'],
            'total'   => $matrix['total'],
            'streak'  => $streak,
        ]);
    }

    public function matrix(Request $request): JsonResponse
    {
        $student = $request->user();
        $today   = JurnalWeek::today();

        $from = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(13);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();
        if ($to->gt($today)) $to = $today->copy();

        $matrix = $this->buildMatrix($student, $from, $to);

        return response()->json([
            'from'    => $from->toDateString(),
            'to'      => $to->toDateString(),
            'headers' => $matrix['headers'],
            'rows'    => $matrix['rows'],
            'pct'     => $matrix['pct'],
            'checked' => $matrix['checked'],
            'total'   => $matrix['total'],
        ]);
    }

    private function buildMatrix($student, Carbon $from, Carbon $to): array
    {
        $items = JurnalLifeItem::forStudent($student->id)
            ->orderBy('kategori')->orderBy('label')->get();

        $entries = JurnalEntry::forStudent($student->id)
            ->whereDate('tanggal', '>=', $from->toDateString())
            ->whereDate('tanggal', '<=', $to->toDateString())
            ->get()->keyBy(fn($e) => $e->tanggal->toDateString());

        $checks = JurnalLifeCheck::forStudent($student->id)
            ->whereDate('tanggal', '>=', $from->toDateString())
            ->whereDate('tanggal', '<=', $to->toDateString())
            ->where('checked', true)
            ->get()->groupBy(fn($c) => $c->tanggal->toDateString());

        $headers = ['Tanggal', 'PL', 'PB', 'Hafal Ayat'];
        foreach ($items as $it) {
            $headers[] = ucfirst($it->kategori) . ': ' . $it->label;
        }

        $rows = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key   = $d->toDateString();
            $entry = $entries->get($key);
            $weekKey = JurnalWeek::weekKeyFor($d);
            $verseChecked = $entries->contains(fn($e) => $e->verse_week_key === $weekKey);

            $row = [
                $key,
                $entry?->pl_checked ? 'Y' : '-',
                $entry?->pb_checked ? 'Y' : '-',
                $verseChecked ? 'Y' : '-',
            ];
            $checkedIds = ($checks->get($key) ?? collect())->pluck('life_item_id')->all();
            foreach ($items as $it) {
                $row[] = in_array($it->id, $checkedIds) ? 'Y' : '-';
            }
            $rows[] = $row;
        }

        $totalCells = count($rows) * (count($headers) - 1);
        $checked    = 0;
        foreach ($rows as $r) {
            for ($i = 1; $i < count($r); $i++) {
                if ($r[$i] === 'Y') $checked++;
            }
        }
        $pct = $totalCells > 0 ? round($checked / $totalCells * 100, 1) : 0.0;

        return compact('headers', 'rows', 'pct', 'checked') + ['total' => $totalCells];
    }

    private function computeStreak($student, Carbon $today): int
    {
        $streak = 0;
        $d      = $today->copy();

        while (true) {
            $key   = $d->toDateString();
            $entry = JurnalEntry::forStudent($student->id)->whereDate('tanggal', $key)->first();

            $hasAny = $entry && ($entry->pl_checked || $entry->pb_checked);
            if (!$hasAny) break;

            $streak++;
            $d->subDay();
        }

        return $streak;
    }
}
