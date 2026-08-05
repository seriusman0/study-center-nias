<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Admin\Traits\HasJurnalAdminActions;
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

class AdminJurnalHubController extends Controller
{
    use HasJurnalAdminActions;

    // Required by HasJurnalAdminActions — overridden per request
    protected string $role            = 'college';
    protected string $viewPrefix      = 'admin.college-jurnal';
    protected string $csvPrefix       = 'jurnal-college';
    protected string $userVar         = 'targetUser';
    protected string $profileRelation = 'collegeProfile';
    protected array  $kategori        = ['pembacaan', 'sidang', 'rohani'];

    private static array $ROLES = [
        'college'              => ['kategori' => ['pembacaan', 'sidang', 'rohani'], 'profile' => 'collegeProfile'],
        'scholarship_teenager' => ['kategori' => ['pembacaan', 'sidang', 'rohani'], 'profile' => 'studentProfile'],
        'student'              => ['kategori' => ['kerohanian', 'pendidikan', 'karakter'], 'profile' => 'studentProfile'],
    ];

    public function index(Request $request)
    {
        $config = CollegeConfig::current();
        $dayNo  = $config->todayDayNo();
        $bible  = CollegeBibleItem::forDayNo($dayNo);
        $today  = JurnalWeek::today()->toDateString();

        $stats = $this->buildStats($today);

        $itemsByRole = [
            'student'              => JurnalLifeItem::template()
                ->whereIn('kategori', ['kerohanian', 'pendidikan', 'karakter'])
                ->orderBy('kategori')->orderBy('id')->get(),
            'college'              => JurnalLifeItem::template()
                ->whereIn('kategori', ['pembacaan', 'sidang', 'rohani'])
                ->orderBy('kategori')->orderBy('id')->get(),
            'scholarship_teenager' => JurnalLifeItem::template()
                ->whereIn('kategori', ['pembacaan', 'sidang', 'rohani'])
                ->orderBy('kategori')->orderBy('id')->get(),
        ];

        return view('admin.jurnal.hub', compact('config', 'dayNo', 'bible', 'today', 'stats', 'itemsByRole'));
    }

    public function users(Request $request)
    {
        $role = $request->get('role', 'college');
        abort_unless(array_key_exists($role, self::$ROLES), 422);

        $roleId  = Role::where('name', $role)->value('id');
        $profile = self::$ROLES[$role]['profile'];

        $usersQ = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->with($profile)
            ->orderBy('name');

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $usersQ->where(fn($w) => $w->where('name', 'like', $term)->orWhere('username', 'like', $term));
        }
        if ($role === 'college' && $request->filled('campus')) {
            $usersQ->whereHas('collegeProfile', fn($q) =>
                $q->where('institution_name', $request->campus)
            );
        }

        $paginated = $usersQ->paginate(20)->withQueryString();
        $userIds   = $paginated->pluck('id');
        $today     = JurnalWeek::today()->toDateString();
        $sevenAgo  = JurnalWeek::today()->subDays(6)->toDateString();

        $checkCounts = JurnalLifeCheck::whereIn('student_id', $userIds)
            ->whereBetween('tanggal', [$sevenAgo, $today])
            ->where('checked', true)
            ->selectRaw('student_id, COUNT(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $lastEntryDates = JurnalEntry::whereIn('student_id', $userIds)
            ->selectRaw('student_id, MAX(tanggal) as last_date')
            ->groupBy('student_id')
            ->pluck('last_date', 'student_id');

        $items = $paginated->map(fn($u) => [
            'id'          => $u->id,
            'name'        => $u->name,
            'username'    => $u->username,
            'campus'      => $u->{$profile}?->institution_name ?? null,
            'checks7'     => $checkCounts[$u->id] ?? 0,
            'last_entry'  => $lastEntryDates[$u->id] ?? null,
            'active_today'=> ($lastEntryDates[$u->id] ?? null) === $today,
        ]);

        return response()->json([
            'data'         => $items,
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ]);
    }

    public function matrix(Request $request, User $user)
    {
        $role = $request->get('role', 'college');
        abort_unless(array_key_exists($role, self::$ROLES), 422);
        abort_unless($user->hasRole($role), 404);

        $this->configureForRole($role);

        $today = JurnalWeek::today();
        $from  = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(13);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();
        if ($to->gt($today)) $to = $today->copy();

        $matrix = $this->buildMatrix($user, $from, $to);

        return response()->json([
            'user'    => ['id' => $user->id, 'name' => $user->name],
            'from'    => $from->toDateString(),
            'to'      => $to->toDateString(),
            'headers' => $matrix['headers'],
            'rows'    => $matrix['rows'],
            'pct'     => $matrix['pct'],
            'checked' => $matrix['checked'],
            'total'   => $matrix['total'],
        ]);
    }

    public function exportCsv(Request $request, User $user)
    {
        $role = $request->get('role', 'college');
        abort_unless(array_key_exists($role, self::$ROLES), 422);
        abort_unless($user->hasRole($role), 404);

        $this->configureForRole($role);
        $this->csvPrefix = 'jurnal-' . str_replace('_', '-', $role);

        $today = JurnalWeek::today();
        $from  = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(29);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();

        $matrix   = $this->buildMatrix($user, $from, $to);
        $filename = sprintf(
            'jurnal-%s-%s-%s-%s.csv',
            str_replace('_', '-', $role),
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

    public function updateConfig(Request $request)
    {
        $data = $request->validate([
            'form_open_time'  => 'required|date_format:H:i',
            'form_close_time' => 'required|date_format:H:i',
        ]);

        $config = CollegeConfig::current();
        $config->update([
            'form_open_time'  => $data['form_open_time'] . ':00',
            'form_close_time' => $data['form_close_time'] . ':00',
            'updated_by'      => $request->user()->id,
        ]);

        return back()->with('config_success', 'Jam form jurnal diperbarui.');
    }

    public function stats()
    {
        $today = JurnalWeek::today()->toDateString();
        return response()->json($this->buildStats($today));
    }

    private function configureForRole(string $role): void
    {
        $this->role     = $role;
        $this->kategori = self::$ROLES[$role]['kategori'];
    }

    private function buildStats(string $today): array
    {
        $result = [];
        foreach (array_keys(self::$ROLES) as $role) {
            $roleId = Role::where('name', $role)->value('id');
            $total  = User::where('is_active', true)
                ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
                ->count();
            $active = User::where('is_active', true)
                ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
                ->where(fn($q) => $q
                    ->whereHas('jurnalEntries', fn($e) => $e->whereDate('tanggal', $today))
                    ->orWhereHas('jurnalLifeChecks', fn($c) => $c->whereDate('tanggal', $today)->where('checked', true))
                )->count();
            $result[$role] = ['total' => $total, 'active_today' => $active];
        }
        return $result;
    }
}
