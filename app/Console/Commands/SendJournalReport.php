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
        
        $message = "";
        if ($type === 'missing') {
            $message = "⚠️ <b>Laporan Jurnal Kosong</b> ($date)\n\n";
            $message .= "Berikut adalah daftar anak/mahasiswa yang <b>belum</b> mengisi jurnal mereka hari ini:\n";
            
            $users = User::with('cabang')->whereHas('roles', function($q) {
                $q->whereIn('name', ['student', 'college', 'scholarship_teenager']);
            })->where('is_active', true)->orderBy('name')->get();

            $missingByCabang = [];
            foreach ($users as $user) {
                $hasEntry = \App\Models\JurnalEntry::where('student_id', $user->id)->whereDate('created_at', now())->exists();
                $hasCollegeLog = \Illuminate\Support\Facades\DB::table('college_study_logs')->where('user_id', $user->id)->whereDate('created_at', now())->exists();
                
                if (!$hasEntry && !$hasCollegeLog) {
                    $cabangName = htmlspecialchars(($user->cabang && !empty($user->cabang->nama)) ? $user->cabang->nama : 'Lainnya');
                    $safeName = htmlspecialchars($user->name);
                    $missingByCabang[$cabangName][] = "➖ {$safeName}";
                }
            }

            if (empty($missingByCabang)) {
                $message .= "🎉 <b>Luar biasa! Semua orang sudah mengisi jurnalnya hari ini.</b>";
            } else {
                ksort($missingByCabang);
                foreach ($missingByCabang as $cabang => $members) {
                    $message .= "\n📍 <b>Cabang {$cabang}</b>:\n";
                    $branchCount = 0;
                    foreach ($members as $member) {
                        $branchCount++;
                        if ($branchCount <= 40) {
                            $message .= "{$member}\n";
                        }
                    }
                    if (count($members) > 40) {
                        $message .= "... (dan " . (count($members) - 40) . " lainnya di cabang ini)\n";
                    }
                }
            }
        } else {
            $timeLabel = $type === 'morning' ? 'Pagi' : ($type === 'afternoon' ? 'Siang' : 'Malam');
            $message = "📊 <b>Rekap Jurnal {$timeLabel}</b> ($date)\n\n";
            $message .= "Berikut adalah daftar yang <b>sudah aktif</b> mengisi jurnal:\n";
            
            $journals = \App\Models\JurnalEntry::with('student')->whereDate('created_at', now())->latest()->get();
            $collegeLogs = \Illuminate\Support\Facades\DB::table('college_study_logs')
                ->join('users', 'college_study_logs.user_id', '=', 'users.id')
                ->whereDate('college_study_logs.created_at', now())
                ->select('users.name')
                ->distinct()
                ->get();
                
            $doneUsers = [];
            foreach ($journals as $j) {
                if ($j->student) {
                    $cabang = htmlspecialchars(($j->student->cabang && !empty($j->student->cabang->nama)) ? $j->student->cabang->nama : 'Lainnya');
                    $safeName = htmlspecialchars($j->student->name);
                    $doneUsers[$cabang][$safeName] = true;
                }
            }
            foreach ($collegeLogs as $log) {
                $user = User::with('cabang')->where('name', $log->name)->first();
                $cabang = htmlspecialchars(($user && $user->cabang && !empty($user->cabang->nama)) ? $user->cabang->nama : 'Lainnya');
                $safeName = htmlspecialchars($log->name);
                $doneUsers[$cabang][$safeName] = true;
            }
            
            if (empty($doneUsers)) {
                $message .= "💤 <i>Belum ada yang mengisi jurnal sejauh ini.</i>";
            } else {
                ksort($doneUsers);
                foreach ($doneUsers as $cabang => $members) {
                    $message .= "\n📍 <b>Cabang {$cabang}</b>:\n";
                    $names = array_keys($members);
                    sort($names);
                    $branchCount = 0;
                    foreach ($names as $name) {
                        $branchCount++;
                        if ($branchCount <= 40) {
                            $message .= "✅ {$name}\n";
                        }
                    }
                    if (count($names) > 40) {
                        $message .= "... (dan " . (count($names) - 40) . " lainnya di cabang ini)\n";
                    }
                }
            }
        }

        foreach ($chatIds as $chatId) {
            $chatId = trim($chatId);
            if (!$chatId) continue;

            try {
                $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);
                if (!$response->successful()) {
                    $this->error('Telegram API Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("Failed to send Telegram notification to {$chatId}: " . $e->getMessage());
            }
        }
        $this->info('Report sent successfully.');
    }
}
