<?php
$file = '/var/www/study-center-nias/app/Console/Commands/SendJournalReport.php';
$content = file_get_contents($file);

$search = "                Http::post(\"https://api.telegram.org/bot{\$token}/sendMessage\", [
                    'chat_id' => \$chatId,
                    'text' => \$message,
                    'parse_mode' => 'Markdown'
                ]);";

$replace = "                \$response = Http::post(\"https://api.telegram.org/bot{\$token}/sendMessage\", [
                    'chat_id' => \$chatId,
                    'text' => \$message,
                    'parse_mode' => 'Markdown'
                ]);
                if (!\$response->successful()) {
                    \$this->error('Telegram API Error: ' . \$response->body());
                }";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Patched http post successfully\n";
