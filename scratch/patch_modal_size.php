<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

// 1. Change modal max-width from sm:max-w-4xl to sm:max-w-3xl, and padding
$content = str_replace('sm:max-w-4xl w-full', 'sm:max-w-3xl w-full', $content);
$content = str_replace('<div class="p-6">', '<div class="p-3 sm:p-5">', $content);

// 2. Reduce camera size and center it
$content = str_replace(
    'id="qr-reader" class="w-full rounded-xl overflow-hidden shadow-md border-4 border-gray-100 bg-gray-50"',
    'id="qr-reader" class="w-full max-w-[280px] mx-auto sm:max-w-full rounded-xl overflow-hidden shadow-sm border-2 border-gray-100 bg-gray-50"',
    $content
);

// 3. Reduce gap between columns from gap-8 to gap-4
$content = str_replace('grid-cols-1 md:grid-cols-12 gap-8', 'grid-cols-1 md:grid-cols-12 gap-4', $content);

// 4. Reduce placeholder min-height from 300px to 200px
$content = str_replace('min-h-[300px]', 'min-h-[200px]', $content);

// 5. Reduce checklist max-height from 350px to 250px
$content = str_replace('max-h-[350px]', 'max-h-[250px]', $content);

// 6. Reduce margin bottom on some elements to save vertical space
$content = str_replace('mb-4 bg-white p-4', 'mb-3 bg-white p-3', $content);
$content = str_replace('mb-5">', 'mb-3">', $content);

file_put_contents($file, $content);
echo "Patched modal size in home.blade.php\n";
