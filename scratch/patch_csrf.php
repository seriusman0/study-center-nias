<?php
$file = 'bootstrap/app.php';
$content = file_get_contents($file);

if (strpos($content, 'validateCsrfTokens') === false) {
    $search = '$middleware->trustProxies(at: [\'*\']);';
    $replace = $search . "\n\n        \$middleware->validateCsrfTokens(except: [\n            'public-jurnal/*',\n        ]);\n";
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "Patched CSRF.\n";
} else {
    echo "Already patched.\n";
}
