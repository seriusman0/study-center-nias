<?php
$file = 'resources/views/admin/dashboard_mentor.blade.php';
$content = file_get_contents($file);

$insert = <<<HTML
    <a href="{{ route('admin.jurnal-prajurit.index', ['scan' => 1]) }}" class="sc-card" style="border: 2px solid #f59f00;">
        <i class="fas fa-qrcode" style="color:#f59f00"></i>
        <span>Scan Prajurit</span>
        <small>Buka Scanner QR</small>
    </a>
HTML;

$pos = strpos($content, '<a href="{{ route(\'admin.jurnal-college.index\') }}" class="sc-card">');
if ($pos !== false) {
    $newContent = substr($content, 0, $pos) . $insert . "\n    " . substr($content, $pos);
    file_put_contents($file, $newContent);
    echo "Patched mentor dashboard.";
} else {
    echo "Could not find insertion point in mentor dashboard.";
}
