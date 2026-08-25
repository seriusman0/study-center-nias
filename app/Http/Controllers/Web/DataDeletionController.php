<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class DataDeletionController extends Controller
{
    /**
     * Tampilkan halaman informasi penghapusan data (public, tanpa login).
     */
    public function show()
    {
        return view('data-deletion');
    }

    /**
     * Proses permintaan penghapusan akun (harus login).
     * Melakukan soft-delete akun dan anonimisasi data identitas.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'konfirmasi' => ['required', 'in:HAPUS'],
        ], [
            'konfirmasi.required' => 'Ketik HAPUS untuk mengkonfirmasi.',
            'konfirmasi.in'       => 'Ketik kata HAPUS (huruf kapital) untuk mengkonfirmasi.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        Log::info('Permintaan penghapusan akun', [
            'user_id'  => $user->id,
            'username' => $user->username,
            'email'    => $user->email,
            'ip'       => $request->ip(),
        ]);

        // Hapus semua token Sanctum (API sessions)
        $user->tokens()->delete();

        // Anonimisasi data identitas sebelum soft-delete
        $user->update([
            'name'           => 'Akun Dihapus',
            'username'       => 'deleted_' . $user->id . '_' . time(),
            'email'          => 'deleted_' . $user->id . '_' . time() . '@deleted.invalid',
            'avatar'         => null,
            'bio'            => null,
            'google_id'      => null,
            'is_active'      => false,
            'profile_public' => false,
            'cv_enabled'     => false,
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Soft-delete record
        $user->delete();

        return redirect('/')->with(
            'success',
            'Akun Anda telah berhasil dihapus. Terima kasih telah menggunakan layanan Study Center Nias.'
        );
    }
}
