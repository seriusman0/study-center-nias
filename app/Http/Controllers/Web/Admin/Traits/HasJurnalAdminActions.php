<?php

namespace App\Http\Controllers\Web\Admin\Traits;

use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\User;
use App\Services\JurnalSetupService;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shared admin journal actions for CollegeJurnalAdmin and ScholarshipTeenagerJurnalAdmin.
 *
 * Consuming class must declare:
 *   protected string $role;           // e.g. 'college'
 *   protected string $viewPrefix;     // e.g. 'admin.college-jurnal'
 *   protected string $csvPrefix;      // e.g. 'jurnal-college'
 *   protected string $userVar;        // variable key passed to show view, e.g. 'collegeUser'
 *   protected string $profileRelation;// e.g. 'collegeProfile'
 *   protected array  $kategori;       // e.g. ['pembacaan', 'sidang', 'rohani']
 */
trait HasJurnalAdminActions
{
    public function show(Request $request, User $user)
    {
        abort_unless($user->hasRole($this->role), 404);

        $today = JurnalWeek::today();
        $from  = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(13);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();
        if ($to->gt($today)) $to = $today->copy();

        $matrix = $this->buildMatrix($user, $from, $to);

        return view($this->viewPrefix . '.show', [
            $this->userVar => $user->load($this->profileRelation),
            'from'         => $from,
            'to'           => $to,
            'matrix'       => $matrix,
        ]);
    }

    public function export(Request $request, User $user)
    {
        abort_unless($user->hasRole($this->role), 404);

        $today = JurnalWeek::today();
        $from  = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(29);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();

        $matrix   = $this->buildMatrix($user, $from, $to);
        $filename = sprintf(
            '%s-%s-%s-%s.csv',
            $this->csvPrefix,
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

    public function quickSetup(Request $request, User $user)
    {
        abort_unless($user->hasRole($this->role), 404);

        $allTemplates = JurnalLifeItem::whereNull('student_id')
            ->where('is_active', true)
            ->orderBy('kategori')->orderBy('label')
            ->get();

        $assignedIds = DB::table('jurnal_student_life_items')
            ->where('student_id', $user->id)
            ->pluck('life_item_id')
            ->all();

        return view('admin.jurnal.quick-setup', [
            'targetUser'  => $user,
            'role'        => $this->role,
            'templates'   => $allTemplates->groupBy('kategori'),
            'assignedIds' => $assignedIds,
            'backRoute'   => $this->viewPrefix . '.show',
        ]);
    }

    public function quickUpdate(Request $request, User $user)
    {
        abort_unless($user->hasRole($this->role), 404);

        $data = $request->validate([
            'action'       => 'required|in:sync,reset',
            'template_ids' => 'nullable|array',
            'template_ids.*' => 'integer|exists:jurnal_life_items,id',
        ]);

        if ($data['action'] === 'reset') {
            app(JurnalSetupService::class)->resetToDefaults($user, $this->role);
            return back()->with('success', 'Jurnal direset ke default role ' . $this->role . '.');
        }

        // sync: replace assignments with selected template_ids
        $ids  = $data['template_ids'] ?? [];
        $now  = now();
        $rows = collect($ids)->map(fn($id) => [
            'student_id'   => $user->id,
            'life_item_id' => (int) $id,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->all();

        DB::transaction(function () use ($user, $rows) {
            DB::table('jurnal_student_life_items')->where('student_id', $user->id)->delete();
            if (!empty($rows)) {
                DB::table('jurnal_student_life_items')->insert($rows);
            }
        });

        return back()->with('success', 'Item jurnal diperbarui.');
    }

    protected function buildMatrix(User $user, Carbon $from, Carbon $to): array
    {
        $items = JurnalLifeItem::forStudent($user->id)
            ->whereIn('kategori', $this->kategori)
            ->orderBy('kategori')->orderBy('id')
            ->get();

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
            $key        = $d->toDateString();
            $entry      = $entries->get($key);
            $checkedIds = ($checks->get($key) ?? collect())->pluck('life_item_id')->all();

            $row = [$key, $entry?->pl_checked ? 'Y' : '-', $entry?->pb_checked ? 'Y' : '-'];
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
