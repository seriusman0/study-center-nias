<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KelasMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasMasterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = KelasMaster::with('cabang:id,nama')->orderBy('nama');

        if ($request->filled('cabang_id')) {
            $q->where('cabang_id', $request->cabang_id);
        } elseif (! $user->isAdmin() && $user->cabang_id) {
            // Prefer mentor's own cabang, but if empty fall through to all kelas
            $own = (clone $q)->where('cabang_id', $user->cabang_id)
                ->when($request->boolean('active', true), fn($w) => $w->where('is_active', true))
                ->limit(1)->exists();
            if ($own) $q->where('cabang_id', $user->cabang_id);
        }
        if ($request->boolean('active', true)) $q->where('is_active', true);
        if ($request->filled('q')) $q->where('nama', 'like', '%' . $request->q . '%');

        return response()->json([
            'data' => $q->limit(100)->get()->map(fn($k) => [
                'id'        => $k->id,
                'nama'      => $k->nama,
                'cabang_id' => $k->cabang_id,
                'cabang'    => $k->cabang?->nama,
                'keterangan'=> $k->keterangan,
                'is_active' => $k->is_active,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || $request->user()->hasRole('mentor'), 403);

        $data = $request->validate([
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('kelas_master', 'nama')->where('cabang_id', $request->cabang_id)->whereNull('deleted_at'),
            ],
            'cabang_id'  => 'required|exists:cabangs,id',
            'keterangan' => 'nullable|string|max:255',
            'is_active'  => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $kelas = KelasMaster::create($data);
        return response()->json(['data' => $kelas], 201);
    }

    public function update(Request $request, KelasMaster $kelas): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || $request->user()->hasRole('mentor'), 403);

        $data = $request->validate([
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('kelas_master', 'nama')
                    ->where('cabang_id', $request->cabang_id)
                    ->ignore($kelas->id)
                    ->whereNull('deleted_at'),
            ],
            'cabang_id'  => 'required|exists:cabangs,id',
            'keterangan' => 'nullable|string|max:255',
            'is_active'  => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? $kelas->is_active);

        $kelas->update($data);
        return response()->json(['data' => $kelas]);
    }

    public function destroy(Request $request, KelasMaster $kelas): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || $request->user()->hasRole('mentor'), 403);

        if ($kelas->mentorPresensi()->exists()) {
            return response()->json(['message' => 'Kelas dipakai di presensi mentor, tidak bisa dihapus.'], 422);
        }
        $kelas->delete();
        return response()->json(['message' => 'deleted']);
    }
}
