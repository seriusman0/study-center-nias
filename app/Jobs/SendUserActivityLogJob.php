<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendUserActivityLogJob implements ShouldQueue
{
    use Queueable;

    public $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatIds = explode(',', env('TELEGRAM_CHAT_ID', ''));

        if (!$token || empty(array_filter($chatIds))) {
            Log::warning('Telegram bot token or chat ID is not set in .env');
            return;
        }

        $time = now()->timezone('Asia/Jakarta')->format('d-m-Y H:i');
        $roleStr = implode(', ', $this->user->roles->pluck('name')->toArray());
        
        $message = "💡 *Info Aktivitas User*\n\n";
        $message .= "👤 *{$this->user->name}* ({$roleStr})\n";
        $message .= "🕒 {$time} WIB\n\n";

        // Cek aktivitas dalam 15 menit terakhir
        $recentTime = now()->subMinutes(15);
        $activities = [];

        // 1. Cek Jurnal Entries (Student / Scholarship)
        $jurnal = \App\Models\JurnalEntry::where('student_id', $this->user->id)
                    ->where('updated_at', '>=', $recentTime)
                    ->latest()->first();
        if ($jurnal) {
            $acts = [];
            if ($jurnal->pl_checked) $acts[] = "PL";
            if ($jurnal->pb_checked) $acts[] = "PB";
            $bacaan = count($acts) > 0 ? implode(" & ", $acts) : "Belum ada bacaan";
            
            $ayat = $jurnal->verse_checked ? "Sudah ($jurnal->verse_ref)" : "Belum";
            $foto = $jurnal->foto_belajar ? "Sudah upload" : "Belum upload";

            $activities[] = "📖 *Jurnal Harian:*\n  - Bacaan: {$bacaan}\n  - Hafalan Ayat: {$ayat}\n  - Foto Belajar: {$foto}";
        }

        // 2. Cek College Logs
        $collegeLogs = \Illuminate\Support\Facades\DB::table('college_study_logs')
            ->join('jurnal_life_items', 'college_study_logs.life_item_id', '=', 'jurnal_life_items.id')
            ->where('college_study_logs.user_id', $this->user->id)
            ->where('college_study_logs.created_at', '>=', $recentTime)
            ->select('jurnal_life_items.label')
            ->get();

        if ($collegeLogs->isNotEmpty()) {
            $items = $collegeLogs->pluck('label')->implode(', ');
            $activities[] = "🎓 *Log Kuliah:*\n  - Mengisi: {$items}";
        }

        if (count($activities) > 0) {
            $message .= "*Aktivitas yang dilakukan:*\n";
            $message .= implode("\n\n", $activities);
        } else {
            $message .= "⚠️ _User ini login, tetapi belum menyimpan pengisian jurnal/log apa pun dalam 10 menit terakhir._";
        }

        foreach ($chatIds as $chatId) {
            $chatId = trim($chatId);
            if (!$chatId) continue;
            
            try {
                $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown'
                ]);
                
                if (!$response->successful()) {
                    Log::error("Failed to send Telegram notification to {$chatId}: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Exception sending Telegram notification to ' . $chatId . ': ' . $e->getMessage());
            }
        }
    }
}
