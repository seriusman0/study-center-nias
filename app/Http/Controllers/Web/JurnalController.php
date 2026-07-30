<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JurnalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $today = JurnalWeek::today();
        $date = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : $today;

        abort_if($date->greaterThan($today), 422, 'Tanggal jurnal tidak boleh di masa depan.');

        $config     = CollegeConfig::current();
        $dayNo      = $config->dayNoFor($date);
        $scheduleId = $user->cabang?->bible_schedule_id ?? $config->active_schedule_id;
        $bibleItem  = CollegeBibleItem::forDayNo($dayNo, $scheduleId);

        $weekKey    = JurnalWeek::weekKeyFor($date);
        $verseEntry = JurnalEntry::forStudent($user->id)
            ->where('verse_week_key', $weekKey)
            ->whereNotNull('verse_ref')
            ->first();
        $verseRef   = $verseEntry?->verse_ref;

        $entry = JurnalEntry::forStudent($user->id)->whereDate('tanggal', $date->toDateString())->first();

        $lifeItems = JurnalLifeItem::forStudent($user->id)
            ->orderBy('kategori')
            ->orderBy('label')
            ->get()
            ->groupBy('kategori');

        $allItems = collect($lifeItems)->flatten(1);
        $itemIds  = $allItems->pluck('id');

        $dailyIds   = $allItems->where('reset_period', 'daily')->pluck('id');
        $saturdayIds = $allItems->where('reset_period', 'weekly_saturday')->pluck('id');
        $sundayIds   = $allItems->where('reset_period', 'weekly_sunday')->pluck('id');

        // For weekly items, store/read check on the actual Saturday/Sunday of that ISO week
        $isoSaturday = $date->copy()->startOfWeek(\Carbon\CarbonInterface::MONDAY)->addDays(5); // Saturday
        $isoSunday   = $date->copy()->startOfWeek(\Carbon\CarbonInterface::MONDAY)->addDays(6); // Sunday

        $lifeChecks = JurnalLifeCheck::forStudent($user->id)
            ->where('checked', true)
            ->where(function ($q) use ($date, $dailyIds, $saturdayIds, $sundayIds, $isoSaturday, $isoSunday) {
                $q->where(function ($q2) use ($date, $dailyIds) {
                    $q2->whereDate('tanggal', $date->toDateString())
                       ->whereIn('life_item_id', $dailyIds);
                });
                if ($saturdayIds->isNotEmpty()) {
                    $q->orWhere(function ($q2) use ($isoSaturday, $saturdayIds) {
                        $q2->whereDate('tanggal', $isoSaturday->toDateString())
                           ->whereIn('life_item_id', $saturdayIds);
                    });
                }
                if ($sundayIds->isNotEmpty()) {
                    $q->orWhere(function ($q2) use ($isoSunday, $sundayIds) {
                        $q2->whereDate('tanggal', $isoSunday->toDateString())
                           ->whereIn('life_item_id', $sundayIds);
                    });
                }
            })
            ->whereIn('life_item_id', $itemIds)
            ->pluck('life_item_id')
            ->all();

        $streak = 0;
        $cursor = $today->copy();
        for ($i = 0; $i < 60; $i++) {
            $d = $cursor->toDateString();
            $has = JurnalEntry::forStudent($user->id)->whereDate('tanggal', $d)
                    ->where(fn($q) => $q->where('pl_checked', true)->orWhere('pb_checked', true)->orWhereNotNull('verse_week_key'))
                    ->exists()
                || JurnalLifeCheck::forStudent($user->id)->whereDate('tanggal', $d)->where('checked', true)->exists();
            if ($has) { $streak++; $cursor->subDay(); } else break;
        }

        return view('jurnal.index', [
            'date'           => $date,
            'today'          => $today,
            'isToday'        => $date->isSameDay($today),
            'dayNo'          => $dayNo,
            'bibleItem'      => $bibleItem,
            'weekKey'        => $weekKey,
            'verseRef'       => $verseRef,
            'entry'          => $entry,
            'lifeItems'      => $lifeItems,
            'checkedItemIds' => $lifeChecks,
            'streak'         => $streak,
        ]);
    }

    public function toggle(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'type'      => 'required|in:pl,pb,verse,life',
            'date'      => 'nullable|date',
            'item_id'   => 'nullable|integer',
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

        $type = $data['type'];

        DB::transaction(function () use ($user, $date, $type, $data) {
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

            switch ($type) {
                case 'pl':
                    $entry->update(['pl_checked' => (bool) $data['checked']]);
                    break;
                case 'pb':
                    $entry->update(['pb_checked' => (bool) $data['checked']]);
                    break;
                case 'verse':
                    $key = JurnalWeek::weekKeyFor($date);
                    $ref = $data['verse_ref'] ?? null;

                    if ($ref) {
                        // Update existing week entry if any, otherwise use today's entry
                        $weekEntry = JurnalEntry::where('student_id', $user->id)
                            ->where('verse_week_key', $key)
                            ->first();
                        if ($weekEntry) {
                            $weekEntry->update(['verse_ref' => $ref]);
                        } else {
                            $entry->update(['verse_week_key' => $key, 'verse_ref' => $ref]);
                        }
                    } else {
                        // Clear from whichever entry in this week holds the verse
                        JurnalEntry::where('student_id', $user->id)
                            ->where('verse_week_key', $key)
                            ->update(['verse_week_key' => null, 'verse_ref' => null]);
                    }
                    break;
                case 'life':
                    $itemId  = (int) ($data['item_id'] ?? 0);
                    $checked = (bool) $data['checked'];
                    abort_if($itemId === 0, 422, 'item_id wajib untuk tipe life.');

                    $lifeItem   = JurnalLifeItem::find($itemId);
                    $checkDate  = $date->toDateString();
                    if ($lifeItem) {
                        $weekMonday = $date->copy()->startOfWeek(\Carbon\CarbonInterface::MONDAY);
                        if ($lifeItem->reset_period === 'weekly_saturday') {
                            $checkDate = $weekMonday->copy()->addDays(5)->toDateString();
                        } elseif ($lifeItem->reset_period === 'weekly_sunday') {
                            $checkDate = $weekMonday->copy()->addDays(6)->toDateString();
                        }
                    }

                    if ($checked) {
                        JurnalLifeCheck::updateOrCreate(
                            ['student_id' => $user->id, 'life_item_id' => $itemId, 'tanggal' => $checkDate],
                            ['checked' => true]
                        );
                    } else {
                        JurnalLifeCheck::where('student_id', $user->id)
                            ->where('life_item_id', $itemId)
                            ->whereDate('tanggal', $checkDate)
                            ->delete();
                    }
                    break;
            }
        });

        return response()->json(['ok' => true]);
    }

    public function uploadFoto(Request $request)
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

        // Delete old photo if exists
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
        ]);
    }

    public function deleteFoto(Request $request)
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

        return response()->json(['ok' => true]);
    }
}
