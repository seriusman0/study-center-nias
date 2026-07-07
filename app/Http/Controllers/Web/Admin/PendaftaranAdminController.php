<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PendaftaranAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'semua');
        $search = trim($request->query('search', ''));

        $query = User::with(['studentProfile'])
            ->whereHas('studentProfile')
            ->whereHas('roles', fn($q) => $q->where('name', 'student'));

        if ($status !== 'semua') {
            $query->whereHas('studentProfile', fn($q) => $q->where('status', $status));
        }

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $pendaftar = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.pendaftaran.index', compact('pendaftar', 'status', 'search'));
    }

    public function show(User $user)
    {
        abort_unless($user->studentProfile !== null, 404);
        $user->load('studentProfile');
        return view('admin.pendaftaran.show', compact('user'));
    }

    public function validasi(Request $request, User $user)
    {
        abort_unless($user->studentProfile !== null, 404);

        $data = $request->validate([
            'status'        => 'required|in:diterima,ditolak,perbaikan',
            'catatan_admin' => 'nullable|string|max:1000',
        ], [
            'status.required' => 'Status validasi wajib dipilih.',
            'status.in'       => 'Pilih status yang valid.',
            'catatan_admin.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $profile = $user->studentProfile;

        if ($data['status'] === 'diterima') {
            $user->update(['is_active' => true]);
            $profile->update([
                'is_pending'    => false,
                'status'        => 'diterima',
                'catatan_admin' => $data['catatan_admin'] ?? null,
            ]);
        } elseif ($data['status'] === 'ditolak') {
            $user->update(['is_active' => false]);
            $profile->update([
                'is_pending'    => false,
                'status'        => 'ditolak',
                'catatan_admin' => $data['catatan_admin'] ?? null,
            ]);
        } else {
            $profile->update([
                'is_pending'    => true,
                'status'        => 'perbaikan',
                'catatan_admin' => $data['catatan_admin'] ?? null,
            ]);
        }

        return redirect()
            ->route('admin.pendaftaran.index')
            ->with('success', 'Validasi pendaftaran berhasil disimpan.');
    }
}
