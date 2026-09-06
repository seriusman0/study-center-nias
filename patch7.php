<?php
$file = '/var/www/study-center-nias/app/Console/Commands/SendJournalReport.php';
$content = file_get_contents($file);

$search1 = "                \$totalMissing = 0;
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
                }
                
                \$totalCount = array_reduce(\$missingByCabang, function(\$carry, \$item) {
                    return \$carry + count(\$item);
                }, 0);
                
                if (\$totalCount > 80) {
                    \$message .= \"\\n... (dan \" . (\$totalCount - 80) . \" lainnya)\";
                }";

$replace1 = "                foreach (\$missingByCabang as \$cabang => \$members) {
                    \$message .= \"\\n📍 <b>Cabang {\$cabang}</b>:\\n\";
                    \$branchCount = 0;
                    foreach (\$members as \$member) {
                        \$branchCount++;
                        if (\$branchCount <= 40) {
                            \$message .= \"{\$member}\\n\";
                        }
                    }
                    if (count(\$members) > 40) {
                        \$message .= \"... (dan \" . (count(\$members) - 40) . \" lainnya di cabang ini)\\n\";
                    }
                }";

$search2 = "                \$totalDone = 0;
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
                }
                
                \$totalCount = array_reduce(\$doneUsers, function(\$carry, \$item) {
                    return \$carry + count(\$item);
                }, 0);
                
                if (\$totalCount > 80) {
                    \$message .= \"\\n... (dan \" . (\$totalCount - 80) . \" lainnya)\";
                }";

$replace2 = "                foreach (\$doneUsers as \$cabang => \$members) {
                    \$message .= \"\\n📍 <b>Cabang {\$cabang}</b>:\\n\";
                    \$names = array_keys(\$members);
                    sort(\$names);
                    \$branchCount = 0;
                    foreach (\$names as \$name) {
                        \$branchCount++;
                        if (\$branchCount <= 40) {
                            \$message .= \"✅ {\$name}\\n\";
                        }
                    }
                    if (count(\$names) > 40) {
                        \$message .= \"... (dan \" . (count(\$names) - 40) . \" lainnya di cabang ini)\\n\";
                    }
                }";

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);

file_put_contents($file, $content);
echo "Patched to per-branch limit successfully\n";
