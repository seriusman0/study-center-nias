<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CollegeJurnalAdminController extends Controller
{
    private const COLLEGE_KATEGORI = ['pembacaan', 'sidang', 'rohani'];

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

        // Active today = has any entry or life check today
        $activeToday = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $collegeRoleId))
            ->where(function ($q) use ($today) {
                $q->whereHas('jurnalEntries', fn($e) => $e->whereDate('tanggal', $today))
                  ->orWhereHas('jurnalLifeChecks', fn($c) => $c->whereDate('tanggal', $today)->where('checked', true));
            })->count();

        $totalUsers = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $collegeRoleId))
            ->count();

        // 7-day check count per user (for sparkline indicator)
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

        $today     = JurnalWeek::today()->toDateString();
        $weekStart = JurnalWeek::today()->subDays(6)->toDateString();

        $campuses = User::whereHas('roles', fn($r) => $r->where('name', 'college'))
            ->join('college_profiles', 'users.id', '=', 'college_profiles.user_id')
            ->whereNotNull('college_profiles.institution_name')
            ->distinct()
            ->pluck('college_profiles.institution_name');

        return view('admin.college-jurnal.index', compact('users', 'campuses'));
    }

    public function show(Request $request, User $user)
    {
        abort_unless($user->hasRole('college'), 404);

        $today = JurnalWeek::today();
        $from  = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(13);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();
        if ($to->gt($today)) $to = $today->copy();

        $matrix = $this->buildMatrix($user, $from, $to);

        return view('admin.college-jurnal.show', [
            'collegeUser' => $user->load('collegeProfile'),
            'from'        => $from,
            'to'          => $to,
            'matrix'      => $matrix,
        ]);
    }

    public function export(Request $request, User $user)
    {
        abort_unless($user->hasRole('college'), 404);

        $today = JurnalWeek::today();
        $from  = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(29);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();

        $matrix   = $this->buildMatrix($user, $from, $to);
        $filename = sprintf(
            'jurnal-college-%s-%s-%s.csv',
            preg_replace('/[^a-z0-9_\-]/i', '_', $user->name),
            $from->toDateString(),
            $to->toDateString()
        );

        return response()->streamDownload(function () use ($matrix) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $matrix['headers']);
            foreach ($matrix['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildMatrix(User $user, Carbon $from, Carbon $to): array
    {
        $items = JurnalLifeItem::forStudent($user->id)
            ->whereIn('kategori', self::COLLEGE_KATEGORI)
            ->orderBy('kategori')->orderBy('id')->get();

        $entries = JurnalEntry::forStudent($user->id)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn($e) => $e->tanggal->toDateString());

        $checks = JurnalLifeCheck::forStudent($user->id)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->where('checked', true)
            ->get()
            ->groupBy(fn($c) => $c->tanggal->toDateString());

        $headers = ['Tanggal', 'PL', 'PB'];
        foreach ($items as $it) {
            $headers[] = $it->label;
        }

        $rows = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key   = $d->toDateString();
            $entry = $entries->get($key);

            $row = [
                $key,
                $entry?->pl_checked ? 'Y' : '-',
                $entry?->pb_checked ? 'Y' : '-',
            ];
            $checkedIds = ($checks->get($key) ?? collect())->pluck('life_item_id')->all();
            foreach ($items as $it) {
                $row[] = in_array($it->id, $checkedIds) ? 'Y' : '-';
            }
            $rows[] = $row;
        }

        $totalCells = count($rows) * (count($headers) - 1);
        $checked    = 0;
        foreach ($rows as $r) {
            for ($i = 1; $i < count($r); $i++) {
                if ($r[$i] === 'Y') $checked++;
            }
        }
        $pct = $totalCells > 0 ? round($checked / $totalCells * 100, 1) : 0;

        return [
            'headers' => $headers,
            'rows'    => $rows,
            'items'   => $items,
            'pct'     => $pct,
            'checked' => $checked,
            'total'   => $totalCells,
        ];
    }
}
