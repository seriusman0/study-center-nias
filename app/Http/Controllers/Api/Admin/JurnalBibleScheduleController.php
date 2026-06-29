<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalBibleSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalBibleScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $month = $request->filled('bulan') ? (int) $request->bulan : (int) now()->month;
        $year  = $request->filled('tahun') ? (int) $request->tahun : (int) now()->year;

        $schedules = JurnalBibleSchedule::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal')
            ->get();

        return response()->json(['data' => $schedules, 'bulan' => $month, 'tahun' => $year]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tanggal'  => 'required|date|unique:jurnal_bible_schedules,tanggal',
            'pl_porsi' => 'nullable|string|max:255',
            'pb_porsi' => 'nullable|string|max:255',
        ]);
        $data['created_by'] = $request->user()->id;
        $item = JurnalBibleSchedule::create($data);
        return response()->json(['data' => $item], 201);
    }

    public function update(Request $request, JurnalBibleSchedule $bibleSchedule): JsonResponse
    {
        $data = $request->validate([
            'tanggal'  => 'required|date|unique:jurnal_bible_schedules,tanggal,' . $bibleSchedule->id,
            'pl_porsi' => 'nullable|string|max:255',
            'pb_porsi' => 'nullable|string|max:255',
        ]);
        $bibleSchedule->update($data);
        return response()->json(['data' => $bibleSchedule]);
    }

    public function destroy(JurnalBibleSchedule $bibleSchedule): JsonResponse
    {
        $bibleSchedule->delete();
        return response()->json(['message' => 'Porsi Alkitab dihapus.']);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from'      => 'required|date',
            'to'        => 'required|date|after_or_equal:from',
            'pl_porsi'  => 'nullable|string|max:255',
            'pb_porsi'  => 'nullable|string|max:255',
            'overwrite' => 'nullable|boolean',
        ]);

        $start = Carbon::parse($data['from']);
        $end   = Carbon::parse($data['to']);
        $userId = $request->user()->id;
        $count = 0;

        DB::transaction(function () use ($start, $end, $data, $userId, &$count) {
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

        return response()->json(['message' => "Bulk: $count tanggal diproses.", 'processed' => $count]);
    }
}
