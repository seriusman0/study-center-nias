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
use Illuminate\Support\Facades\Storage;
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
            'checked'   => 'nullable|boolean',
            'verse_ref' => 'nullable|string|max:100',
        ]);

        $date = isset($data['date'])
            ? Carbon::parse($data['date'], JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();
        $today = JurnalWeek::today();
        if ($date->greaterThan($today)) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

        $type = $data['item_type'];
        $checked = (bool) ($data['checked'] ?? false);

        DB::transaction(function () use ($user, $date, $type, $checked, $data) {
            $entry = JurnalEntry::whereDate('tanggal', $date->toDateString())
                ->where('student_id', $user->id)
                ->first();
            if (! $entry) {
                $entry = JurnalEntry::create([
                    'student_id' => $user->id,
                    'tanggal'    => $date->toDateString(),
                    'cabang_id'  => $user->cabang_id,
                ]);
            }

            switch ($type) {
                case 'pl':
                    $entry->update(['pl_checked' => $checked]); break;
                case 'pb':
                    $entry->update(['pb_checked' => $checked]); break;
                case 'verse':
                    $verseRef = $data['verse_ref'] ?? null;
                    $key = JurnalWeek::weekKeyFor($date);
                    if ($verseRef) {
                        $weekEntry = JurnalEntry::where('student_id', $user->id)
                            ->where('verse_week_key', $key)
                            ->first();
                        if ($weekEntry) {
                            $weekEntry->update(['verse_ref' => $verseRef]);
                        } else {
                            $entry->update(['verse_week_key' => $key, 'verse_ref' => $verseRef]);
                        }
                    } else {
                        JurnalEntry::where('student_id', $user->id)
                            ->where('verse_week_key', $key)
                            ->update(['verse_week_key' => null, 'verse_ref' => null]);
                    }
                    break;
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
            ->whereDate('tanggal', '>=', $from->toDateString())
            ->whereDate('tanggal', '<=', $to->toDateString())
            ->get()
            ->keyBy(fn($e) => $e->tanggal->toDateString());

        $checks = JurnalLifeCheck::forStudent($user->id)
            ->whereDate('tanggal', '>=', $from->toDateString())
            ->whereDate('tanggal', '<=', $to->toDateString())
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
        $config     = \App\Models\CollegeConfig::current();
        $dayNo      = $config->dayNoFor($date);
        $scheduleId = $user->cabang?->bible_schedule_id ?? $config->active_schedule_id;
        $bibleItem  = \App\Models\CollegeBibleItem::forDayNo($dayNo, $scheduleId);

        $weekMeta = JurnalWeek::current($date);
        $weekKey  = JurnalWeek::weekKeyFor($date);

        $entry = JurnalEntry::forStudent($user->id)->whereDate('tanggal', $date->toDateString())->first();

        $verseEntry = JurnalEntry::forStudent($user->id)
            ->where('verse_week_key', $weekKey)
            ->whereNotNull('verse_ref')
            ->first();
        $verseRef = $verseEntry?->verse_ref;

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
                'day_no'     => $dayNo,
                'pl_porsi'   => $bibleItem?->pl_text,
                'pb_porsi'   => $bibleItem?->pb_text,
                'pl_checked' => (bool) ($entry?->pl_checked),
                'pb_checked' => (bool) ($entry?->pb_checked),
            ],
            'verse_ref' => $verseRef,
            'foto_belajar_url' => $entry?->foto_belajar ? asset('storage/' . $entry->foto_belajar) : null,
            'life_items' => $items->map(fn($it) => [
                'id'            => $it->id,
                'kategori'      => $it->kategori,
                'label'         => $it->label,
                'response_type' => $it->response_type,
                'checked'       => in_array($it->id, $checkedIds),
            ])->values(),
        ];
    }

    public function uploadFoto(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'foto'  => 'required|file|mimes:jpeg,jpg,png,webp|max:4096',
            'date'  => 'nullable|date',
        ]);

        $date  = isset($request->date)
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();

        if ($date->greaterThan(JurnalWeek::today())) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

        $entry = JurnalEntry::where('student_id', $user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->first();

        if (!$entry) {
            $entry = JurnalEntry::create([
                'student_id' => $user->id,
                'tanggal'    => $date->toDateString(),
                'cabang_id'  => $user->cabang_id,
            ]);
        }

        if ($entry->foto_belajar) {
            Storage::disk('public')->delete($entry->foto_belajar);
        }

        $path = $request->file('foto')->store(
            'jurnal-foto/' . $date->format('Y/m'),
            'public'
        );

        $entry->update(['foto_belajar' => $path]);

        return response()->json([
            'ok'  => true,
            'url' => asset('storage/' . $path),
            'state' => $this->snapshot($user, $date)
        ]);
    }

    public function deleteFoto(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate(['date' => 'nullable|date']);

        $date = isset($data['date'])
            ? Carbon::parse($data['date'], JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();

        $entry = JurnalEntry::where('student_id', $user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->first();

        if ($entry && $entry->foto_belajar) {
            Storage::disk('public')->delete($entry->foto_belajar);
            $entry->update(['foto_belajar' => null]);
        }

        return response()->json([
            'ok' => true,
            'state' => $this->snapshot($user, $date)
        ]);
    }
}
