<?php
$file = '/var/www/study-center-nias/app/Console/Commands/SendJournalReport.php';
$content = file_get_contents($file);

$content = str_replace(
    "\$cabangName = \$user->cabang ? \$user->cabang->name : 'Lainnya';",
    "\$cabangName = (\$user->cabang && !empty(\$user->cabang->nama)) ? \$user->cabang->nama : 'Lainnya';",
    $content
);

$content = str_replace(
    "\$cabang = \$j->student->cabang ? \$j->student->cabang->name : 'Lainnya';",
    "\$cabang = (\$j->student->cabang && !empty(\$j->student->cabang->nama)) ? \$j->student->cabang->nama : 'Lainnya';",
    $content
);

$content = str_replace(
    "\$cabang = (\$user && \$user->cabang) ? \$user->cabang->name : 'Lainnya';",
    "\$cabang = (\$user && \$user->cabang && !empty(\$user->cabang->nama)) ? \$user->cabang->nama : 'Lainnya';",
    $content
);

file_put_contents($file, $content);
echo "Patched name to nama successfully\n";
