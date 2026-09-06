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
use Illuminate\Support\Facades\DB;

class JurnalBulkController extends Controller
{
    public function create(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->toDateString()
            : JurnalWeek::today()->toDateString();

        $defaultItems = JurnalLifeItem::where('is_default', true)
            ->where('is_active', true)
            ->whereNull('student_id')
            ->whereIn('kategori', ['kerohanian', 'pendidikan', 'karakter'])
            ->orderBy('kategori')
            ->orderBy('label')
            ->get();

        $cabangs = Cabang::orderBy('nama')->get();

        $studentRoleId = Role::where('name', 'student')->value('id');
        $studentsQuery = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $studentRoleId))
            ->with('cabang:id,nama')
            ->orderBy('name');

        if ($request->filled('cabang_id')) {
            $studentsQuery->where('cabang_id', $request->cabang_id);
        }

        $students = $studentsQuery->get();

        return view('admin.jurnal.bulk', compact('date', 'defaultItems', 'cabangs', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'        => ['required', 'date', 'before_or_equal:today'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:users,id'],
            'items'       => ['nullable', 'array'],
            'items.pl'    => ['nullable', 'boolean'],
            'items.pb'    => ['nullable', 'boolean'],
            'items.verse' => ['nullable', 'boolean'],
            'items.life'  => ['nullable', 'array'],
            'items.life.*' => ['integer', 'exists:jurnal_life_items,id'],
        ]);

        $date    = Carbon::parse($validated['date'], JurnalWeek::TZ)->startOfDay();
        $dateStr = $date->toDateString();
        $items   = $validated['items'] ?? [];
        $setPl   = !empty($items['pl']);
        $setPb   = !empty($items['pb']);
        $setVerse = !empty($items['verse']);
        $lifeIds = $items['life'] ?? [];

        $studentRoleId = Role::where('name', 'student')->value('id');

        $students = User::whereIn('id', $validated['student_ids'])
            ->where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $studentRoleId))
            ->get();

        DB::transaction(function () use ($students, $dateStr, $date, $setPl, $setPb, $setVerse, $lifeIds) {
            $weekKey = JurnalWeek::weekKeyFor($date);

            foreach ($students as $student) {
                // Upsert JurnalEntry — hanya update kolom yang dipilih admin, tidak overwrite yang lain
                $entry = JurnalEntry::firstOrNew([
                    'student_id' => $student->id,
                    'tanggal'    => $dateStr,
                ]);

                if (!$entry->exists) {
                    $entry->cabang_id = $student->cabang_id;
                }

                if ($setPl)    $entry->pl_checked = true;
                if ($setPb)    $entry->pb_checked = true;
                if ($setVerse && !$entry->verse_week_key) {
                    $entry->verse_week_key = $weekKey;
                }

                $entry->save();

                // Upsert life checks — hanya yang dipilih, additive
                foreach ($lifeIds as $itemId) {
                    JurnalLifeCheck::updateOrCreate(
                        ['student_id' => $student->id, 'life_item_id' => $itemId, 'tanggal' => $dateStr],
                        ['checked' => true]
                    );
                }
            }
        });

        $count = $students->count();
        return redirect()->route('admin.jurnal.bulk.create')
            ->with('success', "Jurnal berhasil diisi untuk {$count} siswa pada {$dateStr}.");
    }
}
