<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$js = file_get_contents('scratch/final_js_restored.js');

$search = '<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>';

// we find where the script should be.
// the existing JS in home.blade.php is now BROKEN (I already overrode it with sw.js earlier).
// So I will just delete any <script> block between html5-qrcode and <style>
$startStr = '<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>';
$endStr = '<style>';

$startPos = strpos($content, $startStr);
$endPos = strpos($content, $endStr);

if ($startPos !== false && $endPos !== false) {
    $before = substr($content, 0, $startPos + strlen($startStr));
    $after = substr($content, $endPos);
    
    $newContent = $before . "\n<script>\n" . $js . "\n</script>\n" . $after;
    file_put_contents($file, $newContent);
    echo "Applied final JS safely.\n";
} else {
    echo "Could not find bounds.\n";
}
