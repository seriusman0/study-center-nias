<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalWeeklyVerse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JurnalWeeklyVerseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tahun = (int) ($request->tahun ?? now()->year);
        $verses = JurnalWeeklyVerse::where('tahun', $tahun)
            ->orderBy('bulan')
            ->orderBy('minggu')
            ->get();
        return response()->json(['data' => $verses, 'tahun' => $tahun]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tahun'     => 'required|integer|min:2020|max:2100',
            'bulan'     => 'required|integer|min:1|max:12',
            'minggu'    => 'required|integer|min:1|max:4',
            'referensi' => 'required|string|max:100',
            'isi'       => 'required|string|max:5000',
        ]);

        if (JurnalWeeklyVerse::forWeek($data['tahun'], $data['bulan'], $data['minggu'])) {
            throw ValidationException::withMessages(['minggu' => 'Ayat untuk minggu ini sudah ada.']);
        }

        $data['created_by'] = $request->user()->id;
        $verse = JurnalWeeklyVerse::create($data);
        return response()->json(['data' => $verse], 201);
    }

    public function update(Request $request, JurnalWeeklyVerse $weeklyVerse): JsonResponse
    {
        $data = $request->validate([
            'tahun'     => 'required|integer|min:2020|max:2100',
            'bulan'     => 'required|integer|min:1|max:12',
            'minggu'    => 'required|integer|min:1|max:4',
            'referensi' => 'required|string|max:100',
            'isi'       => 'required|string|max:5000',
        ]);

        $exists = JurnalWeeklyVerse::where('tahun', $data['tahun'])
            ->where('bulan', $data['bulan'])
            ->where('minggu', $data['minggu'])
            ->where('id', '!=', $weeklyVerse->id)
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages(['minggu' => 'Ayat untuk minggu ini sudah ada.']);
        }

        $weeklyVerse->update($data);
        return response()->json(['data' => $weeklyVerse]);
    }

    public function destroy(JurnalWeeklyVerse $weeklyVerse): JsonResponse
    {
        $weeklyVerse->delete();
        return response()->json(['message' => 'Ayat dihapus.']);
    }
}
