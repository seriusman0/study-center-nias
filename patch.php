<?php

$file = '/var/www/study-center-nias/app/Console/Commands/SendJournalReport.php';
$content = file_get_contents($file);

$search = "            \$users = User::whereHas('roles', function(\$q) {
                \$q->whereIn('name', ['student', 'college', 'scholarship_teenager']);
            })->where('is_active', true)->orderBy('name')->get();

            \$missingUsers = [];
            foreach (\$users as \$user) {
                \$hasEntry = \App\Models\JurnalEntry::where('student_id', \$user->id)->whereDate('created_at', now())->exists();
                \$hasCollegeLog = \Illuminate\Support\Facades\DB::table('college_study_logs')->where('user_id', \$user->id)->whereDate('created_at', now())->exists();
                
                if (!\$hasEntry && !\$hasCollegeLog) {
                    \$missingUsers[] = \"➖ {\$user->name}\";
                }
            }

            if (empty(\$missingUsers)) {
                \$message .= \"🎉 *Luar biasa! Semua orang sudah mengisi jurnalnya hari ini.*\";
            } else {
                \$message .= implode(\"\\n\", array_slice(\$missingUsers, 0, 80)); 
                if (count(\$missingUsers) > 80) \$message .= \"\\n... (dan \" . (count(\$missingUsers) - 80) . \" lainnya)\";
            }";

$replace = "            \$users = User::with('cabang')->whereHas('roles', function(\$q) {
                \$q->whereIn('name', ['student', 'college', 'scholarship_teenager']);
            })->where('is_active', true)->orderBy('name')->get();

            \$missingByCabang = [];
            foreach (\$users as \$user) {
                \$hasEntry = \App\Models\JurnalEntry::where('student_id', \$user->id)->whereDate('created_at', now())->exists();
                \$hasCollegeLog = \Illuminate\Support\Facades\DB::table('college_study_logs')->where('user_id', \$user->id)->whereDate('created_at', now())->exists();
                
                if (!\$hasEntry && !\$hasCollegeLog) {
                    \$cabangName = \$user->cabang ? \$user->cabang->name : 'Lainnya';
                    \$missingByCabang[\$cabangName][] = \"➖ {\$user->name}\";
                }
            }

            if (empty(\$missingByCabang)) {
                \$message .= \"🎉 *Luar biasa! Semua orang sudah mengisi jurnalnya hari ini.*\";
            } else {
                ksort(\$missingByCabang);
                \$totalMissing = 0;
                foreach (\$missingByCabang as \$cabang => \$members) {
                    \$message .= \"\\n📍 *Cabang {\$cabang}*:\\n\";
                    foreach (\$members as \$member) {
                        \$totalMissing++;
                        if (\$totalMissing <= 80) {
                            \$message .= \"{\$member}\\n\";
                        }
                    }
                }
                
                \$totalCount = array_reduce(\$missingByCabang, function(\$carry, \$item) {
                    return \$carry + count(\$item);
                }, 0);
                
                if (\$totalCount > 80) {
                    \$message .= \"\\n... (dan \" . (\$totalCount - 80) . \" lainnya)\";
                }
            }";

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    
    // Also do for the 'done' users (morning/afternoon/night)
    
    $search2 = "            \$doneUsers = [];
            foreach (\$journals as \$j) {
                if (\$j->student) \$doneUsers[\$j->student->name] = true;
            }
            foreach (\$collegeLogs as \$log) {
                \$doneUsers[\$log->name] = true;
            }";
            
    $replace2 = "            \$doneUsers = [];
            foreach (\$journals as \$j) {
                if (\$j->student) {
                    \$cabang = \$j->student->cabang ? \$j->student->cabang->name : 'Lainnya';
                    \$doneUsers[\$cabang][\$j->student->name] = true;
                }
            }
            foreach (\$collegeLogs as \$log) {
                \$user = User::with('cabang')->where('name', \$log->name)->first();
                \$cabang = (\$user && \$user->cabang) ? \$user->cabang->name : 'Lainnya';
                \$doneUsers[\$cabang][\$log->name] = true;
            }";
            
    $search3 = "            if (empty(\$doneUsers)) {
                \$message .= \"💤 _Belum ada yang mengisi jurnal sejauh ini._\";
            } else {
                \$names = array_keys(\$doneUsers);
                sort(\$names);
                foreach (array_slice(\$names, 0, 80) as \$name) {
                    \$message .= \"✅ \$name\\n\";
                }
                if (count(\$names) > 80) \$message .= \"... (dan \" . (count(\$names) - 80) . \" lainnya)\";
            }";
            
    $replace3 = "            if (empty(\$doneUsers)) {
                \$message .= \"💤 _Belum ada yang mengisi jurnal sejauh ini._\";
            } else {
                ksort(\$doneUsers);
                \$totalDone = 0;
                foreach (\$doneUsers as \$cabang => \$members) {
                    \$message .= \"\\n📍 *Cabang {\$cabang}*:\\n\";
                    \$names = array_keys(\$members);
                    sort(\$names);
                    foreach (\$names as \$name) {
                        \$totalDone++;
                        if (\$totalDone <= 80) {
                            \$message .= \"✅ {\$name}\\n\";
                        }
                    }
                }
                
                \$totalCount = array_reduce(\$doneUsers, function(\$carry, \$item) {
                    return \$carry + count(\$item);
                }, 0);
                
                if (\$totalCount > 80) {
                    \$message .= \"\\n... (dan \" . (\$totalCount - 80) . \" lainnya)\";
                }
            }";
            
    $content = str_replace($search2, $replace2, $content);
    $content = str_replace($search3, $replace3, $content);
    
    file_put_contents($file, $content);
    echo "Patched successfully\n";
} else {
    echo "Could not find the target code to patch\n";
}
