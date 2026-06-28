<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalBibleSchedule;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\JurnalWeeklyVerse;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalApiController extends Controller
{
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $date = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();

        return response()->json($this->snapshot($user, $date));
    }

    public function check(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'item_type' => 'required|in:pl,pb,verse,life',
            'item_id'   => 'nullable|integer',
            'date'      => 'nullable|date',
            'checked'   => 'required|boolean',
        ]);

        $date = isset($data['date'])
            ? Carbon::parse($data['date'], JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();
        $today = JurnalWeek::today();
        if ($date->greaterThan($today)) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

        $type = $data['item_type'];
        $checked = (bool) $data['checked'];

        DB::transaction(function () use ($user, $date, $type, $checked, $data) {
            $entry = JurnalEntry::firstOrCreate(
                ['student_id' => $user->id, 'tanggal' => $date->toDateString()],
                ['cabang_id'  => $user->cabang_id]
            );

            switch ($type) {
                case 'pl':
                    $entry->update(['pl_checked' => $checked]); break;
                case 'pb':
                    $entry->update(['pb_checked' => $checked]); break;
                case 'verse':
                    $entry->update(['verse_week_key' => $checked ? JurnalWeek::weekKeyFor($date) : null]); break;
                case 'life':
                    $itemId = (int) ($data['item_id'] ?? 0);
                    abort_if($itemId === 0, 422, 'item_id wajib untuk tipe life.');
                    if ($checked) {
                        JurnalLifeCheck::updateOrCreate(
                            ['student_id' => $user->id, 'life_item_id' => $itemId, 'tanggal' => $date->toDateString()],
                            ['checked' => true]
                        );
                    } else {
                        JurnalLifeCheck::where('student_id', $user->id)
                            ->where('life_item_id', $itemId)
                            ->whereDate('tanggal', $date->toDateString())
                            ->delete();
                    }
                    break;
            }
        });

        return response()->json(['ok' => true, 'state' => $this->snapshot($user, $date)]);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $from = Carbon::parse($data['from'], JurnalWeek::TZ)->startOfDay();
        $to   = Carbon::parse($data['to'], JurnalWeek::TZ)->startOfDay();

        $entries = JurnalEntry::forStudent($user->id)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn($e) => $e->tanggal->toDateString());

        $checks = JurnalLifeCheck::forStudent($user->id)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->where('checked', true)
            ->get()
            ->groupBy(fn($c) => $c->tanggal->toDateString());

        $days = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key = $d->toDateString();
            $entry = $entries->get($key);
            $weekKey = JurnalWeek::weekKeyFor($d);
            $verseChecked = $entries->contains(fn($e) => $e->verse_week_key === $weekKey);
            $days[] = [
                'date'          => $key,
                'pl_checked'    => (bool) ($entry?->pl_checked),
                'pb_checked'    => (bool) ($entry?->pb_checked),
                'verse_checked' => (bool) $verseChecked,
                'life_checked_ids' => ($checks->get($key) ?? collect())->pluck('life_item_id')->all(),
            ];
        }

        return response()->json(['data' => $days]);
    }

    private function snapshot($user, Carbon $date): array
    {
        $schedule = JurnalBibleSchedule::forDate($date);
        $weekMeta = JurnalWeek::current($date);
        $verse = JurnalWeeklyVerse::forWeek($weekMeta['tahun'], $weekMeta['bulan'], $weekMeta['minggu']);
        $entry = JurnalEntry::forStudent($user->id)->whereDate('tanggal', $date->toDateString())->first();
        $verseChecked = JurnalEntry::forStudent($user->id)
            ->where('verse_week_key', $weekMeta['key'])
            ->exists();

        $items = JurnalLifeItem::forStudent($user->id)
            ->orderBy('kategori')->orderBy('label')->get();
        $itemIds = $items->pluck('id');
        $checkedIds = JurnalLifeCheck::forStudent($user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->where('checked', true)
            ->pluck('life_item_id')
            ->all();

        return [
            'date' => $date->toDateString(),
            'week' => $weekMeta,
            'bible' => [
                'pl_porsi'   => $schedule?->pl_porsi,
                'pb_porsi'   => $schedule?->pb_porsi,
                'pl_checked' => (bool) ($entry?->pl_checked),
                'pb_checked' => (bool) ($entry?->pb_checked),
            ],
            'verse' => $verse ? [
                'referensi' => $verse->referensi,
                'isi'       => $verse->isi,
                'checked'   => (bool) $verseChecked,
            ] : null,
            'life_items' => $items->map(fn($it) => [
                'id'       => $it->id,
                'kategori' => $it->kategori,
                'label'    => $it->label,
                'checked'  => in_array($it->id, $checkedIds),
            ])->values(),
        ];
    }
}
