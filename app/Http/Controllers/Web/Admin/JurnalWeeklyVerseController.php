<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalWeeklyVerse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JurnalWeeklyVerseController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) ($request->tahun ?? now()->year);
        $verses = JurnalWeeklyVerse::where('tahun', $tahun)
            ->orderBy('bulan')
            ->orderBy('minggu')
            ->get();

        return view('admin.jurnal.weekly-verses.index', [
            'verses' => $verses,
            'tahun'  => $tahun,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun'     => 'required|integer|min:2020|max:2100',
            'bulan'     => 'required|integer|min:1|max:12',
            'minggu'    => 'required|integer|min:1|max:4',
            'referensi' => 'required|string|max:100',
            'isi'       => 'required|string|max:5000',
        ]);
        $exists = JurnalWeeklyVerse::forWeek($data['tahun'], $data['bulan'], $data['minggu']);
        if ($exists) {
            return back()->withErrors(['minggu' => 'Ayat untuk minggu ini sudah ada.'])->withInput();
        }
        $data['created_by'] = $request->user()->id;
        JurnalWeeklyVerse::create($data);
        return back()->with('success', 'Ayat hafalan ditambahkan.');
    }

    public function update(Request $request, JurnalWeeklyVerse $weeklyVerse)
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
            return back()->withErrors(['minggu' => 'Ayat untuk minggu ini sudah ada.'])->withInput();
        }

        $weeklyVerse->update($data);
        return back()->with('success', 'Ayat hafalan diperbarui.');
    }

    public function destroy(JurnalWeeklyVerse $weeklyVerse)
    {
        $weeklyVerse->delete();
        return back()->with('success', 'Ayat dihapus.');
    }
}
