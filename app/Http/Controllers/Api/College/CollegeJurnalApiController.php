<?php

namespace App\Http\Controllers\Api\College;

use App\Http\Controllers\Controller;
use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use App\Models\CollegeStudyLog;
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
 * Self-service API untuk role college — jurnal harian college.
 *
 * Mirip JurnalApiController (student) tapi dengan response_type life item
 * (check / boolean / time_range) + CollegeStudyLog + form window config.
 */
class CollegeJurnalApiController extends Controller
{
    /** Life item kategori khusus college */
    protected const KATEGORI = ['pembacaan', 'sidang', 'rohani'];

    /** API: snapshot hari ini / tanggal tertentu untuk college */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();
        $date = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();

        return response()->json($this->snapshot($user, $date));
    }

    /** API: check-toggle life item / bible / study log */
    public function check(Request $request): JsonResponse
    {
        $user   = $request->user();
        $config = CollegeConfig::current();
        $data   = $request->validate([
            'item_type'     => 'required|in:pl,pb,life,study',
            'item_id'       => 'nullable|integer',
            'date'          => 'nullable|date',
            'checked'       => 'nullable|boolean',
            'verse_ref'     => 'nullable|string|max:100',
            'jam_mulai'     => 'nullable|date_format:H:i',
            'jam_selesai'   => 'nullable|date_format:H:i',
            'tipe'          => 'nullable|in:mandiri,kelompok',
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

        $type = $data['item_type'];

        DB::transaction(function () use ($user, $date, $type, $data) {
            if (in_array($type, ['pl', 'pb'])) {
                $entry = JurnalEntry::firstOrCreate(
                    ['student_id' => $user->id, 'tanggal' => $date->toDateString()],
                    ['cabang_id'  => $user->cabang_id]
                );
                $entry->update([$type . '_checked' => (bool) $data['checked']]);
                return;
            }

            if ($type === 'life') {
                $itemId  = (int) ($data['item_id'] ?? 0);
                abort_if($itemId === 0, 422, 'item_id wajib untuk tipe life.');
                $checked = (bool) $data['checked'];

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

            if ($type === 'study') {
                $itemId     = (int) ($data['item_id'] ?? 0);
                abort_if($itemId === 0, 422, 'item_id wajib untuk tipe study.');
                $jamMulai   = $data['jam_mulai'] ?? null;
                $jamSelesai = $data['jam_selesai'] ?? null;

                if ($jamMulai === null && $jamSelesai === null) {
                    CollegeStudyLog::where('user_id', $user->id)
                        ->where('life_item_id', $itemId)
                        ->whereDate('tanggal', $date->toDateString())
                        ->delete();
                    return;
                }

                CollegeStudyLog::updateOrCreate(
                    ['user_id' => $user->id, 'life_item_id' => $itemId, 'tanggal' => $date->toDateString()],
                    [
                        'jam_mulai'   => $jamMulai ?? '00:00',
                        'jam_selesai' => $jamSelesai ?? '00:00',
                        'tipe'        => $data['tipe'] ?? 'mandiri',
                    ]
                );
            }
        });

        return response()->json(['ok' => true, 'state' => $this->snapshot($user, $date)]);
    }

    /** API: history (tanpa verse — college tidak punya verse_ref) */
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

        $studyLogs = CollegeStudyLog::where('user_id', $user->id)
            ->whereDate('tanggal', '>=', $from->toDateString())
            ->whereDate('tanggal', '<=', $to->toDateString())
            ->get()
            ->groupBy(fn($s) => $s->tanggal->toDateString());

        $days = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key    = $d->toDateString();
            $entry  = $entries->get($key);
            $dayChecks = ($checks->get($key) ?? collect())->pluck('life_item_id')->all();
            $dayStudy  = ($studyLogs->get($key) ?? collect());

            $days[] = [
                'date'             => $key,
                'pl_checked'       => (bool) ($entry?->pl_checked),
                'pb_checked'       => (bool) ($entry?->pb_checked),
                'life_checked_ids' => $dayChecks,
                'study_logs'       => $dayStudy->map(fn($s) => [
                    'item_id'     => $s->life_item_id,
                    'jam_mulai'   => substr($s->jam_mulai, 0, 5),
                    'jam_selesai' => substr($s->jam_selesai, 0, 5),
                    'tipe'        => $s->tipe,
                ])->values(),
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

    /** API: college profile (institution, position) */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->collegeProfile;

        return response()->json([
            'institution_name' => $profile?->institution_name,
            'position'         => $profile?->position,
        ]);
    }

    // ── Internal: build snapshot for college ────────────────────────────────
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

        $studyLogs = CollegeStudyLog::where('user_id', $user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->get()
            ->keyBy('life_item_id');

        $studyState = $studyLogs->mapWithKeys(fn($l) => [$l->life_item_id => [
            'jam_mulai'   => substr($l->jam_mulai, 0, 5),
            'jam_selesai' => substr($l->jam_selesai, 0, 5),
            'tipe'        => $l->tipe,
        ]])->toArray();

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
            'show_verse' => false,
            'life_items' => $items->map(fn($it) => [
                'id'            => $it->id,
                'kategori'      => $it->kategori,
                'label'         => $it->label,
                'response_type' => $it->response_type ?? 'check',
                'checked'       => in_array($it->id, $checkedIds),
            ])->values(),
            'study_logs' => $studyState,
            'foto_belajar_url' => $entry?->foto_belajar ? asset('storage/' . $entry->foto_belajar) : null,
            'streak' => $streak,
        ];
    }
}
