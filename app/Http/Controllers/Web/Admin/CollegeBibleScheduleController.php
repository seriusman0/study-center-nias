<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollegeBibleItem;
use App\Models\CollegeBibleSchedule;
use App\Models\CollegeConfig;
use Illuminate\Http\Request;

class CollegeBibleScheduleController extends Controller
{
    public function index()
    {
        $config    = CollegeConfig::current();
        $schedules = CollegeBibleSchedule::withCount('items')->orderBy('id')->get();

        return view('admin.college-jurnal.bible-schedules.index', compact('schedules', 'config'));
    }

    public function create()
    {
        $schedules = CollegeBibleSchedule::orderBy('id')->get(['id', 'name']);
        return view('admin.college-jurnal.bible-schedules.create', compact('schedules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:500',
            'duplicate_from' => 'nullable|exists:college_bible_schedules,id',
        ]);

        $schedule = CollegeBibleSchedule::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (!empty($data['duplicate_from'])) {
            $sourceItems = CollegeBibleItem::where('schedule_id', $data['duplicate_from'])->get();
            foreach ($sourceItems as $item) {
                CollegeBibleItem::create([
                    'schedule_id' => $schedule->id,
                    'day_no'      => $item->day_no,
                    'pl_text'     => $item->pl_text,
                    'pb_text'     => $item->pb_text,
                ]);
            }
        }

        return redirect()->route('admin.jurnal-college.bible-schedules.index')
            ->with('success', "Jadwal \"{$schedule->name}\" berhasil dibuat.");
    }

    public function edit(CollegeBibleSchedule $bibleSchedule)
    {
        return view('admin.college-jurnal.bible-schedules.edit', compact('bibleSchedule'));
    }

    public function update(Request $request, CollegeBibleSchedule $bibleSchedule)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $bibleSchedule->update($data);

        return redirect()->route('admin.jurnal-college.bible-schedules.index')
            ->with('success', "Jadwal \"{$bibleSchedule->name}\" berhasil diperbarui.");
    }

    public function destroy(CollegeBibleSchedule $bibleSchedule)
    {
        $config = CollegeConfig::current();

        if ($config->active_schedule_id === $bibleSchedule->id) {
            return back()->withErrors(['delete' => 'Tidak bisa menghapus jadwal yang sedang aktif. Aktifkan jadwal lain terlebih dahulu.']);
        }

        $bibleSchedule->delete(); // items auto-deleted via cascade

        return redirect()->route('admin.jurnal-college.bible-schedules.index')
            ->with('success', "Jadwal \"{$bibleSchedule->name}\" berhasil dihapus.");
    }

    public function setActive(CollegeBibleSchedule $bibleSchedule)
    {
        CollegeConfig::updateOrCreate(['id' => 1], ['active_schedule_id' => $bibleSchedule->id]);

        return back()->with('success', "\"{$bibleSchedule->name}\" dijadikan jadwal aktif.");
    }
}
