<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\KelasMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasMasterController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $q = KelasMaster::with('cabang:id,nama')
            ->withCount(['mentorPresensi', 'presensi'])
            ->orderBy('cabang_id')
            ->orderBy('nama');

        if (! $user->isAdmin()) {
            if ($user->cabang_id) {
                $q->where('cabang_id', $user->cabang_id);
            } else {
                $q->whereRaw('1 = 0');
            }
        }
        if ($request->filled('cabang_id')) {
            $q->where('cabang_id', $request->cabang_id);
        }
        if ($request->filled('q')) {
            $q->where('nama', 'like', '%' . $request->q . '%');
        }

        $kelas = $q->paginate(30)->withQueryString();
        $cabangs = $user->isAdmin()
            ? Cabang::orderBy('nama')->get()
            : Cabang::where('id', $user->cabang_id)->get();

        return view('admin.kelas-master.index', compact('kelas', 'cabangs'));
    }

    public function store(Request $request)
    {
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

        KelasMaster::create($data);
        return back()->with('success', 'Kelas ditambahkan.');
    }

    public function update(Request $request, KelasMaster $kelas)
    {
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
        return back()->with('success', 'Kelas diperbarui.');
    }

    public function destroy(KelasMaster $kelas)
    {
        if ($kelas->mentorPresensi()->exists()) {
            return back()->withErrors(['kelas' => 'Tidak bisa hapus: kelas ini masih dipakai di presensi mentor.']);
        }
        $kelas->delete();
        return back()->with('success', 'Kelas dihapus.');
    }
}
