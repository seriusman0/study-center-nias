<?php
$file = '/var/www/study-center-nias/app/Console/Commands/SendJournalReport.php';
$content = file_get_contents($file);

$search1 = "                \$totalMissing = 0;
                foreach (\$missingByCabang as \$cabang => \$members) {
                    \$message .= \"\\n📍 <b>Cabang {\$cabang}</b>:\\n\";
                    foreach (\$members as \$member) {
                        \$totalMissing++;
                        if (\$totalMissing <= 80) {
                            \$message .= \"{\$member}\\n\";
                        }
                    }
                }";

$replace1 = "                \$totalMissing = 0;
                \$limitReached = false;
                foreach (\$missingByCabang as \$cabang => \$members) {
                    if (\$limitReached) break;
                    \$message .= \"\\n📍 <b>Cabang {\$cabang}</b>:\\n\";
                    foreach (\$members as \$member) {
                        \$totalMissing++;
                        if (\$totalMissing <= 80) {
                            \$message .= \"{\$member}\\n\";
                        } else {
                            \$limitReached = true;
                            break;
                        }
                    }
                }";

$search2 = "                \$totalDone = 0;
                foreach (\$doneUsers as \$cabang => \$members) {
                    \$message .= \"\\n📍 <b>Cabang {\$cabang}</b>:\\n\";
                    \$names = array_keys(\$members);
                    sort(\$names);
                    foreach (\$names as \$name) {
                        \$totalDone++;
                        if (\$totalDone <= 80) {
                            \$message .= \"✅ {\$name}\\n\";
                        }
                    }
                }";

$replace2 = "                \$totalDone = 0;
                \$limitReached = false;
                foreach (\$doneUsers as \$cabang => \$members) {
                    if (\$limitReached) break;
                    \$message .= \"\\n📍 <b>Cabang {\$cabang}</b>:\\n\";
                    \$names = array_keys(\$members);
                    sort(\$names);
                    foreach (\$names as \$name) {
                        \$totalDone++;
                        if (\$totalDone <= 80) {
                            \$message .= \"✅ {\$name}\\n\";
                        } else {
                            \$limitReached = true;
                            break;
                        }
                    }
                }";

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);

file_put_contents($file, $content);
echo "Patched empty headers successfully\n";
