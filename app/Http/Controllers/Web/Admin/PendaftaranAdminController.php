<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Role;
use App\Models\User;
use App\Services\JurnalSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PendaftaranAdminController extends Controller
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

        $pendaftar = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $cabangs   = Cabang::orderBy('nama')->get();

        return view('admin.pendaftaran.index', compact('pendaftar', 'status', 'search', 'cabangId', 'cabangs'));
    }

    public function show(User $user)
    {
        abort_unless($user->studentProfile !== null, 404);
        $user->load(['studentProfile', 'cabang']);
        $cabangs = Cabang::orderBy('nama')->get();
        return view('admin.pendaftaran.show', compact('user', 'cabangs'));
    }

    public function validasi(Request $request, User $user)
    {
        abort_unless($user->studentProfile !== null, 404);

        $data = $request->validate([
            'status'        => 'required|in:diterima,ditolak,perbaikan',
            'catatan_admin' => 'nullable|string|max:1000',
            'cabang_id'     => 'nullable|exists:cabangs,id',
            'target_role'   => 'required_if:status,diterima|nullable|in:student,scholarship_teenager,college',
        ], [
            'status.required'      => 'Status validasi wajib dipilih.',
            'status.in'            => 'Pilih status yang valid.',
            'catatan_admin.max'    => 'Catatan maksimal 1000 karakter.',
            'cabang_id.exists'     => 'Cabang tidak valid.',
            'target_role.required_if' => 'Pilih role akun ketika status Diterima.',
            'target_role.in'       => 'Role tidak valid.',
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

            // Assign role yang dipilih admin
            $targetRole = $data['target_role'];
            $role = Role::where('name', $targetRole)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }

            // Auto-setup jurnal items sesuai role
            app(JurnalSetupService::class)->setupForRole($user, $targetRole);
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
