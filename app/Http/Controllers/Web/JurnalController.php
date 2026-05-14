<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JurnalBibleSchedule;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\JurnalWeeklyVerse;
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

        $schedule = JurnalBibleSchedule::forDate($date);

        $weekMeta = JurnalWeek::current($date);
        $verse = JurnalWeeklyVerse::forWeek($weekMeta['tahun'], $weekMeta['bulan'], $weekMeta['minggu']);

        $entry = JurnalEntry::forStudent($user->id)->whereDate('tanggal', $date->toDateString())->first();

        // Weekly verse "checked for this week" — any entry within [weekStart, weekEnd] with matching week_key.
        $weekStart = (clone $date)->subDays(($date->day - 1) % 7)->startOfDay();
        // Actually simpler: find any row in this week whose verse_week_key == current week key.
        $verseChecked = JurnalEntry::forStudent($user->id)
            ->where('verse_week_key', $weekMeta['key'])
            ->exists();

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

        return view('jurnal.index', [
            'date'           => $date,
            'today'          => $today,
            'isToday'        => $date->isSameDay($today),
            'schedule'       => $schedule,
            'verse'          => $verse,
            'weekMeta'       => $weekMeta,
            'verseChecked'   => $verseChecked,
            'entry'          => $entry,
            'lifeItems'      => $lifeItems,
            'checkedItemIds' => $lifeChecks,
        ]);
    }

    public function toggle(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'type'    => 'required|in:pl,pb,verse,life',
            'date'    => 'nullable|date',
            'item_id' => 'nullable|integer',
            'checked' => 'required|boolean',
        ]);

        $date = isset($data['date'])
            ? Carbon::parse($data['date'], JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();
        $today = JurnalWeek::today();

        if ($date->greaterThan($today)) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

        $type = $data['type'];
        $checked = (bool) $data['checked'];

        DB::transaction(function () use ($user, $date, $type, $checked, $data) {
            $entry = JurnalEntry::firstOrCreate(
                ['student_id' => $user->id, 'tanggal' => $date->toDateString()],
                ['cabang_id'  => $user->cabang_id]
            );

            switch ($type) {
                case 'pl':
                    $entry->update(['pl_checked' => $checked]);
                    break;
                case 'pb':
                    $entry->update(['pb_checked' => $checked]);
                    break;
                case 'verse':
                    $key = JurnalWeek::weekKeyFor($date);
                    $entry->update(['verse_week_key' => $checked ? $key : null]);
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

        return response()->json(['ok' => true]);
    }
}
