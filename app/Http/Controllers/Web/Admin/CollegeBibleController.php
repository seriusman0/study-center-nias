<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollegeBibleItem;
use App\Models\CollegeBibleSchedule;
use App\Models\CollegeConfig;
use Illuminate\Http\Request;

class CollegeBibleController extends Controller
{
    public function index(Request $request)
    {
        $config    = CollegeConfig::current();
        $schedules = CollegeBibleSchedule::orderBy('id')->get();

        // Resolve which schedule to show
        $scheduleId = $request->input('schedule_id', $config->active_schedule_id ?? $schedules->first()?->id);
        $currentSchedule = $schedules->firstWhere('id', $scheduleId) ?? $schedules->first();

        $items = CollegeBibleItem::where('schedule_id', $currentSchedule?->id)
            ->orderBy('day_no')
            ->paginate(50)
            ->withQueryString();

        $allItems = CollegeBibleItem::where('schedule_id', $currentSchedule?->id)
            ->orderBy('day_no')
            ->get(['day_no', 'pl_text', 'pb_text']);

        return view('admin.college-jurnal.bible-schedule', compact('config', 'items', 'allItems', 'schedules', 'currentSchedule'));
    }

    public function update(Request $request, CollegeBibleItem $item)
    {
        $data = $request->validate([
            'pl_text' => 'nullable|string|max:255',
            'pb_text' => 'nullable|string|max:255',
        ]);
        $item->update($data);
        return back()->with('success', 'Hari ke-' . $item->day_no . ' diperbarui.');
    }

    public function importJson(Request $request)
    {
        $request->validate([
            'json_file'   => 'required|file|mimes:json,txt|max:512',
            'schedule_id' => 'required|exists:college_bible_schedules,id',
        ]);

        $content = file_get_contents($request->file('json_file')->getRealPath());
        $rows    = json_decode($content, true);

        if (!is_array($rows) || empty($rows)) {
            return back()->withErrors(['json_file' => 'Format JSON tidak valid.']);
        }

        $scheduleId = (int) $request->input('schedule_id');
        $count = 0;
        foreach ($rows as $row) {
            if (!isset($row['no'])) continue;
            CollegeBibleItem::updateOrCreate(
                ['schedule_id' => $scheduleId, 'day_no' => (int) $row['no']],
                ['pl_text' => $row['PL'] ?? null, 'pb_text' => $row['PB'] ?? null]
            );
            $count++;
        }

        return back()->with('success', "$count entri jadwal diimpor.");
    }

    public function updateAnchor(Request $request)
    {
        $data = $request->validate([
            'anchor_day_no'      => 'required|integer|min:1|max:366',
            'anchor_date'        => 'required|date',
            'form_open_time'     => 'required|date_format:H:i',
            'form_close_time'    => 'required|date_format:H:i',
            'active_schedule_id' => 'nullable|exists:college_bible_schedules,id',
        ]);

        $payload = [
            'anchor_day_no'   => $data['anchor_day_no'],
            'anchor_date'     => $data['anchor_date'],
            'form_open_time'  => $data['form_open_time'] . ':00',
            'form_close_time' => $data['form_close_time'] . ':00',
            'updated_by'      => $request->user()->id,
        ];

        if (isset($data['active_schedule_id'])) {
            $payload['active_schedule_id'] = $data['active_schedule_id'];
        }

        CollegeConfig::updateOrCreate(['id' => 1], $payload);

        return back()->with('success', 'Konfigurasi jadwal alkitab dan jam form diperbarui.');
    }
}
