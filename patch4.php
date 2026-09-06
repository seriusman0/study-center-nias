<?php
$file = '/var/www/study-center-nias/app/Console/Commands/SendJournalReport.php';
$content = file_get_contents($file);

// Replace parse_mode
$content = str_replace("'parse_mode' => 'Markdown'", "'parse_mode' => 'HTML'", $content);

// Replace bold syntax
$content = str_replace("⚠️ *Laporan Jurnal Kosong*", "⚠️ <b>Laporan Jurnal Kosong</b>", $content);
$content = str_replace("*belum* mengisi jurnal", "<b>belum</b> mengisi jurnal", $content);
$content = str_replace("🎉 *Luar biasa! Semua orang sudah mengisi jurnalnya hari ini.*", "🎉 <b>Luar biasa! Semua orang sudah mengisi jurnalnya hari ini.</b>", $content);
$content = str_replace("📍 *Cabang", "📍 <b>Cabang", $content);
$content = str_replace("}*:\\n", "}</b>:\\n", $content);
$content = str_replace("📊 *Rekap Jurnal", "📊 <b>Rekap Jurnal", $content);
$content = str_replace("*sudah aktif* mengisi jurnal", "<b>sudah aktif</b> mengisi jurnal", $content);
$content = str_replace("💤 _Belum ada", "💤 <i>Belum ada", $content);
$content = str_replace("sejauh ini._", "sejauh ini.</i>", $content);

file_put_contents($file, $content);
echo "Switched to HTML parse_mode successfully\n";
