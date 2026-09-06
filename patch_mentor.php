<?php
$content = file_get_contents('/var/www/study-center-nias/app/Http/Controllers/Web/Admin/MentorPresensiAdminController.php');

// We need to inject the unique students query.
$search = <<<'EOD'
        $totalSesi  = $detail->count();
        $totalMenit = $detail->sum(fn($r) => $r->durasi_menit);
        $totalMurid = $detail->sum('jumlah_murid');
        $mentorAktif = $detail->pluck('mentor_id')->unique()->count();
EOD;

$replace = <<<'EOD'
        $totalSesi  = $detail->count();
        $totalMenit = $detail->sum(fn($r) => $r->durasi_menit);
        
        // Get unique students from presensi table
        $presensiQuery = \Illuminate\Support\Facades\DB::table('presensi')
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()]);
            
        if (! $user->isAdmin()) {
            if ($user->cabang_id) {
                $presensiQuery->where('cabang_id', $user->cabang_id);
            } else {
                $presensiQuery->whereRaw('1 = 0');
            }
        } elseif ($request->filled('cabang_id')) {
            $presensiQuery->where('cabang_id', $request->cabang_id);
        }
        
        if ($request->filled('mentor_id')) {
            $presensiQuery->where('mentor_id', $request->mentor_id);
        }
        
        $presensiIds = $presensiQuery->pluck('id');
        
        $totalMurid = \Illuminate\Support\Facades\DB::table('presensi_students')
            ->whereIn('presensi_id', $presensiIds)
            ->where('status', 'hadir')
            ->distinct()
            ->count('user_id');
            
        // Overwrite perMentor murid_total
        $mentorUniqueStudents = \Illuminate\Support\Facades\DB::table('presensi_students as ps')
            ->join('presensi as p', 'p.id', '=', 'ps.presensi_id')
            ->whereIn('p.id', $presensiIds)
            ->where('ps.status', 'hadir')
            ->select('p.mentor_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT ps.user_id) as unique_students'))
            ->groupBy('p.mentor_id')
            ->pluck('unique_students', 'p.mentor_id');
            
        foreach ($perMentor as $r) {
            $r->murid_total = $mentorUniqueStudents->get($r->mentor_id, 0);
        }

        // Overwrite perCabang murid_total
        $cabangUniqueStudents = \Illuminate\Support\Facades\DB::table('presensi_students as ps')
            ->join('presensi as p', 'p.id', '=', 'ps.presensi_id')
            ->whereIn('p.id', $presensiIds)
            ->where('ps.status', 'hadir')
            ->select('p.cabang_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT ps.user_id) as unique_students'))
            ->groupBy('p.cabang_id')
            ->pluck('unique_students', 'p.cabang_id');
            
        foreach ($perCabang as $r) {
            $r->murid_total = $cabangUniqueStudents->get($r->cabang_id, 0);
        }
        
        $mentorAktif = $detail->pluck('mentor_id')->unique()->count();
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents('/var/www/study-center-nias/app/Http/Controllers/Web/Admin/MentorPresensiAdminController.php', $content);
echo "Replaced in Web Controller\n";
