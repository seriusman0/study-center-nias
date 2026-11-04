<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;

#[Signature('journal:report {type}')]
#[Description('Send journal report to telegram. type can be: morning, afternoon, night, missing')]
class SendJournalReport extends Command
{
    const MAX_LEN = 4000; // batas aman per pesan Telegram (max 4096)

    /**
     * Pecah teks panjang menjadi array pesan, masing-masing ≤ MAX_LEN karakter.
     * Hanya memotong di batas baris (newline) agar tidak memotong nama orang.
     */
    private function splitMessage(string $header, string $body, string $footer): array
    {
        $lines  = explode("\n", $body);
        $chunks = [];
        $current = $header;

        foreach ($lines as $line) {
            $candidate = $current . $line . "\n";
            // +footer untuk cek apakah masih muat
            if (strlen($candidate . $footer) > self::MAX_LEN && $current !== $header) {
                // Simpan chunk saat ini, mulai chunk baru
                $chunks[]  = rtrim($current) . "\n" . $footer;
                $current   = $header . $line . "\n";
            } else {
                $current = $candidate;
            }
        }

        // Sisa terakhir
        if (trim($current) !== trim($header)) {
            $chunks[] = rtrim($current) . "\n" . $footer;
        } elseif (empty($chunks)) {
            $chunks[] = $header . $footer;
        }

        return $chunks;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type    = $this->argument('type');
        $token   = env('TELEGRAM_BOT_TOKEN');
        $chatIds = explode(',', env('TELEGRAM_CHAT_ID', ''));

        if (!$token || empty(array_filter($chatIds))) {
            $this->error('Telegram bot token or chat ID is not set in .env');
            return;
        }

        $date = now()->timezone('Asia/Jakarta')->format('d-m-Y');

        $roleLabels = [
            'student'              => '🎒 Pelajar',
            'college'              => '🎓 Mahasiswa',
            'scholarship_teenager' => '⭐ Beasiswa Remaja',
        ];
        $roleOrder = ['student', 'college', 'scholarship_teenager'];

        // Ambil user aktif dengan role jurnal beserta cabang & roles
        $users = User::with(['cabang', 'roles'])
            ->whereHas('roles', function ($q) use ($roleOrder) {
                $q->whereIn('name', $roleOrder);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Kumpulkan user ID yang sudah isi jurnal hari ini
        $journalDoneIds = \App\Models\JurnalEntry::whereDate('created_at', now())
            ->pluck('student_id')
            ->toArray();
        $collegeDoneIds = \Illuminate\Support\Facades\DB::table('college_study_logs')
            ->whereDate('created_at', now())
            ->pluck('user_id')
            ->toArray();
        $doneUserIds = array_unique(array_merge($journalDoneIds, $collegeDoneIds));

        // Kelompokkan: [cabang][role] => [nama, ...]
        $grouped = [];
        foreach ($users as $user) {
            $cabangName = htmlspecialchars(($user->cabang && !empty($user->cabang->nama)) ? $user->cabang->nama : 'Lainnya');
            $safeName   = htmlspecialchars($user->name);
            $userRoles  = $user->roles->whereIn('name', $roleOrder)->pluck('name')->toArray();

            $isDone = in_array($user->id, $doneUserIds);

            if ($type === 'missing' && $isDone)  continue;
            if ($type !== 'missing' && !$isDone) continue;

            foreach ($userRoles as $role) {
                $grouped[$cabangName][$role][] = $safeName;
            }
        }

        // Header global (judul + tanggal)
        if ($type === 'missing') {
            $globalHeader = "⚠️ <b>Laporan Jurnal Kosong</b> ($date)\n\n"
                          . "Daftar anak/mahasiswa yang <b>belum</b> mengisi jurnal hari ini:\n";
        } else {
            $timeLabel    = $type === 'morning' ? 'Pagi' : ($type === 'afternoon' ? 'Siang' : 'Malam');
            $globalHeader = "📊 <b>Rekap Jurnal {$timeLabel}</b> ($date)\n\n"
                          . "Daftar yang <b>sudah aktif</b> mengisi jurnal:\n";
        }

        // Kumpulkan semua pesan: 1 pesan per kombinasi Cabang+Role,
        // dipecah otomatis jika terlalu panjang
        $messages = [];

        if (empty($grouped)) {
            if ($type === 'missing') {
                $messages[] = $globalHeader . "\n🎉 <b>Luar biasa! Semua orang sudah mengisi jurnalnya hari ini.</b>";
            } else {
                $messages[] = $globalHeader . "\n💤 <i>Belum ada yang mengisi jurnal sejauh ini.</i>";
            }
        } else {
            $prefix = ($type === 'missing') ? '➖' : '✅';

            ksort($grouped);
            foreach ($grouped as $cabang => $roleData) {
                foreach ($roleOrder as $role) {
                    if (!isset($roleData[$role]) || empty($roleData[$role])) continue;

                    $label   = $roleLabels[$role] ?? $role;
                    $members = $roleData[$role];
                    sort($members);
                    $total   = count($members);

                    // Header per blok Cabang+Role
                    $blockHeader = $globalHeader
                                 . "\n📍 <b>Cabang {$cabang}</b> — {$label}\n";

                    // Footer per blok (total)
                    $blockFooter = "\n<i>Total: {$total} orang</i>";

                    // Baris nama
                    $body = '';
                    foreach ($members as $name) {
                        $body .= "  {$prefix} {$name}\n";
                    }

                    // Pecah jika perlu
                    $chunks = $this->splitMessage($blockHeader, $body, $blockFooter);
                    foreach ($chunks as $i => $chunk) {
                        // Jika lebih dari 1 chunk, tambahkan penanda lanjutan
                        if (count($chunks) > 1) {
                            $part  = $i + 1;
                            $total_parts = count($chunks);
                            // Ganti footer chunk pertama & tengah
                            if ($i < count($chunks) - 1) {
                                $chunk = rtrim($chunk, "\n") . "\n<i>(lanjut...)</i>";
                            }
                        }
                        $messages[] = $chunk;
                    }
                }
            }
        }

        // Kirim setiap pesan ke semua chat ID
        foreach ($chatIds as $chatId) {
            $chatId = trim($chatId);
            if (!$chatId) continue;

            foreach ($messages as $msg) {
                try {
                    $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id'    => $chatId,
                        'text'       => $msg,
                        'parse_mode' => 'HTML',
                    ]);
                    if (!$response->successful()) {
                        $this->error('Telegram API Error: ' . $response->body());
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to send Telegram notification to {$chatId}: " . $e->getMessage());
                }
                usleep(300000); // 0.3 detik jeda anti rate-limit
            }
        }

        $this->info('Report sent successfully. Total messages: ' . count($messages));
    }
}
