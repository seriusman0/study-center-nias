<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Generic jurnal monitoring across ALL roles that fill a jurnal
 * (student, college, scholarship_teenager) - built for the admin
 * app's unified "Monitor Jurnal Semua Role" screen. Reuses the same
 * jurnal_entries / jurnal_life_checks / jurnal_life_items tables that
 * JurnalReportController (student-only) and HasJurnalAdminActions
 * (college/scholarship_teenager web trait) already use, so numbers
 * here are guaranteed consistent with the existing web admin pages.
 */
class JurnalMonitorController extends Controller
{
    private const ROLES = ['student', 'college', 'scholarship_teenager'];

    /**
     * Cross-role summary: how many active users per role, how many
     * filled their jurnal today / this week, completion rate.
     * This powers the "monitor semua role" overview screen.
     */
    public function summary(Request $request): JsonResponse
    {
        $today = JurnalWeek::today()->toDateString();
        $weekStart = JurnalWeek::today()->subDays(6)->toDateString();

        $roleIds = Role::whereIn('name', self::ROLES)->pluck('id', 'name');

        $byRole = [];
        foreach (self::ROLES as $role) {
            $roleId = $roleIds[$role] ?? null;
            if (!$roleId) {
                $byRole[$role] = ['total_users' => 0, 'active_today' => 0, 'active_week' => 0];
                continue;
            }

            $userIdsQ = User::where('is_active', true)
                ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId));

            $totalUsers = (clone $userIdsQ)->count();

            $userIds = (clone $userIdsQ)->pluck('id');

            $activeToday = User::whereIn('id', $userIds)
                ->where(fn($q) => $q
                    ->whereHas('jurnalEntries', fn($e) => $e->whereDate('tanggal', $today))
                    ->orWhereHas('jurnalLifeChecks', fn($c) => $c->whereDate('tanggal', $today)->where('checked', true))
                )->count();

            $activeWeek = User::whereIn('id', $userIds)
                ->where(fn($q) => $q
                    ->whereHas('jurnalEntries', fn($e) => $e->whereBetween('tanggal', [$weekStart, $today]))
                    ->orWhereHas('jurnalLifeChecks', fn($c) => $c->whereBetween('tanggal', [$weekStart, $today])->where('checked', true))
                )->count();

            $byRole[$role] = [
                'total_users'  => $totalUsers,
                'active_today' => $activeToday,
                'active_week'  => $activeWeek,
                'pct_today'    => $totalUsers > 0 ? round($activeToday / $totalUsers * 100, 1) : 0,
                'pct_week'     => $totalUsers > 0 ? round($activeWeek / $totalUsers * 100, 1) : 0,
            ];
        }

        return response()->json([
            'today'   => $today,
            'by_role' => $byRole,
        ]);
    }

    /**
     * List users for a given role with their jurnal fill status
     * (last entry date, checks in last 7 days) - feeds the drill-down
     * list under each role tab in the monitor screen.
     */
    public function index(Request $request, string $role): JsonResponse
    {
        abort_unless(in_array($role, self::ROLES, true), 404);

        $roleId = Role::where('name', $role)->value('id');
        abort_unless($roleId, 404);

        $today = JurnalWeek::today()->toDateString();
        $weekStart = JurnalWeek::today()->subDays(6)->toDateString();

        $q = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->with('cabang:id,nama')
            ->orderBy('name');

        if ($request->filled('cabang_id')) {
            $q->where('cabang_id', $request->cabang_id);
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $q->where(fn($w) => $w->where('name', 'like', $term)->orWhere('username', 'like', $term));
        }

        $users = $q->paginate(20)->withQueryString();

        $userIds = collect($users->items())->pluck('id');

        $lastEntryDates = JurnalEntry::whereIn('student_id', $userIds)
            ->selectRaw('student_id, MAX(tanggal) as last_date')
            ->groupBy('student_id')
            ->pluck('last_date', 'student_id');

        $checkCounts = JurnalLifeCheck::whereIn('student_id', $userIds)
            ->whereBetween('tanggal', [$weekStart, $today])
            ->where('checked', true)
            ->selectRaw('student_id, COUNT(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $users->getCollection()->transform(function ($user) use ($lastEntryDates, $checkCounts) {
            $user->last_jurnal_date = $lastEntryDates[$user->id] ?? null;
            $user->checks_last_7_days = $checkCounts[$user->id] ?? 0;
            return $user;
        });

        return response()->json([
            'role'  => $role,
            'users' => $users,
        ]);
    }

    /**
     * Full matrix report for one user of a given role - same shape as
     * JurnalReportController::show but works for college/scholarship
     * users too (their jurnal_life_items are filtered by category the
     * same way the web HasJurnalAdminActions trait does).
     */
    public function show(Request $request, string $role, User $targetUser): JsonResponse
    {
        abort_unless(in_array($role, self::ROLES, true), 404);
        abort_unless($targetUser->hasRole($role), 404);

        $today = JurnalWeek::today();
        $from = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(13);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();
        if ($to->gt($today)) $to = $today->copy();

        $matrix = $this->buildMatrix($targetUser, $from, $to, $role);

        return response()->json([
            'user'   => $targetUser->load('cabang:id,nama'),
            'role'   => $role,
            'from'   => $from->toDateString(),
            'to'     => $to->toDateString(),
            'matrix' => $matrix,
        ]);
    }

    public function export(Request $request, string $role, User $targetUser)
    {
        abort_unless(in_array($role, self::ROLES, true), 404);
        abort_unless($targetUser->hasRole($role), 404);

        $today = JurnalWeek::today();
        $from = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(29);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();

        $matrix = $this->buildMatrix($targetUser, $from, $to, $role);

        $filename = sprintf(
            'jurnal-%s-%s-%s-%s.csv',
            $role,
            preg_replace('/[^a-z0-9_\-]/i', '_', $targetUser->name),
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

    private function buildMatrix(User $user, Carbon $from, Carbon $to, string $role): array
    {
        $itemsQ = JurnalLifeItem::forStudent($user->id)->orderBy('kategori')->orderBy('label');
        if ($role !== 'student') {
            $itemsQ->whereIn('kategori', ['pembacaan', 'sidang', 'rohani']);
        }
        $items = $itemsQ->get();

        $entries = JurnalEntry::forStudent($user->id)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->get()->keyBy(fn($e) => $e->tanggal->toDateString());

        $checks = JurnalLifeCheck::forStudent($user->id)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->where('checked', true)
            ->get()->groupBy(fn($c) => $c->tanggal->toDateString());

        $headers = $role === 'student' ? ['Tanggal', 'PL', 'PB'] : ['Tanggal'];
        foreach ($items as $it) {
            $headers[] = ucfirst($it->kategori) . ': ' . $it->label;
        }

        $rows = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key = $d->toDateString();
            $entry = $entries->get($key);

            $row = [$key];
            if ($role === 'student') {
                $row[] = $entry?->pl_checked ? 'Y' : '-';
                $row[] = $entry?->pb_checked ? 'Y' : '-';
            }
            $checkedIds = ($checks->get($key) ?? collect())->pluck('life_item_id')->all();
            foreach ($items as $it) {
                $row[] = in_array($it->id, $checkedIds) ? 'Y' : '-';
            }
            $rows[] = $row;
        }

        $totalCells = count($rows) * (count($headers) - 1);
        $checked = 0;
        foreach ($rows as $r) {
            for ($i = 1; $i < count($r); $i++) {
                if ($r[$i] === 'Y') $checked++;
            }
        }

        return [
            'headers' => $headers,
            'rows'    => $rows,
            'items'   => $items,
            'pct'     => $totalCells > 0 ? round($checked / $totalCells * 100, 1) : 0,
            'checked' => $checked,
            'total'   => $totalCells,
        ];
    }
}
