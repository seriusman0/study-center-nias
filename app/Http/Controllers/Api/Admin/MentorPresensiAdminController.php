<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorPresensi;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorPresensiAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        [$from, $to] = $this->resolvePeriod($request);

        $q = MentorPresensi::with(['mentor:id,name,username', 'cabang:id,nama', 'kelas:id,nama'])
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()]);

        if (!$user->isAdmin()) {
            if ($user->cabang_id) $q->where('cabang_id', $user->cabang_id);
            else $q->whereRaw('1 = 0');
        }
        if ($request->filled('cabang_id')) $q->where('cabang_id', $request->cabang_id);
        if ($request->filled('mentor_id')) $q->where('mentor_id', $request->mentor_id);

        return response()->json([
            'data' => $q->latest('tanggal')->latest('jam_datang')->paginate(20),
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
        ]);
    }

    public function reports(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = $this->buildReportData($request, $from, $to);

        return response()->json(array_merge($data, [
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
        ]));
    }

    public function exportExcel(Request $request)
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = $this->buildReportData($request, $from, $to);

        $filename = sprintf('presensi-mentor-%s-%s.csv', $from->toDateString(), $to->toDateString());

        return response()->streamDownload(function () use ($data, $from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Laporan Presensi Mentor']);
            fputcsv($out, ['Periode', $from->toDateString() . ' s/d ' . $to->toDateString()]);
            fputcsv($out, []);
            fputcsv($out, ['Ringkasan Total']);
            fputcsv($out, ['Total Sesi', $data['totals']['sesi']]);
            fputcsv($out, ['Total Jam', $data['totals']['jam']]);
            fputcsv($out, ['Total Murid', $data['totals']['murid']]);
            fputcsv($out, ['Mentor Aktif', $data['totals']['mentor_aktif']]);
            fputcsv($out, []);
            fputcsv($out, ['Detail']);
            fputcsv($out, ['Tanggal', 'Mentor', 'Cabang', 'Kelas', 'Jam Datang', 'Jam Pulang', 'Durasi (jam)', 'Jumlah Murid']);
            foreach ($data['detail'] as $r) {
                fputcsv($out, [
                    $r->tanggal->toDateString(),
                    $r->mentor?->name ?? '—',
                    $r->cabang?->nama ?? '—',
                    $r->kelas?->nama ?? '—',
                    substr((string) $r->jam_datang, 0, 5),
                    substr((string) $r->jam_pulang, 0, 5),
                    round($r->durasi_menit / 60, 2),
                    $r->jumlah_murid,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = $this->buildReportData($request, $from, $to);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.mentor-presensi.export-pdf', array_merge($data, [
            'from'        => $from,
            'to'          => $to,
            'cabangNama'  => null,
            'generatedAt' => now(),
        ]))->setPaper('a4', 'landscape');

        return $pdf->download(sprintf('presensi-mentor-%s-%s.pdf', $from->toDateString(), $to->toDateString()));
    }

    private function buildReportData(Request $request, Carbon $from, Carbon $to): array
    {
        $user = $request->user();
        $base = MentorPresensi::query()
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()]);

        if (!$user->isAdmin()) {
            if ($user->cabang_id) $base->where('cabang_id', $user->cabang_id);
            else $base->whereRaw('1 = 0');
        } elseif ($request->filled('cabang_id')) {
            $base->where('cabang_id', $request->cabang_id);
        }

        $perMentor = (clone $base)
            ->selectRaw('mentor_id,
                COUNT(*) as sesi,
                SUM(TIMESTAMPDIFF(MINUTE, jam_datang, jam_pulang)) as menit_total,
                SUM(jumlah_murid) as murid_total,
                AVG(jumlah_murid) as murid_avg')
            ->groupBy('mentor_id')
            ->with('mentor:id,name,username')
            ->get();

        $perCabang = (clone $base)
            ->selectRaw('cabang_id, SUM(jumlah_murid) as murid_total, COUNT(*) as sesi')
            ->groupBy('cabang_id')
            ->with('cabang:id,nama')
            ->get();

        $trend = (clone $base)
            ->selectRaw('tanggal, COUNT(*) as sesi, SUM(jumlah_murid) as murid, SUM(TIMESTAMPDIFF(MINUTE, jam_datang, jam_pulang)) as menit')
            ->groupBy('tanggal')->orderBy('tanggal')->get();

        $detail = (clone $base)
            ->with(['mentor:id,name', 'cabang:id,nama', 'kelas:id,nama'])
            ->orderBy('tanggal')->orderBy('jam_datang')->get();

        $totalSesi = $detail->count();
        $totalMenit = $detail->sum(fn($r) => $r->durasi_menit);
        $totalMurid = $detail->sum('jumlah_murid');
        $mentorAktif = $detail->pluck('mentor_id')->unique()->count();

        return [
            'perMentor' => $perMentor,
            'perCabang' => $perCabang,
            'trend'     => $trend,
            'detail'    => $detail,
            'totals'    => [
                'sesi'         => $totalSesi,
                'jam'          => round($totalMenit / 60, 2),
                'murid'        => $totalMurid,
                'mentor_aktif' => $mentorAktif,
            ],
        ];
    }

    private function resolvePeriod(Request $request): array
    {
        $today = JurnalWeek::today();
        $from = $request->filled('from')
            ? Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay()
            : $today->copy()->subDays(29);
        $to = $request->filled('to')
            ? Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay()
            : $today->copy();
        if ($to->gt($today)) $to = $today->copy();
        if ($from->gt($to)) $from = $to->copy();
        return [$from, $to];
    }
}
