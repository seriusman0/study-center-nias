<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalLifeItem;
use Illuminate\Http\Request;

class CollegeItemController extends Controller
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

        return view('admin.college-jurnal.items', [
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

        JurnalLifeItem::create([
            'kategori'      => $data['kategori'],
            'response_type' => $data['response_type'],
            'label'         => $data['label'],
            'is_default'    => false,
            'is_active'     => true,
            'created_by'    => $request->user()->id,
        ]);

        return back()->with('success', 'Item ditambahkan.');
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

        return back()->with('success', 'Item diperbarui.');
    }

    public function destroy(JurnalLifeItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item dihapus.');
    }
}
