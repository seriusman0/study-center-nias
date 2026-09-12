<?php
$file = 'resources/views/admin/prajurit-jurnal/dashboard.blade.php';
$content = file_get_contents($file);

$insert = <<<HTML
@if(request()->query('scan') == '1')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            var btn = document.getElementById('btnOpenScanner');
            if (btn) btn.click();
        }, 500);
    });
</script>
@endif
HTML;

$pos = strpos($content, '@endsection');
if ($pos !== false) {
    $newContent = substr($content, 0, $pos) . $insert . "\n" . substr($content, $pos);
    file_put_contents($file, $newContent);
    echo "Patched prajurit dashboard.";
} else {
    echo "Could not find insertion point.";
}
