<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Support\JurnalWeek;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $studentId = $user->id;
        $tz        = JurnalWeek::TZ;

        // Total life items assigned to user
        $totalItems = JurnalLifeItem::forStudent($studentId)->count();

        // Build 8-week windows (most recent first)
        $weeks = [];
        $today = Carbon::today($tz);

        for ($i = 0; $i < 8; $i++) {
            $sunday   = $today->copy()->subWeeks($i)->startOfWeek(Carbon::SUNDAY);
            $saturday = $sunday->copy()->addDays(6);

            $weekKey  = $sunday->format('Y-m-d');
            $label    = $sunday->translatedFormat('d M') . ' – ' . $saturday->translatedFormat('d M');

            // Count distinct life checks in this week (checked=true)
            $checks = JurnalLifeCheck::where('student_id', $studentId)
                ->where('checked', true)
                ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                ->count();

            // Count entry-based items (Baca Alkitab PL/PB) within the week
            $entryDays = JurnalEntry::where('student_id', $studentId)
                ->whereBetween('tanggal', [$sunday->toDateString(), $saturday->toDateString()])
                ->where(fn($q) => $q->where('pl_checked', true)->orWhere('pb_checked', true))
                ->count();

            // Active days: any check or entry that week
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
                'label'      => $label,
                'week_key'   => $weekKey,
                'checks'     => $totalChecks,
                'max'        => $maxChecks,
                'pct'        => $pct,
                'active_days'=> min(7, $activeDays),
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

        // -- Leaderboard Logic --
        $leaderboards = [];
        $rolesToCheck = $user->roles()->whereIn('name', ['student', 'college', 'scholarship_teenager'])->pluck('name');
        $cabangId = $user->cabang_id;
        $sevendaysAgoStr = $today->copy()->subDays(6)->toDateString();
        $todayStr = $today->toDateString();

        foreach ($rolesToCheck as $roleName) {
            $roleId = \App\Models\Role::where('name', $roleName)->value('id');
            $usersQ = \Illuminate\Support\Facades\DB::table('users as u')
                ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
                ->where('ur.role_id', $roleId)
                ->where('u.is_active', true)
                ->whereNull('u.deleted_at');
            
            if ($cabangId) {
                $usersQ->where('u.cabang_id', $cabangId);
            }
            
            $usersMap = $usersQ->select('u.id', 'u.name', 'u.avatar')->get()->keyBy('id');
            $userIds = $usersMap->keys()->all();
            
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
            
            $roleLabel = [
                'student' => 'Siswa',
                'college' => 'College',
                'scholarship_teenager' => 'Remaja Beasiswa'
            ][$roleName] ?? ucfirst($roleName);
            
            $leaderboards[$roleLabel] = $studentsRanked;
        }

        return view('laporan.index', compact('weeks', 'totalItems', 'overallActiveDays', 'bestWeek', 'user', 'leaderboards'));
    }
}
