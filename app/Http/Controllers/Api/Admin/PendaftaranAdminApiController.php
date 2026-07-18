<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PendaftaranAdminApiController extends Controller
{
    public function index(Request $request)
    {
        $status   = $request->query('status', 'semua');
        $search   = trim($request->query('search', ''));
        $cabangId = $request->query('cabang_id', '');

        $query = User::with(['studentProfile', 'cabang'])
            ->whereHas('studentProfile')
            ->whereHas('roles', fn($q) => $q->where('name', 'student'));

        if ($status !== 'semua') {
            $query->whereHas('studentProfile', fn($q) => $q->where('status', $status));
        }

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($cabangId !== '') {
            $query->where('cabang_id', $cabangId);
        }

        $pendaftar = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($pendaftar);
    }

    public function show(User $user)
    {
        abort_unless($user->studentProfile !== null, 404);
        $user->load(['studentProfile', 'cabang']);
        return response()->json($user);
    }

    public function validasi(Request $request, User $user)
    {
        abort_unless($user->studentProfile !== null, 404);

        $data = $request->validate([
            'status'        => 'required|in:diterima,ditolak,perbaikan',
            'catatan_admin' => 'nullable|string|max:1000',
            'cabang_id'     => 'nullable|exists:cabangs,id',
        ], [
            'status.required'   => 'Status validasi wajib dipilih.',
            'status.in'         => 'Pilih status yang valid.',
            'catatan_admin.max' => 'Catatan maksimal 1000 karakter.',
            'cabang_id.exists'  => 'Cabang tidak valid.',
        ]);

        if (!empty($data['cabang_id'])) {
            $user->update(['cabang_id' => $data['cabang_id']]);
        }

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

        return response()->json([
            'message' => 'Validasi pendaftaran berhasil disimpan.',
            'data'    => $user->load('studentProfile')
        ]);
    }

    public function generateUpdateLink(User $user)
    {
        abort_unless($user->studentProfile !== null, 404);

        $token   = Str::random(64);
        $expires = now()->addDays(7);

        $user->studentProfile->update([
            'update_token'            => $token,
            'update_token_expires_at' => $expires,
        ]);

        return response()->json([
            'url'        => route('pendaftaran.update.form', $token),
            'expires_at' => $expires->translatedFormat('d M Y, H:i'),
        ]);
    }
}
