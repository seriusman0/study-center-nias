<?php

namespace App\Jobs;

use App\Models\JurnalPhotoScan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessJurnalPhotoScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(public readonly int $scanId) {}

    public function handle(): void
    {
        $scan = JurnalPhotoScan::findOrFail($this->scanId);
        $scan->update(['status' => 'processing']);

        try {
            $imageData = Storage::disk('local')->get($scan->image_path);
            $base64    = base64_encode($imageData);
            $mimeType  = $this->detectMime($scan->image_path);

            $prompt = <<<'PROMPT'
Ini adalah foto jurnal harian siswa Study Center NIAS yang sudah diisi manual dengan pulpen.
Template jurnal memiliki kolom: Hari/Tanggal | PL | PB | Ayat | Baca Alkitab | Hafal Ayat | Berdoa Pagi | Kelas SC | Pem. Minggu | Pem. Sabtu | Sapa Ortu/Guru | Rapikan Kasur.

Tugas kamu: baca foto ini dan ekstrak data yang terisi.

Kembalikan HANYA JSON berikut (tidak ada teks lain di luar JSON):
{
  "nama_siswa": "nama lengkap siswa dari header template",
  "cabang": "nama cabang dari header template",
  "minggu_label": "contoh: Minggu 32 (3 Agt - 9 Agt 2026)",
  "hari": {
    "YYYY-MM-DD": {
      "pl": true,
      "pb": false,
      "ayat": true,
      "Baca Alkitab": true,
      "Hafal Ayat": false,
      "Berdoa Pagi": true,
      "Kelas SC": false,
      "Pem. Minggu": false,
      "Pem. Sabtu": true,
      "Sapa Ortu/Guru": true,
      "Rapikan Kasur": false
    }
  }
}

Aturan:
- Gunakan format tanggal YYYY-MM-DD untuk key di "hari"
- Sel dengan tanda centang, contreng, atau coretan apapun = true
- Sel kosong atau tidak terisi = false
- Jika tanggal di foto tidak ada tahunnya, cari dari konteks minggu yang tertulis
- Hanya kembalikan JSON, tidak ada penjelasan lain
PROMPT;

            $apiKey  = config('services.anthropic.key');
            $headers = [
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ];
            // OAuth token (oat01) pakai Authorization Bearer, API key biasa pakai x-api-key
            if (str_starts_with($apiKey, 'sk-ant-oat')) {
                $headers['Authorization'] = 'Bearer ' . $apiKey;
            } else {
                $headers['x-api-key'] = $apiKey;
            }

            $response = Http::withHeaders($headers)->timeout(50)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 2048,
                'messages'   => [[
                    'role'    => 'user',
                    'content' => [
                        [
                            'type'   => 'image',
                            'source' => [
                                'type'       => 'base64',
                                'media_type' => $mimeType,
                                'data'       => $base64,
                            ],
                        ],
                        ['type' => 'text', 'text' => $prompt],
                    ],
                ]],
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Claude API error: ' . $response->body());
            }

            $text = $response->json('content.0.text', '');

            // Ekstrak JSON dari respons
            $jsonStr = $this->extractJson($text);
            $parsed  = json_decode($jsonStr, true);

            if (! $parsed || ! isset($parsed['hari'])) {
                throw new \RuntimeException('Gagal parse JSON dari Claude: ' . $text);
            }

            $scan->update([
                'status'      => 'done',
                'result_json' => $parsed,
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessJurnalPhotoScan gagal', ['scan_id' => $this->scanId, 'error' => $e->getMessage()]);
            $scan->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function detectMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    private function extractJson(string $text): string
    {
        // Ambil blok JSON pertama yang ditemukan
        if (preg_match('/\{.*\}/s', $text, $m)) {
            return $m[0];
        }
        return $text;
    }
}
