<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Role labels untuk display.
     */
    private const ROLE_LABELS = [
        'student'              => 'Siswa',
        'college'              => 'College',
        'scholarship_teenager' => 'Remaja Beasiswa',
    ];

    public function index(Request $request)
    {
        $user      = auth()->user();
        $studentId = $user->id;
        $tz        = JurnalWeek::TZ;
        $today     = Carbon::today($tz);

        // Deteksi semua role jurnal yang dimiliki user
        $userRoles  = $user->roles()->pluck('name')->toArray();
        $jurnalRoles = array_values(array_intersect(
            ['student', 'college', 'scholarship_teenager'],
            $userRoles
        ));

        // Total life items assigned to user (semua role)
        $totalItems = JurnalLifeItem::forStudent($studentId)->count();

        // Build 8-week windows (most recent first)
        $weeks = [];
        for ($i = 0; $i < 8; $i++) {
            $sunday   = $today->copy()->subWeeks($i)->startOfWeek(Carbon::SUNDAY);
            $saturday = $sunday->copy()->addDays(6);

            $weekKey = $sunday->format('Y-m-d');
            $label   = $sunday->translatedFormat('d M') . ' – ' . $saturday->translatedFormat('d M');

            $checks = JurnalLifeCheck::where('student_id', $studentId)
                ->where('checked', true)
                ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                ->count();

            $entryDays = JurnalEntry::where('student_id', $studentId)
                ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                ->where(fn($q) => $q->where('pl_checked', true)->orWhere('pb_checked', true))
                ->count();

            $activeDays = JurnalEntry::where('student_id', $studentId)
                ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                ->count()
                + JurnalLifeCheck::where('student_id', $studentId)
                    ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                    ->where('checked', true)
                    ->distinct('tanggal')
                    ->count('tanggal');

            $totalChecks = $checks + $entryDays;
            $maxChecks   = $totalItems > 0 ? $totalItems * 7 : 1;
            $pct         = min(100, round($totalChecks / max($maxChecks, 1) * 100));

            $weeks[] = [
                'label'       => $label,
                'week_key'    => $weekKey,
                'checks'      => $totalChecks,
                'max'         => $maxChecks,
                'pct'         => $pct,
                'active_days' => min(7, $activeDays),
            ];
        }

        // Summary
        $totalActiveDays = JurnalEntry::where('student_id', $studentId)
            ->selectRaw('COUNT(DISTINCT tanggal) as cnt')
            ->value('cnt') ?? 0;

        $totalCheckDays = JurnalLifeCheck::where('student_id', $studentId)
            ->where('checked', true)
            ->selectRaw('COUNT(DISTINCT tanggal) as cnt')
            ->value('cnt') ?? 0;

        $overallActiveDays = max($totalActiveDays, $totalCheckDays);

        // Best week
        $bestWeek = collect($weeks)->sortByDesc('pct')->first();

        // -- Daily matrix (14 hari terakhir, bisa difilter) --
        $dailyFrom = $request->filled('from')
            ? Carbon::parse($request->from, $tz)->startOfDay()
            : Carbon::parse(
                \Illuminate\Support\Facades\DB::table('jurnal_entries')->where('student_id', $studentId)->min(\Illuminate\Support\Facades\DB::raw('DATE(tanggal)')) ?? $today->copy()->startOfMonth()->toDateString(),
                $tz
              )->startOfDay();
        $dailyTo = $request->filled('to')
            ? Carbon::parse($request->to, $tz)->startOfDay()
            : $today->copy();
        if ($dailyTo->gt($today)) {
            $dailyTo = $today->copy();
        }

        $dailyMatrix = $this->buildDailyMatrix($user, $studentId, $dailyFrom, $dailyTo, $jurnalRoles);

        // -- Leaderboard Logic --
        $leaderboards    = [];
        $cabangId        = $user->cabang_id;
        $sevendaysAgoStr = $today->copy()->subDays(6)->toDateString();
        $todayStr        = $today->toDateString();

        foreach ($jurnalRoles as $roleName) {
            $roleId = \App\Models\Role::where('name', $roleName)->value('id');
            if (!$roleId) continue;

            $usersQ = \Illuminate\Support\Facades\DB::table('users as u')
                ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
                ->where('ur.role_id', $roleId)
                ->where('u.is_active', true)
                ->whereNull('u.deleted_at');

            if ($cabangId) {
                $usersQ->where('u.cabang_id', $cabangId);
            }

            $usersMap = $usersQ->select('u.id', 'u.name', 'u.avatar')->get()->keyBy('id');
            $userIds  = $usersMap->keys()->all();

            if (empty($userIds)) continue;

            $plQ = \Illuminate\Support\Facades\DB::table('jurnal_entries')
                ->whereIn('student_id', $userIds)
                ->where('pl_checked', true)
                ->whereBetween('tanggal', [$sevendaysAgoStr, $todayStr])
                ->select('student_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
                ->groupBy('student_id')->get()->keyBy('student_id');

            $pbQ = \Illuminate\Support\Facades\DB::table('jurnal_entries')
                ->whereIn('student_id', $userIds)
                ->where('pb_checked', true)
                ->whereBetween('tanggal', [$sevendaysAgoStr, $todayStr])
                ->select('student_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
                ->groupBy('student_id')->get()->keyBy('student_id');

            $lifeQ = \Illuminate\Support\Facades\DB::table('jurnal_life_checks')
                ->whereIn('student_id', $userIds)
                ->where('checked', true)
                ->whereBetween('tanggal', [$sevendaysAgoStr, $todayStr])
                ->select('student_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
                ->groupBy('student_id')->get()->keyBy('student_id');

            $studentsRanked = $usersMap->map(function ($u) use ($plQ, $pbQ, $lifeQ) {
                $score = ($plQ->get($u->id)->count ?? 0)
                    + ($pbQ->get($u->id)->count ?? 0)
                    + ($lifeQ->get($u->id)->count ?? 0);
                return (object) [
                    'id'     => $u->id,
                    'name'   => $u->name,
                    'avatar' => $u->avatar,
                    'score'  => $score,
                ];
            })->sortByDesc('score')->values()->take(10);

            $roleLabel             = self::ROLE_LABELS[$roleName] ?? ucfirst($roleName);
            $leaderboards[$roleLabel] = $studentsRanked;
        }

        return view('laporan.index', compact(
            'weeks', 'totalItems', 'overallActiveDays', 'bestWeek', 'user', 'leaderboards',
            'dailyMatrix', 'dailyFrom', 'dailyTo', 'jurnalRoles'
        ));
    }

    /**
     * Build a per-day progress matrix for the student (read-only).
     * Supports multi-role: items dari semua role user digabung dengan label role.
     */
    private function buildDailyMatrix($user, int $studentId, Carbon $from, Carbon $to, array $jurnalRoles): array
    {
        // Life items assigned to this student (semua)
        $items = JurnalLifeItem::forStudent($studentId)
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();

        // Entries & checks in range
        $entries = JurnalEntry::where('student_id', $studentId)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn($e) => ($e->tanggal instanceof Carbon
                ? $e->tanggal
                : Carbon::parse($e->tanggal))->toDateString());

        $checks = JurnalLifeCheck::where('student_id', $studentId)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->where('checked', true)
            ->get()
            ->groupBy(fn($c) => ($c->tanggal instanceof Carbon
                ? $c->tanggal
                : Carbon::parse($c->tanggal))->toDateString());

        // PL/PB ditampilkan untuk non-prajurit
        $showPlPb = !in_array('prajurit', $jurnalRoles) || count($jurnalRoles) > 1;

        // Tentukan role label per item berdasarkan kategori
        // Mapping kategori → role yang relevan
        $kategoriRoleMap = [
            'kerohanian' => ['student', 'college', 'scholarship_teenager'],
            'pendidikan' => ['student', 'scholarship_teenager'],
            'karakter'   => ['student', 'scholarship_teenager'],
            'pembacaan'  => ['scholarship_teenager', 'college', 'student'],
            'sidang'     => ['scholarship_teenager', 'college'],
            'rohani'     => ['scholarship_teenager', 'college'],
            'prajurit'   => ['prajurit'],
        ];

        // Buat header + group label
        $headers = [];
        if ($showPlPb) {
            $headers[] = ['key' => 'pl', 'label' => 'Baca PL', 'role_tag' => null];
            $headers[] = ['key' => 'pb', 'label' => 'Baca PB', 'role_tag' => null];
        }
        foreach ($items as $it) {
            $headers[] = ['key' => 'life_' . $it->id, 'label' => $it->label, 'role_tag' => null];
        }

        $totalCols = count($headers);

        // Build per-day rows (newest first)
        $days = [];
        for ($d = $to->copy(); $d->gte($from); $d->subDay()) {
            $key        = $d->toDateString();
            $entry      = $entries->get($key);
            $dayChecks  = $checks->get($key) ?? collect();
            $checkedIds = $dayChecks->pluck('life_item_id')->toArray();

            $cells   = [];
            $checked = 0;

            if ($showPlPb) {
                $plOk = (bool) ($entry?->pl_checked);
                $pbOk = (bool) ($entry?->pb_checked);
                $cells[] = ['label' => 'Baca PL', 'ok' => $plOk, 'value' => null];
                $cells[] = ['label' => 'Baca PB', 'ok' => $pbOk, 'value' => null];
                if ($plOk) $checked++;
                if ($pbOk) $checked++;
            }

            foreach ($items as $it) {
                $ok  = in_array($it->id, $checkedIds);
                $val = null;
                if ($ok && $it->response_type === 'number') {
                    $val = $dayChecks->firstWhere('life_item_id', $it->id)?->value;
                }
                $cells[] = ['label' => $it->label, 'ok' => $ok, 'value' => $val, 'kategori' => $it->kategori];
                if ($ok) $checked++;
            }

            $pct    = $totalCols > 0 ? round($checked / $totalCols * 100) : 0;
            $days[] = [
                'date'    => $key,
                'label'   => $d->translatedFormat('D, d M Y'),
                'cells'   => $cells,
                'checked' => $checked,
                'total'   => $totalCols,
                'pct'     => $pct,
                'points'  => $checked,
            ];
        }

        // Label roles yang aktif untuk ditampilkan di header
        $roleLabels = array_map(fn($r) => self::ROLE_LABELS[$r] ?? ucfirst($r), $jurnalRoles);
        $multiRole  = count($jurnalRoles) > 1;

        return [
            'headers'    => $headers,
            'days'       => $days,
            'from'       => $from->toDateString(),
            'to'         => $to->toDateString(),
            'role_labels' => $roleLabels,
            'multi_role'  => $multiRole,
        ];
    }
}
