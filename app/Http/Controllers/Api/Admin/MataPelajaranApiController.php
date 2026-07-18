<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MataPelajaranApiController extends Controller
{
    public function index()
    {
        $items = MataPelajaran::orderBy('urutan')->orderBy('nama')->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'    => 'required|string|max:100|unique:mata_pelajarans,nama',
            'urutan'  => 'nullable|integer|min:0',
        ]);

        $item = MataPelajaran::create([
            'nama'      => strtoupper(trim($request->nama)),
            'urutan'    => $request->filled('urutan') ? (int) $request->urutan : 0,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Mata pelajaran ditambahkan.',
            'data'    => $item
        ], 201);
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $request->validate([
            'nama'      => 'required|string|max:100|unique:mata_pelajarans,nama,' . $mataPelajaran->id,
            'urutan'    => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $mataPelajaran->update([
            'nama'      => strtoupper(trim($request->nama)),
            'urutan'    => $request->filled('urutan') ? (int) $request->urutan : 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Mata pelajaran diperbarui.',
            'data'    => $mataPelajaran
        ]);
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->delete();
        return response()->json(['message' => 'Mata pelajaran dihapus.']);
    }

    public function toggleActive(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->update(['is_active' => ! $mataPelajaran->is_active]);
        return response()->json([
            'message' => 'Status diperbarui.',
            'data'    => $mataPelajaran
        ]);
    }
}
