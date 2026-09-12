<?php
$file = 'resources/views/mentor/presensi/form.blade.php';
$content = file_get_contents($file);

$fetchRegex = '/fetch\(searchUrl \+ \'\?\' \+ params\.toString\(\)\)/';
$fetchReplacement = "fetch(searchUrl + '?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })";

$newContent = preg_replace($fetchRegex, $fetchReplacement, $content);
file_put_contents($file, $newContent);
echo "Patched fetch headers successfully.";
