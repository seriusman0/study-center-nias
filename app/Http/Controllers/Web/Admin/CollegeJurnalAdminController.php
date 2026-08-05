<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Admin\Traits\HasJurnalAdminActions;
use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Illuminate\Http\Request;

class CollegeJurnalAdminController extends Controller
{
    use HasJurnalAdminActions;

    protected string $role            = 'college';
    protected string $viewPrefix      = 'admin.college-jurnal';
    protected string $csvPrefix       = 'jurnal-college';
    protected string $userVar         = 'collegeUser';
    protected string $profileRelation = 'collegeProfile';
    protected array  $kategori        = ['pembacaan', 'sidang', 'rohani'];

    public function dashboard(Request $request)
    {
        $config        = CollegeConfig::current();
        $dayNo         = $config->todayDayNo();
        $bible         = CollegeBibleItem::forDayNo($dayNo);
        $today         = JurnalWeek::today()->toDateString();
        $sevenDaysAgo  = JurnalWeek::today()->subDays(6)->toDateString();
        $collegeRoleId = Role::where('name', 'college')->value('id');

        $usersQ = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $collegeRoleId))
            ->with('collegeProfile')
            ->orderBy('name');

        if ($request->filled('campus')) {
            $usersQ->whereHas('collegeProfile', fn($q) =>
                $q->where('institution_name', $request->campus)
            );
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $usersQ->where(fn($w) => $w->where('name', 'like', $term)->orWhere('username', 'like', $term));
        }

        $users = $usersQ->paginate(20)->withQueryString();

        $activeToday = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $collegeRoleId))
            ->where(fn($q) => $q
                ->whereHas('jurnalEntries', fn($e) => $e->whereDate('tanggal', $today))
                ->orWhereHas('jurnalLifeChecks', fn($c) => $c->whereDate('tanggal', $today)->where('checked', true))
            )->count();

        $totalUsers = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $collegeRoleId))
            ->count();

        $userIds = $users->pluck('id');

        $checkCounts = JurnalLifeCheck::whereIn('student_id', $userIds)
            ->whereBetween('tanggal', [$sevenDaysAgo, $today])
            ->where('checked', true)
            ->selectRaw('student_id, COUNT(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $lastEntryDates = JurnalEntry::whereIn('student_id', $userIds)
            ->selectRaw('student_id, MAX(tanggal) as last_date')
            ->groupBy('student_id')
            ->pluck('last_date', 'student_id');

        $campuses = User::whereHas('roles', fn($r) => $r->where('name', 'college'))
            ->join('college_profiles', 'users.id', '=', 'college_profiles.user_id')
            ->whereNotNull('college_profiles.institution_name')
            ->distinct()
            ->pluck('college_profiles.institution_name');

        return view('admin.college-jurnal.dashboard', compact(
            'config', 'dayNo', 'bible', 'today',
            'users', 'activeToday', 'totalUsers',
            'checkCounts', 'lastEntryDates', 'campuses'
        ));
    }

    public function index(Request $request)
    {
        $collegeRoleId = Role::where('name', 'college')->value('id');

        $usersQ = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $collegeRoleId))
            ->with('collegeProfile')
            ->orderBy('name');

        if ($request->filled('campus')) {
            $usersQ->whereHas('collegeProfile', fn($q) =>
                $q->where('institution_name', $request->campus)
            );
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $usersQ->where(fn($w) => $w->where('name', 'like', $term)->orWhere('username', 'like', $term));
        }

        $users = $usersQ->paginate(20)->withQueryString();

        $campuses = User::whereHas('roles', fn($r) => $r->where('name', 'college'))
            ->join('college_profiles', 'users.id', '=', 'college_profiles.user_id')
            ->whereNotNull('college_profiles.institution_name')
            ->distinct()
            ->pluck('college_profiles.institution_name');

        return view('admin.college-jurnal.index', compact('users', 'campuses'));
    }
}
