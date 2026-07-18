<?php

namespace App\Jobs;

use App\Models\Blog;
use App\Models\Cabang;
use App\Models\CertificateTemplate;
use App\Models\IssuedCertificate;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ProcessWebSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function handle(): void
    {
        $this->syncDatabase();
        $this->syncStorage();
    }

    private function syncDatabase(): void
    {
        $response = Http::timeout(120)
            ->withHeaders(['X-Sync-Key' => config('sync.secret_key')])
            ->get(config('sync.target_url') . '/api/sync/export');

        if (! $response->successful()) {
            Log::error('Sync export fetch failed', ['status' => $response->status()]);
            return;
        }

        $data = $response->json();

        if (! empty($data['cabangs'])) {
            Cabang::upsert($data['cabangs'], ['slug'], [
                'nama', 'alamat', 'kontak', 'foto_wajib', 'pendaftaran_buka',
                'whatsapp_link', 'kelas_min', 'kelas_max', 'mata_pelajaran',
            ]);
        }

        if (! empty($data['mata_pelajarans'])) {
            MataPelajaran::upsert($data['mata_pelajarans'], ['nama'], ['urutan', 'is_active']);
        }

        if (! empty($data['users'])) {
            User::upsert($data['users'], ['email'], [
                'name', 'username', 'password', 'avatar', 'bio', 'cabang_id', 'is_active',
            ]);
        }

        if (! empty($data['blogs'])) {
            Blog::upsert($data['blogs'], ['slug'], [
                'user_id', 'cabang_id', 'title', 'content', 'image', 'published_at',
            ]);
        }

        if (! empty($data['certificate_templates'])) {
            CertificateTemplate::upsert($data['certificate_templates'], ['nama'], [
                'deskripsi', 'html_content', 'orientation', 'paper_size', 'logo_path', 'is_active', 'created_by',
            ]);
        }

        if (! empty($data['issued_certificates'])) {
            IssuedCertificate::upsert($data['issued_certificates'], ['nomor_sertifikat'], [
                'user_id', 'template_id', 'issued_by', 'tanggal_lulus', 'nama_kursus', 'file_path', 'issued_at',
            ]);
        }

        Log::info('Sync database completed successfully.');
    }

    private function syncStorage(): void
    {
        $source = config('sync.rsync_source');

        if (! $source) {
            Log::warning('SYNC_RSYNC_SOURCE not configured. Skipping storage sync.');
            return;
        }

        $dest   = storage_path('app/public/');
        $result = Process::run("rsync -avz --update {$source} {$dest}");

        if (! $result->successful()) {
            Log::error('rsync failed: ' . $result->errorOutput());
        } else {
            Log::info('Sync storage completed successfully.');
        }
    }
}
