<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $items = MataPelajaran::orderBy('urutan')->orderBy('nama')->get();
        return view('admin.mata-pelajaran', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'    => 'required|string|max:100|unique:mata_pelajarans,nama',
            'urutan'  => 'nullable|integer|min:0',
        ]);

        MataPelajaran::create([
            'nama'      => strtoupper(trim($request->nama)),
            'urutan'    => $request->filled('urutan') ? (int) $request->urutan : 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Mata pelajaran ditambahkan.');
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

        return back()->with('success', 'Mata pelajaran diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->delete();
        return back()->with('success', 'Mata pelajaran dihapus.');
    }

    public function toggleActive(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->update(['is_active' => ! $mataPelajaran->is_active]);
        return back()->with('success', 'Status diperbarui.');
    }
}
