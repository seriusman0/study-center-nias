<?php

function fixFile($file) {
    $content = file_get_contents($file);
    
    // The broken code looks like:
    /*
    });
}).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Gagal memulai kamera: ' + err + '</span>';
    });
}
    */
    
    // or for dashboard.blade.php:
    /*
    });
}).catch(err => {
        document.getElementById('scan-status').innerHTML = '<span class="text-danger">Gagal memulai kamera: ' + err + '</span>';
    });
}
    */

    $pattern = "/\}\);\n\}\)\.catch\(err => \{\n.*?\}\);\n\}/s";
    
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, "});\n}", $content);
        file_put_contents($file, $content);
        echo "Fixed \$file\n";
    } else {
        echo "Pattern not found in \$file\n";
    }
}

fixFile('resources/views/home.blade.php');
fixFile('resources/views/admin/prajurit-jurnal/dashboard.blade.php');

