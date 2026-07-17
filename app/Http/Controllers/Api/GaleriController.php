<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = Presensi::where('cabang_id', $user->cabang_id)
            ->whereNotNull('foto')
            ->orderByDesc('tanggal')
            ->limit(20)
            ->get(['id', 'foto', 'tanggal'])
            ->map(fn($p) => [
                'id'       => $p->id,
                'foto_url' => Storage::url($p->foto),
                'tanggal'  => $p->tanggal->toDateString(),
            ]);

        return response()->json(['data' => $items]);
    }
}
