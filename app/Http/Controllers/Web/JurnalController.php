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

        $config    = CollegeConfig::current();
        $dayNo     = $config->dayNoFor($date);
        $bibleItem = CollegeBibleItem::forDayNo($dayNo);

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

        $itemIds = collect($lifeItems)->flatten(1)->pluck('id');
        $lifeChecks = JurnalLifeCheck::forStudent($user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->where('checked', true)
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
                    $entry->update(['verse_week_key' => $ref ? $key : null, 'verse_ref' => $ref ?: null]);
                    break;
                case 'life':
                    $itemId  = (int) ($data['item_id'] ?? 0);
                    $checked = (bool) $data['checked'];
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

        return response()->json(['ok' => true]);
    }
}
