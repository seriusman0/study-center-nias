<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// 1. Ambil Admin
$admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
if (!$admin) {
    die("Admin not found\n");
}

$cabangId = 2; // Kabupaten Nias
$cabang = Cabang::find($cabangId);
$from = Carbon::now()->startOfMonth()->toDateString();
$to = Carbon::now()->toDateString();

echo "Generating Journal Report for Cabang: " . $cabang->nama . " ($from to $to)\n";

// 2. Ambil list siswa di cabang tsb
$students = User::where('cabang_id', $cabangId)
    ->whereHas('roles', fn($q) => $q->whereIn('name', ['student', 'college', 'scholarship_teenager']))
    ->get();

echo "Found " . $students->count() . " students.\n";

$reportsDir = storage_path('app/reports');
if (!file_exists($reportsDir)) {
    mkdir($reportsDir, 0775, true);
}

$outputFile = $reportsDir . '/jurnal_student_nias_' . date('Ymd_His') . '.pdf';

// Kita pakai JurnalReportController@export yang menghasilkan PDF (biasanya via Snappy atau DomPDF)
$controller = app(\App\Http\Controllers\Web\Admin\JurnalReportController::class);

// Karena export() di controller asli mungkin cuma buat per-siswa, 
// kita butuh ringkasan. Jika tidak ada controller ringkasan PDF, kita buat manual via Tinker/Blade.
// Tapi user minta PDF ringkasan. Mari kita cek method di JurnalReportController.

// Sepertinya kita harus buat script yang menggabungkan ringkasan.
// Untuk saat ini, mari kita kumpulkan data ringkasan dulu ke CSV/PDF sederhana.

$summaryData = [];
foreach ($students as $s) {
    $stats = DB::table('jurnal_entries')
        ->where('student_id', $s->id)
        ->whereBetween('tanggal', [$from, $to])
        ->selectRaw('count(*) as total, sum(pl_checked) as pl, sum(pb_checked) as pb, sum(verse_checked) as verse')
        ->first();
    
    $presence = DB::table('presensi_students')
        ->join('presensi', 'presensi.id', '=', 'presensi_students.presensi_id')
        ->where('presensi_students.user_id', $s->id)
        ->whereBetween('presensi.tanggal', [$from, $to])
        ->selectRaw('count(*) as total_sesi, sum(status="hadir") as hadir')
        ->first();

    $summaryData[] = [
        'Nama' => $s->name,
        'Jurnal (Isi/Total Hari)' => ($stats->total ?? 0) . " hari",
        'PL' => $stats->pl ?? 0,
        'PB' => $stats->pb ?? 0,
        'Ayat' => $stats->verse ?? 0,
        'Presensi' => ($presence->hadir ?? 0) . "/" . ($presence->total_sesi ?? 0)
    ];
}

// Generate HTML sederhana untuk di-convert ke PDF
$html = "<h1>Ringkasan Jurnal & Kehadiran Siswa - Cabang " . $cabang->nama . "</h1>";
$html .= "<p>Periode: $from s/d $to</p>";
$html .= "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse:collapse;'>";
$html .= "<thead><tr><th>Nama</th><th>Jurnal</th><th>PL</th><th>PB</th><th>Ayat</th><th>Presensi</th></tr></thead>";
$html .= "<tbody>";
foreach ($summaryData as $row) {
    $html .= "<tr>";
    foreach ($row as $val) { $html .= "<td>$val</td>"; }
    $html .= "</tr>";
}
$html .= "</tbody></table>";

// Gunakan DomPDF yang sudah ada di vendor
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
file_put_contents($outputFile, $dompdf->output());

echo "PDF Generated: $outputFile\n";
echo "RESULT_PATH:" . $outputFile . "\n";
