<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JurnalReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $studentRoleId = Role::where('name', 'student')->value('id');

        $studentsQ = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $studentRoleId))
            ->with('cabang:id,nama')
            ->orderBy('name');

        if (! $user->isAdmin()) {
            if ($user->cabang_id) {
                $studentsQ->where('cabang_id', $user->cabang_id);
            } else {
                $studentsQ->whereRaw('1 = 0');
            }
        }
        if ($request->filled('cabang_id')) {
            $studentsQ->where('cabang_id', $request->cabang_id);
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $studentsQ->where(fn($w) => $w->where('name', 'like', $term)
                ->orWhere('username', 'like', $term));
        }

        $students = $studentsQ->paginate(20)->withQueryString();
        $cabangs = $user->isAdmin() ? Cabang::orderBy('nama')->get() : collect();

        $today = JurnalWeek::today()->toDateString();
        $weekStart = JurnalWeek::today()->subDays(6)->toDateString();
        $totalToday = JurnalEntry::whereDate('tanggal', $today)->count();
        $totalWeek  = JurnalEntry::whereBetween('tanggal', [$weekStart, $today])->count();

        return view('admin.jurnal.reports.index', [
            'students'   => $students,
            'cabangs'    => $cabangs,
            'totalToday' => $totalToday,
            'totalWeek'  => $totalWeek,
        ]);
    }

    public function show(Request $request, User $student)
    {
        $this->authorizeStudent($request, $student);

        $today = JurnalWeek::today();
        $from = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(13);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();
        if ($to->gt($today)) $to = $today->copy();

        $matrix = $this->buildMatrix($student, $from, $to);

        return view('admin.jurnal.reports.show', [
            'student' => $student->load('cabang', 'studentProfile'),
            'from'    => $from,
            'to'      => $to,
            'matrix'  => $matrix,
        ]);
    }

    public function export(Request $request, User $student)
    {
        $this->authorizeStudent($request, $student);

        $today = JurnalWeek::today();
        $from = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(29);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();

        $matrix = $this->buildMatrix($student, $from, $to);

        $filename = sprintf(
            'jurnal-%s-%s-%s.csv',
            preg_replace('/[^a-z0-9_\-]/i', '_', $student->name),
            $from->toDateString(),
            $to->toDateString()
        );

        return response()->streamDownload(function () use ($matrix) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM for Excel UTF-8
            fputcsv($out, $matrix['headers']);
            foreach ($matrix['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildMatrix(User $student, Carbon $from, Carbon $to): array
    {
        $items = JurnalLifeItem::forStudent($student->id)
            ->orderBy('kategori')->orderBy('label')->get();

        $entries = JurnalEntry::forStudent($student->id)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn($e) => $e->tanggal->toDateString());

        $checks = JurnalLifeCheck::forStudent($student->id)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->where('checked', true)
            ->get()
            ->groupBy(fn($c) => $c->tanggal->toDateString());

        $headers = ['Tanggal', 'PL', 'PB', 'Hafal Ayat'];
        foreach ($items as $it) {
            $headers[] = ucfirst($it->kategori) . ': ' . $it->label;
        }

        $rows = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key = $d->toDateString();
            $entry = $entries->get($key);
            $weekKey = JurnalWeek::weekKeyFor($d);
            $verseChecked = $entries->contains(fn($e) => $e->verse_week_key === $weekKey);

            $row = [
                $key,
                $entry?->pl_checked ? 'Y' : '-',
                $entry?->pb_checked ? 'Y' : '-',
                $verseChecked ? 'Y' : '-',
            ];
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

    private function authorizeStudent(Request $request, User $student): void
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            abort_if(!$user->cabang_id || $student->cabang_id !== $user->cabang_id, 403);
        }
    }
}
