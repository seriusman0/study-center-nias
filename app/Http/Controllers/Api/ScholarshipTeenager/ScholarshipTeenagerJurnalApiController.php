<?php

namespace App\Http\Controllers\Api\ScholarshipTeenager;

use App\Http\Controllers\Controller;
use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Self-service API untuk role scholarship_teenager — jurnal harian scholarship_teenager.
 *
 * Mirip JurnalApiController (student) tapi dengan response_type life item
 * (check / boolean) + support verse_ref dan verse_checked + form window config.
 */
class ScholarshipTeenagerJurnalApiController extends Controller
{
    /** Life item kategori khusus scholarship_teenager */
    protected const KATEGORI = ['pembacaan', 'sidang', 'rohani'];

    /** API: snapshot hari ini / tanggal tertentu untuk scholarship_teenager */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $date = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();

        return response()->json($this->snapshot($user, $date));
    }

    /** API: check-toggle life item / bible / verse */
    public function check(Request $request): JsonResponse
    {
        $user   = $request->user();
        $config = CollegeConfig::current();
        $data   = $request->validate([
            'item_type'     => 'required|in:pl,pb,life,verse,verse_check',
            'item_id'       => 'nullable|integer',
            'date'          => 'nullable|date',
            'checked'       => 'nullable|boolean',
            'verse_ref'     => 'nullable|string|max:100',
        ]);

        $date = isset($data['date'])
            ? Carbon::parse($data['date'], JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();

        $today = JurnalWeek::today();
        if ($date->greaterThan($today)) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

        // Form-window enforcement (hanya untuk hari ini)
        if ($date->isSameDay($today) && !$config->isFormOpen()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Form jurnal hanya bisa diisi pukul '
                    . substr($config->form_open_time, 0, 5) . '-'
                    . substr($config->form_close_time, 0, 5) . '.',
            ], 403);
        }

        $type    = $data['item_type'];
        $checked = (bool) ($data['checked'] ?? false);

        DB::transaction(function () use ($user, $date, $type, $checked, $data) {
            if (in_array($type, ['pl', 'pb'])) {
                $entry = JurnalEntry::firstOrCreate(
                    ['student_id' => $user->id, 'tanggal' => $date->toDateString()],
                    ['cabang_id'  => $user->cabang_id]
                );
                $entry->update([$type . '_checked' => $checked]);
                return;
            }

            if ($type === 'life') {
                $itemId  = (int) ($data['item_id'] ?? 0);
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
                return;
            }

            if ($type === 'verse') {
                // Simpan/hapus teks ayat hafalan untuk minggu ini (per-minggu)
                $verseRef = $data['verse_ref'] ?? null;
                $key = JurnalWeek::weekKeyFor($date);

                $entry = JurnalEntry::firstOrCreate(
                    ['student_id' => $user->id, 'tanggal' => $date->toDateString()],
                    ['cabang_id'  => $user->cabang_id]
                );

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
                    // Jika hapus verse_ref, reset semua verse_checked minggu ini
                    JurnalEntry::where('student_id', $user->id)
                        ->whereBetween('tanggal', [
                            Carbon::parse($key)->startOfWeek(\Carbon\CarbonInterface::SUNDAY)->toDateString(),
                            Carbon::parse($key)->endOfWeek(\Carbon\CarbonInterface::SATURDAY)->toDateString(),
                        ])
                        ->update(['verse_checked' => false]);
                }
                return;
            }

            if ($type === 'verse_check') {
                // Centang/uncentang hafalan ayat per-hari
                $key = JurnalWeek::weekKeyFor($date);
                $hasVerse = JurnalEntry::where('student_id', $user->id)
                    ->where('verse_week_key', $key)
                    ->whereNotNull('verse_ref')
                    ->exists();
                if (!$hasVerse && $checked) {
                    // Tidak bisa centang kalau belum ada teks ayat
                    return;
                }
                $entry = JurnalEntry::firstOrCreate(
                    ['student_id' => $user->id, 'tanggal' => $date->toDateString()],
                    ['cabang_id'  => $user->cabang_id]
                );
                $entry->update(['verse_checked' => $checked]);
                return;
            }
        });

        return response()->json(['ok' => true, 'state' => $this->snapshot($user, $date)]);
    }

    /** API: history scholarship_teenager */
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
            $key       = $d->toDateString();
            $entry     = $entries->get($key);
            $weekKey   = JurnalWeek::weekKeyFor($d);
            $dayChecks = ($checks->get($key) ?? collect())->pluck('life_item_id')->all();

            // verse_checked per-hari dari entry
            $verseChecked = (bool) ($entry?->verse_checked);

            $days[] = [
                'date'             => $key,
                'pl_checked'       => (bool) ($entry?->pl_checked),
                'pb_checked'       => (bool) ($entry?->pb_checked),
                'verse_checked'    => $verseChecked,
                'life_checked_ids' => $dayChecks,
            ];
        }

        return response()->json(['data' => $days]);
    }

    /** API: upload foto belajar */
    public function uploadFoto(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'foto' => 'required|file|mimes:jpeg,jpg,png,webp|max:4096',
            'date' => 'nullable|date',
        ]);

        $date = isset($request->date)
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();

        if ($date->greaterThan(JurnalWeek::today())) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

        $entry = JurnalEntry::firstOrCreate(
            ['student_id' => $user->id, 'tanggal' => $date->toDateString()],
            ['cabang_id'  => $user->cabang_id]
        );

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
            'state' => $this->snapshot($user, $date),
        ]);
    }

    /** API: delete foto belajar */
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

        return response()->json(['ok' => true, 'state' => $this->snapshot($user, $date)]);
    }

    // ── Internal: build snapshot for scholarship_teenager ────────────────────────────────
    private function snapshot($user, Carbon $date): array
    {
        $config = CollegeConfig::current();
        $dayNo  = $config->dayNoFor($date);

        $scheduleId = $user->cabang?->bible_schedule_id ?? $config->active_schedule_id;
        $bibleItem  = CollegeBibleItem::forDayNo($dayNo, $scheduleId);

        $weekMeta = JurnalWeek::current($date);
        $weekKey  = JurnalWeek::weekKeyFor($date);

        $entry = JurnalEntry::forStudent($user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->first();

        // verse_ref: dari entry yang punya verse_week_key untuk minggu ini
        $verseEntry = JurnalEntry::forStudent($user->id)
            ->where('verse_week_key', $weekKey)
            ->whereNotNull('verse_ref')
            ->first();
        $verseRef = $verseEntry?->verse_ref;

        // verse_checked: centang per-hari
        $verseChecked = (bool) ($entry?->verse_checked);

        $items = JurnalLifeItem::forStudent($user->id)
            ->whereIn('kategori', self::KATEGORI)
            ->orderBy('kategori')
            ->orderBy('label')
            ->get();

        $itemIds = $items->pluck('id');
        $checkedIds = JurnalLifeCheck::forStudent($user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->where('checked', true)
            ->pluck('life_item_id')
            ->all();

        $streak = 0;
        $cursor = JurnalWeek::today()->copy();
        for ($i = 0; $i < 60; $i++) {
            $d = $cursor->toDateString();
            $has = JurnalEntry::forStudent($user->id)->whereDate('tanggal', $d)
                    ->where(fn($q) => $q->where('pl_checked', true)->orWhere('pb_checked', true))
                    ->exists()
                || JurnalLifeCheck::forStudent($user->id)->whereDate('tanggal', $d)->where('checked', true)->exists();
            if ($has) { $streak++; $cursor->subDay(); } else break;
        }

        return [
            'date' => $date->toDateString(),
            'week' => $weekMeta,
            'config' => [
                'form_open_time'  => $config->form_open_time,
                'form_close_time' => $config->form_close_time,
                'form_active'     => $config->isFormOpen(),
            ],
            'bible' => [
                'day_no'     => $dayNo,
                'pl_porsi'   => $bibleItem?->pl_text ?? '',
                'pb_porsi'   => $bibleItem?->pb_text ?? '',
                'pl_checked' => (bool) ($entry?->pl_checked),
                'pb_checked' => (bool) ($entry?->pb_checked),
            ],
            'verse_ref'     => $verseRef,
            'verse_checked' => $verseChecked,
            'life_items' => $items->map(fn($it) => [
                'id'            => $it->id,
                'kategori'      => $it->kategori,
                'label'         => $it->label,
                'response_type' => $it->response_type ?? 'check',
                'checked'       => in_array($it->id, $checkedIds),
            ])->values(),
            'foto_belajar_url' => $entry?->foto_belajar ? asset('storage/' . $entry->foto_belajar) : null,
            'streak' => $streak,
        ];
    }
}
