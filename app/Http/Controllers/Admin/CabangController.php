<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CabangController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Cabang::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'alamat'           => 'nullable|string',
            'kontak'           => 'nullable|string',
            'foto_wajib'       => 'nullable|boolean',
            'pendaftaran_buka' => 'nullable|boolean',
            'whatsapp_link'    => 'nullable|url|max:500',
            'kelas_min'        => 'nullable|integer|min:1|max:12',
            'kelas_max'        => 'nullable|integer|min:1|max:12|gte:kelas_min',
            'mata_pelajaran'   => 'nullable|array',
            'mata_pelajaran.*' => 'string|max:100',
        ]);

        $cabang = Cabang::create([
            'nama'             => $request->nama,
            'slug'             => \Illuminate\Support\Str::slug($request->nama),
            'alamat'           => $request->alamat,
            'kontak'           => $request->kontak,
            'foto_wajib'       => $request->boolean('foto_wajib', true),
            'pendaftaran_buka' => $request->boolean('pendaftaran_buka', true),
            'whatsapp_link'    => $request->input('whatsapp_link') ?: null,
            'kelas_min'        => $request->filled('kelas_min') ? (int) $request->kelas_min : null,
            'kelas_max'        => $request->filled('kelas_max') ? (int) $request->kelas_max : null,
            'mata_pelajaran'   => $request->input('mata_pelajaran') ?: [],
        ]);

        return response()->json($cabang, 201);
    }

    public function update(Request $request, Cabang $cabang): JsonResponse
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'alamat'           => 'nullable|string',
            'kontak'           => 'nullable|string',
            'foto_wajib'       => 'nullable|boolean',
            'pendaftaran_buka' => 'nullable|boolean',
            'whatsapp_link'    => 'nullable|url|max:500',
            'kelas_min'        => 'nullable|integer|min:1|max:12',
            'kelas_max'        => 'nullable|integer|min:1|max:12|gte:kelas_min',
            'mata_pelajaran'   => 'nullable|array',
            'mata_pelajaran.*' => 'string|max:100',
        ]);

        $cabang->update([
            'nama'             => $request->nama,
            'slug'             => \Illuminate\Support\Str::slug($request->nama),
            'alamat'           => $request->alamat,
            'kontak'           => $request->kontak,
            'foto_wajib'       => $request->boolean('foto_wajib', false),
            'pendaftaran_buka' => $request->boolean('pendaftaran_buka', false),
            'whatsapp_link'    => $request->input('whatsapp_link') ?: null,
            'kelas_min'        => $request->filled('kelas_min') ? (int) $request->kelas_min : null,
            'kelas_max'        => $request->filled('kelas_max') ? (int) $request->kelas_max : null,
            'mata_pelajaran'   => $request->input('mata_pelajaran') ?: [],
        ]);

        return response()->json($cabang);
    }

    public function destroy(Cabang $cabang): JsonResponse
    {
        $cabang->delete();
        return response()->json(['message' => 'Cabang dihapus.']);
    }
}
