<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Cabang;
use App\Models\CertificateTemplate;
use App\Models\IssuedCertificate;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\Request;

class SyncProviderController extends Controller
{
    public function export(Request $request)
    {
        $secret = config('sync.secret_key');

        if (! $secret || $request->header('X-Sync-Key') !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'cabangs' => Cabang::all([
                'id', 'nama', 'slug', 'alamat', 'kontak', 'foto_wajib',
                'pendaftaran_buka', 'whatsapp_link', 'kelas_min', 'kelas_max', 'mata_pelajaran',
            ])->toArray(),

            'mata_pelajarans' => MataPelajaran::all([
                'id', 'nama', 'urutan', 'is_active',
            ])->toArray(),

            'users' => User::all([
                'id', 'name', 'username', 'email', 'password', 'avatar',
                'bio', 'cabang_id', 'is_active',
            ])->toArray(),

            'blogs' => Blog::all([
                'id', 'user_id', 'cabang_id', 'title', 'slug', 'content', 'image', 'published_at',
            ])->toArray(),

            'certificate_templates' => CertificateTemplate::all([
                'id', 'nama', 'deskripsi', 'html_content', 'orientation',
                'paper_size', 'logo_path', 'is_active', 'created_by',
            ])->toArray(),

            'issued_certificates' => IssuedCertificate::all([
                'id', 'nomor_sertifikat', 'user_id', 'template_id', 'issued_by',
                'tanggal_lulus', 'nama_kursus', 'file_path', 'issued_at',
            ])->toArray(),
        ]);
    }
}
