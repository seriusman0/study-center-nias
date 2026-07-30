<?php

namespace App\Http\Controllers\Web\College;

use App\Http\Controllers\Controller;
use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use App\Models\CollegeStudyLog;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CollegeJurnalController extends Controller
{
    private const COLLEGE_KATEGORI = ['pembacaan', 'sidang', 'rohani'];

    public function index(Request $request)
    {
        $user   = $request->user();
        $config = CollegeConfig::current();

        $today = JurnalWeek::today();
        $date  = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : $today;

        abort_if($date->greaterThan($today), 422, 'Tanggal tidak boleh masa depan.');

        $isToday  = $date->isSameDay($today);
        $formOpen = !$isToday || $config->isFormOpen();

        $dayNo     = $config->dayNoFor($date);
        $bibleItem = CollegeBibleItem::forDayNo($dayNo);

        $entry = JurnalEntry::forStudent($user->id)->whereDate('tanggal', $date->toDateString())->first();

        $lifeItems = JurnalLifeItem::forStudent($user->id)
            ->whereIn('kategori', self::COLLEGE_KATEGORI)
            ->orderBy('kategori')
            ->orderBy('id')
            ->get()
            ->groupBy('kategori');

        $itemIds         = collect($lifeItems)->flatten(1)->pluck('id');
        $checkedItemIds  = JurnalLifeCheck::forStudent($user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->where('checked', true)
            ->pluck('life_item_id')
            ->all();

        // Load study logs keyed by life_item_id
        $studyLogs = CollegeStudyLog::where('user_id', $user->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->get()
            ->keyBy('life_item_id');

        // Pre-build study state for Alpine (avoid fn() inside @json Blade)
        $studyState = $studyLogs->mapWithKeys(function ($l) {
            return [$l->life_item_id => [
                'jam_mulai'   => substr($l->jam_mulai, 0, 5),
                'jam_selesai' => substr($l->jam_selesai, 0, 5),
                'tipe'        => $l->tipe,
            ]];
        })->toArray();

        // Streak calculation
        $streak = 0;
        $cursor = $today->copy();
        for ($i = 0; $i < 60; $i++) {
            $d   = $cursor->toDateString();
            $has = JurnalEntry::forStudent($user->id)->whereDate('tanggal', $d)
                    ->where(fn($q) => $q->where('pl_checked', true)->orWhere('pb_checked', true))
                    ->exists()
                || JurnalLifeCheck::forStudent($user->id)->whereDate('tanggal', $d)->where('checked', true)->exists();
            if ($has) { $streak++; $cursor->subDay(); } else break;
        }

        $fotoUrl = $entry?->foto_belajar
            ? asset('storage/' . $entry->foto_belajar)
            : null;

        return view('college.jurnal', [
            'date'           => $date,
            'today'          => $today,
            'isToday'        => $isToday,
            'formOpen'       => $formOpen,
            'config'         => $config,
            'dayNo'          => $dayNo,
            'bibleItem'      => $bibleItem,
            'entry'          => $entry,
            'lifeItems'      => $lifeItems,
            'checkedItemIds' => $checkedItemIds,
            'studyLogs'      => $studyLogs,
            'studyState'     => $studyState,
            'streak'         => $streak,
            'fotoUrl'        => $fotoUrl,
        ]);
    }

    public function showOther($userId, Request $request)
    {
        $viewer     = $request->user();
        $targetUser = \App\Models\User::where('is_active', true)->findOrFail($userId);

        abort_unless($targetUser->hasRole('college'), 403);
        abort_if($targetUser->id === $viewer->id, 302, route('college-jurnal.index'));

        $config = CollegeConfig::current();
        $today  = JurnalWeek::today();
        $date   = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : $today;

        abort_if($date->greaterThan($today), 422, 'Tanggal tidak boleh masa depan.');

        $isToday  = $date->isSameDay($today);
        $dayNo    = $config->dayNoFor($date);
        $bibleItem = CollegeBibleItem::forDayNo($dayNo);

        $entry = JurnalEntry::forStudent($targetUser->id)->whereDate('tanggal', $date->toDateString())->first();

        $lifeItems = JurnalLifeItem::forStudent($targetUser->id)
            ->whereIn('kategori', self::COLLEGE_KATEGORI)
            ->orderBy('kategori')
            ->orderBy('id')
            ->get()
            ->groupBy('kategori');

        $itemIds        = collect($lifeItems)->flatten(1)->pluck('id');
        $checkedItemIds = JurnalLifeCheck::forStudent($targetUser->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->where('checked', true)
            ->pluck('life_item_id')
            ->all();

        $studyLogs = CollegeStudyLog::where('user_id', $targetUser->id)
            ->whereDate('tanggal', $date->toDateString())
            ->whereIn('life_item_id', $itemIds)
            ->get()
            ->keyBy('life_item_id');

        $studyState = $studyLogs->mapWithKeys(function ($l) {
            return [$l->life_item_id => [
                'jam_mulai'   => substr($l->jam_mulai, 0, 5),
                'jam_selesai' => substr($l->jam_selesai, 0, 5),
                'tipe'        => $l->tipe,
            ]];
        })->toArray();

        $fotoUrl = $entry?->foto_belajar
            ? asset('storage/' . $entry->foto_belajar)
            : null;

        return view('college.jurnal', [
            'date'           => $date,
            'today'          => $today,
            'isToday'        => $isToday,
            'formOpen'       => false,
            'config'         => $config,
            'dayNo'          => $dayNo,
            'bibleItem'      => $bibleItem,
            'entry'          => $entry,
            'lifeItems'      => $lifeItems,
            'checkedItemIds' => $checkedItemIds,
            'studyLogs'      => $studyLogs,
            'studyState'     => $studyState,
            'streak'         => 0,
            'fotoUrl'        => $fotoUrl,
            'readOnly'       => true,
            'readOnlyUser'   => $targetUser,
        ]);
    }

    public function toggle(Request $request)
    {
        $user   = $request->user();
        $config = CollegeConfig::current();

        $data = $request->validate([
            'type'      => 'required|in:pl,pb,life,study',
            'date'      => 'nullable|date',
            'item_id'   => 'nullable|integer',
            'checked'   => 'nullable|boolean',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i',
            'tipe'      => 'nullable|in:mandiri,kelompok',
        ]);

        $date  = isset($data['date'])
            ? Carbon::parse($data['date'], JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();
        $today = JurnalWeek::today();

        if ($date->greaterThan($today)) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

        // Enforce form window only for today's entries
        if ($date->isSameDay($today) && !$config->isFormOpen()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Form jurnal hanya bisa diisi pukul ' . substr($config->form_open_time, 0, 5) . '–' . substr($config->form_close_time, 0, 5) . '.',
            ], 403);
        }

        $type = $data['type'];

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
                $itemId = (int) ($data['item_id'] ?? 0);
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
                $itemId = (int) ($data['item_id'] ?? 0);
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

        return response()->json(['ok' => true]);
    }

    public function uploadFoto(Request $request)
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
