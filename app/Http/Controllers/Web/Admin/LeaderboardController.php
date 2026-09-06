<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    private const ROLES = [
        'student'              => 'Siswa',
        'college'              => 'College',
        'scholarship_teenager' => 'Remaja Beasiswa',
    ];

    public function index(Request $request)
    {
        $role     = $request->input('role', 'student');
        $metric   = $request->input('metric', 'score');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $cabangId = $request->input('cabang_id');

        if (!array_key_exists($role, self::ROLES)) {
            $role = 'student';
        }

        $allowedMetrics = ['score', 'pl_count', 'pb_count', 'life_count'];
        if (!in_array($metric, $allowedMetrics)) {
            $metric = 'score';
        }

        $roleId = Role::where('name', $role)->value('id');

        $usersQ = DB::table('users as u')
            ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
            ->where('ur.role_id', $roleId)
            ->where('u.is_active', true)
            ->whereNull('u.deleted_at')
            ->leftJoin('cabangs as c', 'c.id', '=', 'u.cabang_id')
            ->select('u.id', 'u.name', 'u.username', 'u.avatar', 'c.nama as cabang_name');

        if ($cabangId) {
            $usersQ->where('u.cabang_id', $cabangId);
        }

        $users   = $usersQ->get()->keyBy('id');
        $userIds = $users->keys()->all();

        $cabangs  = Cabang::orderBy('nama')->get();
        $students = collect();

        if (!empty($userIds)) {
            $plQ = DB::table('jurnal_entries')
                ->whereIn('student_id', $userIds)
                ->where('pl_checked', true)
                ->select('student_id', DB::raw('COUNT(*) as pl_count'))
                ->groupBy('student_id');
            if ($dateFrom) $plQ->where('tanggal', '>=', $dateFrom);
            if ($dateTo)   $plQ->where('tanggal', '<=', $dateTo);
            $plMap = $plQ->get()->keyBy('student_id');

            $pbQ = DB::table('jurnal_entries')
                ->whereIn('student_id', $userIds)
                ->where('pb_checked', true)
                ->select('student_id', DB::raw('COUNT(*) as pb_count'))
                ->groupBy('student_id');
            if ($dateFrom) $pbQ->where('tanggal', '>=', $dateFrom);
            if ($dateTo)   $pbQ->where('tanggal', '<=', $dateTo);
            $pbMap = $pbQ->get()->keyBy('student_id');

            $lifeQ = DB::table('jurnal_life_checks')
                ->whereIn('student_id', $userIds)
                ->where('checked', true)
                ->select('student_id', DB::raw('COUNT(*) as life_count'))
                ->groupBy('student_id');
            if ($dateFrom) $lifeQ->where('tanggal', '>=', $dateFrom);
            if ($dateTo)   $lifeQ->where('tanggal', '<=', $dateTo);
            $lifeMap = $lifeQ->get()->keyBy('student_id');

            $students = $users->map(function ($u) use ($plMap, $pbMap, $lifeMap) {
                $pl   = (int) ($plMap->get($u->id)?->pl_count ?? 0);
                $pb   = (int) ($pbMap->get($u->id)?->pb_count ?? 0);
                $life = (int) ($lifeMap->get($u->id)?->life_count ?? 0);

                return (object) [
                    'user_id'     => $u->id,
                    'name'        => $u->name,
                    'username'    => $u->username,
                    'avatar'      => $u->avatar,
                    'cabang_name' => $u->cabang_name,
                    'pl_count'    => $pl,
                    'pb_count'    => $pb,
                    'life_count'  => $life,
                    'score'       => $pl + $pb + $life,
                ];
            })->sortByDesc($metric)->values();
        }

        return view('admin.leaderboard', compact(
            'students', 'cabangs', 'role', 'metric', 'dateFrom', 'dateTo', 'cabangId'
        ));
    }
}
