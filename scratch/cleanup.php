<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

// Remove the entire scannerResultContainer
$content = preg_replace('/<div id="scannerResultContainer" class="hidden max-w-2xl mx-auto w-full">.*?<\/div>\s*<\/div>\s*<div class="bg-gray-50/s', "</div>\n            <div class=\"bg-gray-50", $content);

// Remove openPublicJurnalModal and saveJurnalPublic
$content = preg_replace('/function openPublicJurnalModal\(data\) \{.*?\}\n\nfunction saveJurnalPublic\(itemId, actionType, val = null\) \{.*?\n\}/s', '', $content);

// Remove close logic that touched result container
$content = preg_replace("/document\.getElementById\('scannerResultContainer'\)\.classList\.add\('hidden'\);\s*document\.getElementById\('scannerCameraSection'\)\.classList\.remove\('hidden'\);/s", '', $content);

// Remove resetScannerUI
$content = preg_replace('/function resetScannerUI\(\) \{.*?\}\n\nfunction closePublicScanner/s', 'function closePublicScanner', $content);

file_put_contents($file, $content);

$file2 = 'app/Http/Controllers/Web/PublicJurnalController.php';
$content2 = file_get_contents($file2);
// Remove buildResponse, getItemsQuery, and save
$content2 = preg_replace('/private function getItemsQuery.*?private function buildResponse.*?\n\s+public function scan/s', 'public function scan', $content2);
$content2 = preg_replace('/public function save\(Request \$request\).*?\n    \}/s', '', $content2);
file_put_contents($file2, $content2);

echo "Cleaned up obsolete modal and controller code.\n";
