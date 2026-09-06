<?php
$file = '/var/www/study-center-nias/app/Console/Commands/SendJournalReport.php';
$content = file_get_contents($file);

$content = str_replace(
    "\$cabangName = (\$user->cabang && !empty(\$user->cabang->nama)) ? \$user->cabang->nama : 'Lainnya';",
    "\$cabangName = htmlspecialchars((\$user->cabang && !empty(\$user->cabang->nama)) ? \$user->cabang->nama : 'Lainnya');",
    $content
);

$content = str_replace(
    "\$missingByCabang[\$cabangName][] = \"➖ {\$user->name}\";",
    "\$safeName = htmlspecialchars(\$user->name);\n                    \$missingByCabang[\$cabangName][] = \"➖ {\$safeName}\";",
    $content
);

$content = str_replace(
    "\$cabang = (\$j->student->cabang && !empty(\$j->student->cabang->nama)) ? \$j->student->cabang->nama : 'Lainnya';",
    "\$cabang = htmlspecialchars((\$j->student->cabang && !empty(\$j->student->cabang->nama)) ? \$j->student->cabang->nama : 'Lainnya');",
    $content
);

$content = str_replace(
    "\$doneUsers[\$cabang][\$j->student->name] = true;",
    "\$safeName = htmlspecialchars(\$j->student->name);\n                    \$doneUsers[\$cabang][\$safeName] = true;",
    $content
);

$content = str_replace(
    "\$cabang = (\$user && \$user->cabang && !empty(\$user->cabang->nama)) ? \$user->cabang->nama : 'Lainnya';",
    "\$cabang = htmlspecialchars((\$user && \$user->cabang && !empty(\$user->cabang->nama)) ? \$user->cabang->nama : 'Lainnya');",
    $content
);

$content = str_replace(
    "\$doneUsers[\$cabang][\$log->name] = true;",
    "\$safeName = htmlspecialchars(\$log->name);\n                \$doneUsers[\$cabang][\$safeName] = true;",
    $content
);

file_put_contents($file, $content);
echo "Added htmlspecialchars successfully\n";
