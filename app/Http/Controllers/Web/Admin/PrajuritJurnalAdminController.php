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
use Illuminate\Http\Request;

class PrajuritJurnalAdminController extends Controller
{
    use HasJurnalAdminActions;

    protected string $role            = 'prajurit';
    protected string $viewPrefix      = 'admin.prajurit-jurnal';
    protected string $csvPrefix       = 'jurnal-prajurit';
    protected string $userVar         = 'targetUser';
    protected string $profileRelation = 'studentProfile';
    protected array  $kategori        = ['prajurit'];

    public function dashboard(Request $request)
    {
        $config       = CollegeConfig::current();
        $dayNo        = $config->todayDayNo();
        $bible        = CollegeBibleItem::forDayNo($dayNo);
        $today        = JurnalWeek::today()->toDateString();
        $sevenDaysAgo = JurnalWeek::today()->subDays(6)->toDateString();
        $roleId       = Role::where('name', 'prajurit')->value('id');

        $usersQ = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->with('studentProfile')
            ->orderBy('name');

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $usersQ->where(fn($w) => $w
                ->where('name', 'like', $term)
                ->orWhere('username', 'like', $term));
        }

        $users = $usersQ->paginate(30)->withQueryString();

        $activeToday = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->where(fn($q) => $q
                ->whereHas('jurnalLifeChecks', fn($c) =>
                    $c->whereDate('tanggal', $today)->where('checked', true))
            )->count();

        $totalUsers = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
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

        return view('admin.prajurit-jurnal.dashboard', compact(
            'config', 'dayNo', 'bible', 'today',
            'users', 'activeToday', 'totalUsers',
            'checkCounts', 'lastEntryDates'
        ));
    }

    /**
     * AJAX: Terima user_id dari QR scan, validasi prajurit, return data untuk modal jurnal.
     */
    public function scanJurnal(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);
        $roleId = Role::where('name', 'prajurit')->value('id');

        $prajurit = User::with('studentProfile')
            ->where('id', $request->user_id)
            ->where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->first();

        if (!$prajurit) {
            return response()->json(['status' => 'not_found']);
        }

        $today = JurnalWeek::today()->toDateString();

        // Ambil semua life items kategori prajurit
        $items = JurnalLifeItem::where('kategori', 'prajurit')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        // Ambil centangan hari ini untuk prajurit ini
        $checkedIds = JurnalLifeCheck::where('student_id', $prajurit->id)
            ->whereDate('tanggal', $today)
            ->where('checked', true)
            ->pluck('life_item_id')
            ->all();

        // Untuk item number (Jumlah Salah Ayat Hafalan), ambil nilai jika ada
        $numberValues = JurnalLifeCheck::where('student_id', $prajurit->id)
            ->whereDate('tanggal', $today)
            ->whereIn('life_item_id', $items->where('response_type', 'number')->pluck('id'))
            ->get()
            ->pluck('value', 'life_item_id');

        return response()->json([
            'status'   => 'found',
            'prajurit' => [
                'id'    => $prajurit->id,
                'name'  => $prajurit->name,
                'kelas' => $prajurit->studentProfile?->grade_class,
            ],
            'today'      => $today,
            'items'      => $items->map(fn($i) => [
                'id'            => $i->id,
                'label'         => $i->label,
                'response_type' => $i->response_type,
            ]),
            'checkedIds'   => $checkedIds,
            'numberValues' => $numberValues,
        ]);
    }

    /**
     * AJAX: Simpan centangan dari modal jurnal prajurit.
     * Auto-save (tidak perlu submit terpisah).
     */
    public function saveJurnal(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|integer|exists:users,id',
            'tanggal'           => 'required|date',
            'checks'            => 'nullable|array',
            'checks.*.item_id'  => 'required|integer',
            'checks.*.checked'  => 'nullable|boolean',
            'checks.*.value'    => 'nullable|numeric|min:0',
        ]);

        $roleId = Role::where('name', 'prajurit')->value('id');
        $prajurit = User::where('id', $request->user_id)
            ->where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->firstOrFail();

        $tanggal = $request->tanggal;
        $checks  = $request->checks ?? [];

        foreach ($checks as $check) {
            $itemId  = $check['item_id'];
            $checked = (bool) ($check['checked'] ?? false);
            $value   = $check['value'] ?? null;

            JurnalLifeCheck::updateOrCreate(
                [
                    'student_id'   => $prajurit->id,
                    'life_item_id' => $itemId,
                    'tanggal'      => $tanggal,
                ],
                [
                    'checked' => $checked,
                    'value'   => $value,
                ]
            );
        }

        // Buat/update JurnalEntry untuk tanggal ini (tanda sudah ada aktivitas)
        JurnalEntry::updateOrCreate(
            ['student_id' => $prajurit->id, 'tanggal' => $tanggal],
            ['cabang_id'  => $prajurit->cabang_id]
        );

        return response()->json(['status' => 'saved']);
    }
}
