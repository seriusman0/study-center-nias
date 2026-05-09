<?php

namespace App\Http\Controllers;

use App\Models\CvData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CvController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cv = $request->user()->cvData ?? new CvData(['user_id' => $request->user()->id]);
        return response()->json($cv);
    }

    public function showPublic(string $username): JsonResponse
    {
        $user = \App\Models\User::where('username', $username)
            ->where('cv_enabled', true)
            ->where('is_active', true)
            ->firstOrFail();

        $cv = $user->cvData;
        return response()->json([
            'user' => $user->only('id', 'name', 'username', 'avatar', 'bio') + ['role' => $user->role?->name, 'cabang' => $user->cabang?->nama],
            'cv' => $cv,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pendidikan' => ['nullable', 'array'],
            'pendidikan.*.jenjang' => ['required', 'string'],
            'pendidikan.*.institusi' => ['required', 'string'],
            'pendidikan.*.tahun_lulus' => ['nullable', 'string'],
            'pengalaman' => ['nullable', 'array'],
            'pengalaman.*.posisi' => ['required', 'string'],
            'pengalaman.*.deskripsi' => ['nullable', 'string'],
            'pengalaman.*.tahun' => ['nullable', 'string'],
            'keterampilan' => ['nullable', 'array'],
            'keterampilan.*' => ['string'],
            'portofolio' => ['nullable', 'string'],
            'template' => ['nullable', 'in:template1,template2,template3'],
        ]);

        $cv = CvData::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json($cv);
    }
}
