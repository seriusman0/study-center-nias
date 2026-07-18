<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalLifeItem;
use Illuminate\Http\Request;

class CollegeItemApiController extends Controller
{
    private const KATEGORI = ['pembacaan', 'sidang', 'rohani'];
    private const RESPONSE_TYPES = ['check', 'boolean', 'time_range'];

    public function index(Request $request)
    {
        $items = JurnalLifeItem::whereIn('kategori', self::KATEGORI)
            ->whereNull('student_id')
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();

        return response()->json([
            'items'         => $items,
            'kategoriList'  => self::KATEGORI,
            'responseTypes' => self::RESPONSE_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori'      => 'required|in:' . implode(',', self::KATEGORI),
            'label'         => 'required|string|max:255',
            'response_type' => 'required|in:' . implode(',', self::RESPONSE_TYPES),
        ]);

        $item = JurnalLifeItem::create([
            'kategori'      => $data['kategori'],
            'response_type' => $data['response_type'],
            'label'         => $data['label'],
            'is_default'    => false,
            'is_active'     => true,
            'created_by'    => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Item ditambahkan.',
            'data' => $item
        ], 201);
    }

    public function update(Request $request, JurnalLifeItem $item)
    {
        $data = $request->validate([
            'kategori'      => 'required|in:' . implode(',', self::KATEGORI),
            'label'         => 'required|string|max:255',
            'response_type' => 'required|in:' . implode(',', self::RESPONSE_TYPES),
            'is_active'     => 'nullable|boolean',
        ]);

        $item->update([
            'kategori'      => $data['kategori'],
            'response_type' => $data['response_type'],
            'label'         => $data['label'],
            'is_active'     => (bool) ($data['is_active'] ?? true),
        ]);

        return response()->json([
            'message' => 'Item diperbarui.',
            'data' => $item
        ]);
    }

    public function destroy(JurnalLifeItem $item)
    {
        $item->delete();
        return response()->json(['message' => 'Item dihapus.']);
    }
}
