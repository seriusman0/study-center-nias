<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalLifeItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalLifeItemController extends Controller
{
    public function studentAssignments(Request $request, User $student): JsonResponse
    {
        $this->authorizeStudent($request, $student);

        $templates = JurnalLifeItem::template()->where('is_active', true)
            ->orderBy('kategori')->orderBy('label')->get();

        $custom = JurnalLifeItem::where('student_id', $student->id)
            ->where('is_active', true)
            ->orderBy('kategori')->orderBy('label')->get();

        $assignedIds = DB::table('jurnal_student_life_items')
            ->where('student_id', $student->id)
            ->pluck('life_item_id')->all();

        return response()->json([
            'student'      => $student->only('id', 'name', 'username'),
            'templates'    => $templates,
            'custom'       => $custom,
            'assigned_ids' => $assignedIds,
        ]);
    }

    public function syncStudent(Request $request, User $student): JsonResponse
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
                if ($label === '' || !$kategori) continue;
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

        return response()->json(['message' => 'Jadwal kehidupan siswa diperbarui.']);
    }

    private function authorizeStudent(Request $request, User $student): void
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort_if(!$user->cabang_id || $student->cabang_id !== $user->cabang_id, 403);
        }
    }

    public function index(): JsonResponse
    {
        $items = JurnalLifeItem::template()
            ->orderBy('kategori')->orderBy('label')
            ->get(['id', 'kategori', 'label', 'is_default', 'is_active']);
        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kategori' => 'required|in:kerohanian,pendidikan,karakter',
            'label'    => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
        ]);
        $data['student_id'] = null;
        $data['is_default'] = false;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['created_by'] = $request->user()->id;
        $item = JurnalLifeItem::create($data);
        return response()->json(['data' => $item], 201);
    }

    public function update(Request $request, JurnalLifeItem $item): JsonResponse
    {
        $data = $request->validate([
            'kategori'  => 'required|in:kerohanian,pendidikan,karakter',
            'label'     => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? $item->is_active);
        $item->update($data);
        return response()->json(['data' => $item]);
    }

    public function destroy(JurnalLifeItem $item): JsonResponse
    {
        $item->delete();
        return response()->json(['message' => 'deleted']);
    }
}
