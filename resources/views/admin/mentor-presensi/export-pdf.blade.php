<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Presensi Mentor</title>
<style>
    @page { margin: 18mm 12mm 18mm 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
    .header { border-bottom: 3px solid #1e3a5f; padding-bottom: 8px; margin-bottom: 14px; }
    .header .title { color: #1e3a5f; font-size: 18px; font-weight: bold; margin: 0; }
    .header .sub { color: #6b7280; font-size: 11px; margin-top: 2px; }
    .meta { display: table; width: 100%; margin-bottom: 10px; }
    .meta .cell { display: table-cell; font-size: 10px; }
    h2 { color: #1e3a5f; font-size: 12px; margin: 16px 0 6px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    th { background: #1e3a5f; color: #fff; padding: 6px 8px; font-size: 10px; text-align: left; }
    td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
    tr:nth-child(even) td { background: #f9fafb; }
    .totals { display: table; width: 100%; margin-bottom: 12px; }
    .totals .box { display: table-cell; border: 1px solid #1e3a5f; padding: 8px; text-align: center; width: 25%; }
    .totals .box .v { font-size: 18px; font-weight: bold; color: #1e3a5f; }
    .totals .box .l { font-size: 9px; color: #6b7280; text-transform: uppercase; }
    .footer { position: fixed; bottom: -8mm; left: 0; right: 0; text-align: center; color: #9ca3af; font-size: 9px; }
    .footer .pn:after { content: counter(page) " / " counter(pages); }
    .right { text-align: right; }
    .center { text-align: center; }
</style>
</head>
<body>

<div class="header">
    <p class="title">Laporan Presensi Mentor</p>
    <p class="sub">Study Center Nias</p>
</div>

<div class="meta">
    <div class="cell"><strong>Periode:</strong> {{ $from->locale('id')->isoFormat('D MMMM Y') }} &mdash; {{ $to->locale('id')->isoFormat('D MMMM Y') }}</div>
    <div class="cell right">
        <strong>Cabang:</strong> {{ $cabangNama ?: 'Semua' }}<br>
        <strong>Dibuat:</strong> {{ $generatedAt->locale('id')->isoFormat('D MMM Y HH:mm') }}
    </div>
</div>

<div class="totals">
    <div class="box"><div class="v">{{ $totals['sesi'] }}</div><div class="l">Total Sesi</div></div>
    <div class="box"><div class="v">{{ $totals['jam'] }}</div><div class="l">Total Jam</div></div>
    <div class="box"><div class="v">{{ $totals['murid'] }}</div><div class="l">Total Murid</div></div>
    <div class="box"><div class="v">{{ $totals['mentor_aktif'] }}</div><div class="l">Mentor Aktif</div></div>
</div>

<h2>Per Mentor</h2>
<table>
    <thead>
        <tr>
            <th>Mentor</th>
            <th class="center">Sesi</th>
            <th class="center">Jam</th>
            <th class="center">Total Murid</th>
            <th class="center">Rata-rata</th>
        </tr>
    </thead>
    <tbody>
        @forelse($perMentor as $r)
        <tr>
            <td>{{ $r->mentor?->name ?? '—' }}</td>
            <td class="center">{{ $r->sesi }}</td>
            <td class="center">{{ number_format($r->menit_total / 60, 2) }}</td>
            <td class="center">{{ $r->murid_total }}</td>
            <td class="center">{{ number_format((float) $r->murid_avg, 1) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="center">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>

<h2>Per Cabang</h2>
<table>
    <thead>
        <tr>
            <th>Cabang</th>
            <th class="center">Sesi</th>
            <th class="center">Total Murid</th>
        </tr>
    </thead>
    <tbody>
        @forelse($perCabang as $r)
        <tr>
            <td>{{ $r->cabang?->nama ?? '—' }}</td>
            <td class="center">{{ $r->sesi }}</td>
            <td class="center">{{ $r->murid_total }}</td>
        </tr>
        @empty
        <tr><td colspan="3" class="center">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>

<h2>Detail</h2>
<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Mentor</th>
            <th>Cabang</th>
            <th>Kelas</th>
            <th class="center">Datang</th>
            <th class="center">Pulang</th>
            <th class="center">Jam</th>
            <th class="center">Murid</th>
        </tr>
    </thead>
    <tbody>
        @forelse($detail as $r)
        <tr>
            <td>{{ $r->tanggal->toDateString() }}</td>
            <td>{{ $r->mentor?->name ?? '—' }}</td>
            <td>{{ $r->cabang?->nama ?? '—' }}</td>
            <td>{{ $r->kelas?->nama ?? '—' }}</td>
            <td class="center">{{ substr($r->jam_datang, 0, 5) }}</td>
            <td class="center">{{ substr($r->jam_pulang, 0, 5) }}</td>
            <td class="center">{{ number_format($r->durasi_menit / 60, 2) }}</td>
            <td class="center">{{ $r->jumlah_murid }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="center">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Study Center Nias · Laporan Presensi Mentor · Halaman <span class="pn"></span>
</div>

</body>
</html>
