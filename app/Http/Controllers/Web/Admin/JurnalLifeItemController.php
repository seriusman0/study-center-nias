<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalLifeItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalLifeItemController extends Controller
{
    public function index(Request $request)
    {
        $templates = JurnalLifeItem::template()->orderBy('kategori')->orderBy('label')->get();

        return view('admin.jurnal.life-items.index', [
            'templates' => $templates->groupBy('kategori'),
            'students'  => $this->studentList($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori' => 'required|in:kerohanian,pendidikan,karakter',
            'label'    => 'required|string|max:150',
        ]);
        $data['student_id'] = null;
        $data['is_default'] = false;
        $data['is_active']  = true;
        $data['created_by'] = $request->user()->id;
        JurnalLifeItem::create($data);
        return back()->with('success', 'Item template ditambahkan.');
    }

    public function update(Request $request, JurnalLifeItem $item)
    {
        $data = $request->validate([
            'kategori'  => 'required|in:kerohanian,pendidikan,karakter',
            'label'     => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? $item->is_active);
        $item->update($data);
        return back()->with('success', 'Item diperbarui.');
    }

    public function destroy(JurnalLifeItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item dihapus.');
    }

    public function studentAssignments(Request $request, User $student)
    {
        $this->authorizeStudent($request, $student);

        $templates = JurnalLifeItem::template()->where('is_active', true)
            ->orderBy('kategori')->orderBy('label')->get();

        $custom = JurnalLifeItem::where('student_id', $student->id)
            ->where('is_active', true)
            ->orderBy('kategori')->orderBy('label')->get();

        $assignedIds = DB::table('jurnal_student_life_items')
            ->where('student_id', $student->id)
            ->pluck('life_item_id')
            ->all();

        return view('admin.jurnal.life-items.student', [
            'student'     => $student,
            'templates'   => $templates->groupBy('kategori'),
            'custom'      => $custom->groupBy('kategori'),
            'assignedIds' => $assignedIds,
        ]);
    }

    public function syncStudent(Request $request, User $student)
    {
        $this->authorizeStudent($request, $student);

        $data = $request->validate([
            'template_ids'      => 'nullable|array',
            'template_ids.*'    => 'integer|exists:jurnal_life_items,id',
            'custom'            => 'nullable|array',
            'custom.*.kategori' => 'nullable|in:kerohanian,pendidikan,karakter',
            'custom.*.label'    => 'nullable|string|max:150',
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $student, $userId) {
            DB::table('jurnal_student_life_items')->where('student_id', $student->id)->delete();
            $now = now();
            $rows = collect($data['template_ids'] ?? [])->map(fn($id) => [
                'student_id'   => $student->id,
                'life_item_id' => (int) $id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ])->all();
            if (!empty($rows)) {
                DB::table('jurnal_student_life_items')->insert($rows);
            }

            foreach ($data['custom'] ?? [] as $row) {
                $label = trim((string) ($row['label'] ?? ''));
                $kategori = $row['kategori'] ?? null;
                if ($label === '' || ! $kategori) continue;
                JurnalLifeItem::create([
                    'kategori'   => $kategori,
                    'label'      => $label,
                    'is_default' => false,
                    'student_id' => $student->id,
                    'is_active'  => true,
                    'created_by' => $userId,
                ]);
            }
        });

        return back()->with('success', 'Jadwal kehidupan siswa diperbarui.');
    }

    private function authorizeStudent(Request $request, User $student): void
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            abort_if(!$user->cabang_id || $student->cabang_id !== $user->cabang_id, 403);
        }
    }

    private function studentList(User $user)
    {
        $studentRoleId = Role::where('name', 'student')->value('id');
        $q = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $studentRoleId))
            ->orderBy('name');
        if (! $user->isAdmin()) {
            if ($user->cabang_id) {
                $q->where('cabang_id', $user->cabang_id);
            } else {
                $q->whereRaw('1 = 0');
            }
        }
        return $q->get(['id', 'name', 'username', 'cabang_id']);
    }
}
