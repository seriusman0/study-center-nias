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
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'kontak' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['slug'] = Str::slug($validated['nama']);

        $cabang = Cabang::create($validated);
        return response()->json($cabang, 201);
    }

    public function update(Request $request, Cabang $cabang): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['sometimes', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'kontak' => ['nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['nama'])) {
            $validated['slug'] = Str::slug($validated['nama']);
        }

        $cabang->update($validated);
        return response()->json($cabang);
    }

    public function destroy(Cabang $cabang): JsonResponse
    {
        $cabang->delete();
        return response()->json(['message' => 'Cabang dihapus.']);
    }
}
