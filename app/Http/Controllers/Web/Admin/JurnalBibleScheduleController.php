<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalBibleSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalBibleScheduleController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->filled('bulan') ? (int) $request->bulan : (int) now()->month;
        $year  = $request->filled('tahun') ? (int) $request->tahun : (int) now()->year;

        $schedules = JurnalBibleSchedule::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal')
            ->get();

        return view('admin.jurnal.bible-schedules.index', [
            'schedules' => $schedules,
            'bulan'     => $month,
            'tahun'     => $year,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal'  => 'required|date|unique:jurnal_bible_schedules,tanggal',
            'pl_porsi' => 'nullable|string|max:255',
            'pb_porsi' => 'nullable|string|max:255',
        ]);
        $data['created_by'] = $request->user()->id;

        JurnalBibleSchedule::create($data);

        return back()->with('success', 'Porsi Alkitab ditambahkan.');
    }

    public function update(Request $request, JurnalBibleSchedule $bibleSchedule)
    {
        $data = $request->validate([
            'tanggal'  => 'required|date|unique:jurnal_bible_schedules,tanggal,' . $bibleSchedule->id,
            'pl_porsi' => 'nullable|string|max:255',
            'pb_porsi' => 'nullable|string|max:255',
        ]);

        $bibleSchedule->update($data);

        return back()->with('success', 'Porsi Alkitab diperbarui.');
    }

    public function destroy(JurnalBibleSchedule $bibleSchedule)
    {
        $bibleSchedule->delete();
        return back()->with('success', 'Porsi Alkitab dihapus.');
    }

    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'from'     => 'required|date',
            'to'       => 'required|date|after_or_equal:from',
            'pl_porsi' => 'nullable|string|max:255',
            'pb_porsi' => 'nullable|string|max:255',
            'overwrite' => 'nullable|boolean',
        ]);

        $start = Carbon::parse($data['from']);
        $end   = Carbon::parse($data['to']);
        $now   = now();
        $userId = $request->user()->id;
        $count = 0;

        DB::transaction(function () use ($start, $end, $data, $now, $userId, &$count) {
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $exists = JurnalBibleSchedule::where('tanggal', $d->toDateString())->first();
                if ($exists) {
                    if (!empty($data['overwrite'])) {
                        $exists->update([
                            'pl_porsi' => $data['pl_porsi'] ?? null,
                            'pb_porsi' => $data['pb_porsi'] ?? null,
                        ]);
                        $count++;
                    }
                } else {
                    JurnalBibleSchedule::create([
                        'tanggal'    => $d->toDateString(),
                        'pl_porsi'   => $data['pl_porsi'] ?? null,
                        'pb_porsi'   => $data['pb_porsi'] ?? null,
                        'created_by' => $userId,
                    ]);
                    $count++;
                }
            }
        });

        return back()->with('success', "Bulk: $count tanggal diproses.");
    }
}
