<?php

namespace App\Http\Controllers\Api\Prajurit;

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

class PrajuritJurnalApiController extends Controller
{
    protected const KATEGORI = ['prajurit'];

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
        $user   = $request->user();
        $config = CollegeConfig::current();
        
        $data = $request->validate([
            'item_type'     => 'required|in:pl,pb,life,verse,verse_check,study',
            'item_id'       => 'nullable|integer',
            'date'          => 'nullable|date',
            'checked'       => 'nullable|boolean',
            'value'         => 'nullable|integer',
            'jam_mulai'     => 'nullable|date_format:H:i',
            'jam_selesai'   => 'nullable|date_format:H:i',
            'tipe'          => 'nullable|in:mandiri,kelompok',
            'verse_ref'     => 'nullable|string|max:100',
        ]);

        $date = isset($data['date'])
            ? Carbon::parse($data['date'], JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();

        $today = JurnalWeek::today();
        if ($date->greaterThan($today)) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

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
                
                // Prajurit logic: sync with Membaca Alkitab Bersama-sama
                $mabId = JurnalLifeItem::where('label', 'like', '%Membaca Alkitab Bersama-sama%')->value('id');
                if ($mabId) {
                    if ($entry->pl_checked || $entry->pb_checked) {
                        $matchMab = ['student_id' => $user->id, 'life_item_id' => $mabId, 'tanggal' => $date->toDateString()];
                        if (DB::table('jurnal_life_checks')->where($matchMab)->exists()) {
                            DB::table('jurnal_life_checks')->where($matchMab)->update(['checked' => true, 'updated_at' => now()]);
                        } else {
                            DB::table('jurnal_life_checks')->insert(array_merge($matchMab, ['checked' => true, 'updated_at' => now(), 'created_at' => now()]));
                        }
                    } else {
                        DB::table('jurnal_life_checks')
                            ->where('student_id', $user->id)
                            ->where('life_item_id', $mabId)
                            ->where('tanggal', $date->toDateString())
                            ->delete();
                    }
                }
                return;
            }

            if ($type === 'life') {
                $itemId  = (int) ($data['item_id'] ?? 0);
                abort_if($itemId === 0, 422, 'item_id wajib untuk tipe life.');
                
                $isChecked = false;

                if (isset($data['checked']) && (bool) $data['checked']) {
                    $match = ['student_id' => $user->id, 'life_item_id' => $itemId, 'tanggal' => $date->toDateString()];
                    $isChecked = true;
                    $updateData = ['checked' => true, 'updated_at' => now()];
                    if (isset($data['value'])) {
                        $updateData['value'] = $data['value'];
                    }
                    if (DB::table('jurnal_life_checks')->where($match)->exists()) {
                        DB::table('jurnal_life_checks')->where($match)->update($updateData);
                    } else {
                        DB::table('jurnal_life_checks')->insert(array_merge($match, $updateData, ['created_at' => now()]));
                    }
                } else if (isset($data['value'])) {
                     $isChecked = true;
                     $match = ['student_id' => $user->id, 'life_item_id' => $itemId, 'tanggal' => $date->toDateString()];
                     $updateData = ['checked' => true, 'value' => $data['value'], 'updated_at' => now()];
                     if (DB::table('jurnal_life_checks')->where($match)->exists()) {
                         DB::table('jurnal_life_checks')->where($match)->update($updateData);
                     } else {
                         DB::table('jurnal_life_checks')->insert(array_merge($match, $updateData, ['created_at' => now()]));
                     }
                } else {
                    JurnalLifeCheck::where('student_id', $user->id)
                        ->where('life_item_id', $itemId)
                        ->whereDate('tanggal', $date->toDateString())
                        ->delete();
                }

                // Prajurit logic: sync Membaca Alkitab Bersama-sama to pl/pb
                $mabId = JurnalLifeItem::where('label', 'like', '%Membaca Alkitab Bersama-sama%')->value('id');
                if ($mabId && $itemId == $mabId) {
                    $entry = JurnalEntry::firstOrCreate(
                        ['student_id' => $user->id, 'tanggal' => $date->toDateString()],
                        ['cabang_id'  => $user->cabang_id]
                    );
                    $entry->update([
                        'pl_checked' => $isChecked,
                        'pb_checked' => $isChecked
                    ]);
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
                $key = JurnalWeek::weekKeyFor($date);
                $hasVerse = JurnalEntry::where('student_id', $user->id)
                    ->where('verse_week_key', $key)
                    ->whereNotNull('verse_ref')
                    ->exists();
                if (!$hasVerse && $checked) {
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
            
            // For study items we just check if it exists in DB, or provide the basic check items
            $dayChecks = ($checks->get($key) ?? collect())->pluck('life_item_id')->all();

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

    public function uploadFoto(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'foto' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
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

    private function snapshot($user, Carbon $date): array
    {
        $config = CollegeConfig::current();
        
        // Prajurit uses a specific anchor for Bible Reading
        $prajuritAnchor = \Carbon\Carbon::create(2026, 9, 6, 0, 0, 0, 'Asia/Jakarta');
        $diff = $prajuritAnchor->diffInDays($date->copy()->startOfDay(), false);
        $dayNo = (($diff) % 366) + 1;
        if ($dayNo < 1) $dayNo += 366;
        $bibleItem = CollegeBibleItem::forDayNo($dayNo, 4);

        $weekMeta = JurnalWeek::current($date);
        $weekKey  = JurnalWeek::weekKeyFor($date);

        $entry = JurnalEntry::forStudent($user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->first();

        $verseEntry = JurnalEntry::forStudent($user->id)
            ->where('verse_week_key', $weekKey)
            ->whereNotNull('verse_ref')
            ->first();
        $verseRef = $verseEntry?->verse_ref;

        $verseChecked = (bool) ($entry?->verse_checked);

        $items = JurnalLifeItem::where('is_active', true)
            ->whereIn('kategori', self::KATEGORI)
            ->where(function ($q) use ($user) {
                $q->whereNull('student_id')
                  ->orWhere('student_id', $user->id)
                  ->orWhereHas('assignedStudents', fn($a) => $a->where('users.id', $user->id));
            })
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();

        $itemIds = $items->pluck('id');
        $checks = JurnalLifeCheck::forStudent($user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->get()
            ->keyBy('life_item_id');

        $studyLogs = CollegeStudyLog::where('user_id', $user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->get()
            ->keyBy('life_item_id');

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
            'show_verse'    => false,
            'life_items' => $items->map(function($it) use ($checks, $studyLogs) {
                $check = $checks->get($it->id);
                $study = $studyLogs->get($it->id);
                return [
                    'id'            => $it->id,
                    'kategori'      => $it->kategori,
                    'label'         => $it->label,
                    'response_type' => $it->response_type ?? 'check',
                    'checked'       => $check ? (bool)$check->checked : false,
                    'value'         => $check ? $check->value : null,
                    'jam_mulai'     => $study ? substr($study->jam_mulai, 0, 5) : null,
                    'jam_selesai'   => $study ? substr($study->jam_selesai, 0, 5) : null,
                    'tipe_study'    => $study ? $study->tipe : null,
                ];
            })->values(),
            'foto_belajar_url' => $entry?->foto_belajar ? asset('storage/' . $entry->foto_belajar) : null,
            'streak' => $streak,
        ];
    }
}
