<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessJurnalPhotoScan;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\JurnalPhotoScan;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JurnalPhotoScanController extends Controller
{
    public function create()
    {
        return view('admin.jurnal.scan');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'file', 'image', 'max:10240'],
        ]);

        $file = $request->file('photo');
        $path = $file->store('jurnal-photo-scans', 'local');

        $scan = JurnalPhotoScan::create([
            'image_path'    => $path,
            'original_name' => $file->getClientOriginalName(),
            'status'        => 'pending',
            'created_by'    => auth()->id(),
        ]);

        ProcessJurnalPhotoScan::dispatch($scan->id);

        return response()->json(['scan_id' => $scan->id]);
    }

    public function status(JurnalPhotoScan $scan)
    {
        $data = [
            'id'     => $scan->id,
            'status' => $scan->status,
        ];

        if ($scan->isDone() && $scan->result_json) {
            $result = $scan->result_json;

            // Fuzzy-match nama siswa ke DB
            $studentRoleId = Role::where('name', 'student')->value('id');
            $matches = [];
            if (!empty($result['nama_siswa'])) {
                $words = array_filter(explode(' ', $result['nama_siswa']));
                $query = User::where('is_active', true)
                    ->whereHas('roles', fn($r) => $r->where('roles.id', $studentRoleId))
                    ->with('cabang:id,nama');
                foreach ($words as $w) {
                    $query->where('name', 'like', "%{$w}%");
                }
                $matches = $query->limit(5)->get(['id', 'name', 'username', 'cabang_id'])
                    ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'username' => $u->username, 'cabang' => $u->cabang?->nama])
                    ->values()->all();
            }

            $data['result']        = $result;
            $data['student_matches'] = $matches;
        }

        if ($scan->isFailed()) {
            $data['error'] = $scan->error_message;
        }

        return response()->json($data);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'entries'              => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', 'exists:users,id'],
            'entries.*.hari'       => ['required', 'array'],
        ]);

        $studentRoleId = Role::where('name', 'student')->value('id');
        $defaultItems  = JurnalLifeItem::where('is_default', true)
            ->where('is_active', true)
            ->whereNull('student_id')
            ->get()
            ->keyBy('label');

        // Map label singkat → label asli
        $shortMap = [
            'Berdoa Pagi'    => 'Mengawali hari dengan berdoa',
            'Kelas SC'       => 'Hadir di kelas SC',
            'Pem. Minggu'    => 'Hadir Pembinaan hari Minggu',
            'Pem. Sabtu'     => 'Hadir Pembinaan hari Sabtu',
            'Sapa Ortu/Guru' => 'Menyapa orangtua/guru/kakak',
            'Rapikan Kasur'  => 'Merapikan tempat tidur',
        ];

        $saved = 0;

        DB::transaction(function () use ($request, $studentRoleId, $defaultItems, $shortMap, &$saved) {
            foreach ($request->entries as $entry) {
                $student = User::where('id', $entry['student_id'])
                    ->where('is_active', true)
                    ->whereHas('roles', fn($r) => $r->where('roles.id', $studentRoleId))
                    ->first();

                if (! $student) continue;

                foreach ($entry['hari'] as $dateStr => $items) {
                    try {
                        $date    = Carbon::parse($dateStr, JurnalWeek::TZ)->startOfDay();
                        $weekKey = JurnalWeek::weekKeyFor($date);
                    } catch (\Throwable) {
                        continue;
                    }

                    $jEntry = JurnalEntry::firstOrNew([
                        'student_id' => $student->id,
                        'tanggal'    => $dateStr,
                    ]);
                    if (! $jEntry->exists) {
                        $jEntry->cabang_id = $student->cabang_id;
                    }

                    if (!empty($items['pl']))   $jEntry->pl_checked = true;
                    if (!empty($items['pb']))   $jEntry->pb_checked = true;
                    if (!empty($items['ayat']) && !$jEntry->verse_week_key) {
                        $jEntry->verse_week_key = $weekKey;
                    }
                    $jEntry->save();

                    // Life items
                    foreach ($items as $key => $checked) {
                        if (in_array($key, ['pl', 'pb', 'ayat']) || ! $checked) continue;

                        $realLabel = $shortMap[$key] ?? $key;
                        $item      = $defaultItems->get($realLabel);
                        if (! $item) continue;

                        JurnalLifeCheck::updateOrCreate(
                            ['student_id' => $student->id, 'life_item_id' => $item->id, 'tanggal' => $dateStr],
                            ['checked' => true]
                        );
                    }
                    $saved++;
                }
            }
        });

        return response()->json(['saved_days' => $saved]);
    }
}
