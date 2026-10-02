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
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $token = env('TELEGRAM_BOT_TOKEN');
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
        $grouped = []; // [cabangName][roleName][] = userName
        foreach ($users as $user) {
            $cabangName = htmlspecialchars(($user->cabang && !empty($user->cabang->nama)) ? $user->cabang->nama : 'Lainnya');
            $safeName   = htmlspecialchars($user->name);
            $userRoles  = $user->roles->whereIn('name', $roleOrder)->pluck('name')->toArray();

            $isDone = in_array($user->id, $doneUserIds);

            if ($type === 'missing' && $isDone)   continue; // laporan missing: skip yang sudah isi
            if ($type !== 'missing' && !$isDone)  continue; // laporan rekap: skip yang belum isi

            foreach ($userRoles as $role) {
                $grouped[$cabangName][$role][] = $safeName;
            }
        }

        // Bangun pesan
        $message = '';
        if ($type === 'missing') {
            $message = "⚠️ <b>Laporan Jurnal Kosong</b> ($date)\n\n";
            $message .= "Daftar anak/mahasiswa yang <b>belum</b> mengisi jurnal hari ini:\n";
        } else {
            $timeLabel = $type === 'morning' ? 'Pagi' : ($type === 'afternoon' ? 'Siang' : 'Malam');
            $message = "📊 <b>Rekap Jurnal {$timeLabel}</b> ($date)\n\n";
            $message .= "Daftar yang <b>sudah aktif</b> mengisi jurnal:\n";
        }

        // Kumpulkan semua pesan: 1 pesan per kombinasi Cabang+Role
        $messages = [];

        if (empty($grouped)) {
            // Satu pesan global jika tidak ada data
            if ($type === 'missing') {
                $messages[] = $message . "\n🎉 <b>Luar biasa! Semua orang sudah mengisi jurnalnya hari ini.</b>";
            } else {
                $messages[] = $message . "\n💤 <i>Belum ada yang mengisi jurnal sejauh ini.</i>";
            }
        } else {
            ksort($grouped);
            foreach ($grouped as $cabang => $roleData) {
                foreach ($roleOrder as $role) {
                    if (!isset($roleData[$role]) || empty($roleData[$role])) continue;

                    $label   = $roleLabels[$role] ?? $role;
                    $members = $roleData[$role];
                    sort($members);

                    $msg = $message; // header (judul + tanggal)
                    $msg .= "\n📍 <b>Cabang {$cabang}</b> — {$label}\n";

                    $count = 0;
                    foreach ($members as $name) {
                        $count++;
                        if ($count <= 40) {
                            $prefix = ($type === 'missing') ? '➖' : '✅';
                            $msg .= "  {$prefix} {$name}\n";
                        }
                    }
                    if (count($members) > 40) {
                        $msg .= "  ... (dan " . (count($members) - 40) . " lainnya)\n";
                    }
                    $msg .= "\n<i>Total: " . count($members) . " orang</i>";

                    $messages[] = $msg;
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
                // Jeda kecil agar tidak kena rate limit Telegram
                usleep(300000); // 0.3 detik
            }
        }

        $this->info('Report sent successfully. Total messages: ' . count($messages));
    }
}
