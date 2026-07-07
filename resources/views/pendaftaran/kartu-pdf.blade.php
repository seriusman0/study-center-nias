<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 0; }
    html, body { margin: 0; padding: 0; }
    * { box-sizing: border-box; }
    body {
        font-family: "DejaVu Sans", Arial, sans-serif;
        font-size: 11pt;
        color: #1a1a1a;
        background: #fff;
        width: 595pt;
        height: 420pt;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }
    .card {
        width: 100%;
        height: 100%;
        border: 2pt solid #0e8e6d;
        display: block;
        overflow: hidden;
    }

    /* ── HEADER ── */
    .header {
        background-color: #0e8e6d;
        color: #ffffff;
        padding: 0 20pt;
        height: 60pt;
        display: table;
        width: 100%;
    }
    .header-left {
        display: table-cell;
        vertical-align: middle;
        width: 68%;
    }
    .header-logo {
        height: 34pt;
        vertical-align: middle;
        margin-right: 10pt;
    }
    .header-title {
        display: inline-block;
        vertical-align: middle;
    }
    .header-title h1 {
        font-size: 13pt;
        font-weight: bold;
        letter-spacing: 0.5pt;
        margin: 0;
        line-height: 1.2;
    }
    .header-title p {
        font-size: 8pt;
        opacity: 0.82;
        margin: 2pt 0 0;
    }
    .header-right {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        width: 32%;
    }
    .header-badge {
        display: inline-block;
        background: rgba(255,255,255,0.18);
        border: 1pt solid rgba(255,255,255,0.5);
        color: #fff;
        font-size: 8pt;
        font-weight: bold;
        padding: 5pt 12pt;
        border-radius: 20pt;
        letter-spacing: 1pt;
        text-transform: uppercase;
    }

    /* ── BODY ── */
    .body {
        padding: 12pt 20pt;
        display: table;
        width: 100%;
        height: 296pt;
    }
    .foto-col {
        display: table-cell;
        width: 116pt;
        vertical-align: top;
        padding-right: 16pt;
    }
    .foto-col img.foto {
        width: 100pt;
        height: 133pt;
        object-fit: cover;
        border: 2pt solid #0e8e6d;
        border-radius: 4pt;
        display: block;
    }
    .foto-placeholder {
        width: 100pt;
        height: 133pt;
        border: 2pt solid #0e8e6d;
        border-radius: 4pt;
        background: #f3f4f6;
        display: table;
    }
    .foto-placeholder-inner {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
        font-size: 8pt;
        color: #aaa;
    }
    .foto-label {
        text-align: center;
        font-size: 7pt;
        color: #888;
        margin-top: 5pt;
    }
    .data-col {
        display: table-cell;
        vertical-align: top;
    }
    .data-col table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-col td {
        padding: 3pt 4pt;
        vertical-align: top;
        font-size: 10pt;
    }
    .data-col td.label {
        color: #555;
        width: 130pt;
        white-space: nowrap;
    }
    .data-col td.colon {
        width: 10pt;
        color: #bbb;
    }
    .data-col td.value {
        font-weight: bold;
        color: #111;
    }

    /* ── DIVIDER ── */
    .divider {
        border: none;
        border-top: 1pt solid #c8e8df;
        margin: 0 20pt;
    }

    /* ── FOOTER ── */
    .footer {
        padding: 8pt 20pt;
        display: table;
        width: 100%;
        height: 59pt;
    }
    .footer-left {
        display: table-cell;
        vertical-align: middle;
    }
    .footer-right {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        width: 90pt;
    }
    .status-badge {
        display: inline-block;
        font-size: 8pt;
        font-weight: bold;
        padding: 3pt 10pt;
        border-radius: 4pt;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
    }
    .status-pending   { background: #fff7e0; border: 1pt solid #f59e0b; color: #b45309; }
    .status-diterima  { background: #ecfdf5; border: 1pt solid #10b981; color: #065f46; }
    .status-ditolak   { background: #fef2f2; border: 1pt solid #ef4444; color: #991b1b; }
    .status-perbaikan { background: #fff7e0; border: 1pt solid #f59e0b; color: #b45309; }
    .footer-text {
        font-size: 8pt;
        color: #444;
        margin-top: 3pt;
    }
    .footer-formal {
        font-size: 7pt;
        color: #777;
        margin-top: 2pt;
        font-style: italic;
    }
    .qr-wrap {
        width: 70pt;
        height: 70pt;
        display: block;
        margin-left: auto;
        overflow: hidden;
    }
    .qr-wrap svg {
        width: 70pt !important;
        height: 70pt !important;
        display: block;
    }
    .qr-label {
        font-size: 7pt;
        color: #888;
        text-align: center;
        margin-top: 2pt;
    }
</style>
</head>
<body>
@php
    $profile = $user->studentProfile;

    // Foto pendaftar → base64
    $fotoBase64 = null;
    if ($profile->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->photo)) {
        $fotoBytes  = \Illuminate\Support\Facades\Storage::disk('public')->get($profile->photo);
        $mime       = mime_content_type(\Illuminate\Support\Facades\Storage::disk('public')->path($profile->photo));
        $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode($fotoBytes);
    }

    // Logo institusi → base64
    $logoPath   = public_path('assets/img/logo.png');
    $logoBase64 = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    // QR Code → inline SVG, strip XML declaration agar dompdf tidak crash
    $cekUrl = url('/cek-pendaftaran/' . $user->username);
    $qrSvg  = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(80)->margin(1)->generate($cekUrl);
    $qrSvg  = preg_replace('/^<\?xml[^>]+\?>\s*/i', '', $qrSvg);
    $qrSvg  = preg_replace('/<!DOCTYPE[^>]+>\s*/i', '', $qrSvg);

    // Status
    $statusLabel = match($profile->status ?? 'pending') {
        'diterima'  => 'DITERIMA',
        'ditolak'   => 'DITOLAK',
        'perbaikan' => 'PERLU PERBAIKAN',
        default     => 'MENUNGGU VALIDASI',
    };
    $statusClass = match($profile->status ?? 'pending') {
        'diterima'  => 'status-diterima',
        'ditolak'   => 'status-ditolak',
        'perbaikan' => 'status-perbaikan',
        default     => 'status-pending',
    };

    $tglDaftar = $user->created_at->isoFormat('D MMMM YYYY');
    $tglCetak  = now()->isoFormat('D MMMM YYYY');
@endphp

<div class="card">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="header-logo" alt="Logo">
            @endif
            <div class="header-title">
                <h1>STUDY CENTER NIAS</h1>
                <p>Sebagai Rumah Kedua Komunitas Remaja Sehat, Positif dan Berprestasi</p>
            </div>
        </div>
        <div class="header-right">
            <span class="header-badge">Kartu Pendaftaran Siswa</span>
        </div>
    </div>

    {{-- Body --}}
    <div class="body">
        <div class="foto-col">
            @if($fotoBase64)
            <img src="{{ $fotoBase64 }}" class="foto" alt="Foto Pendaftar">
            @else
            <div class="foto-placeholder">
                <div class="foto-placeholder-inner">Tidak ada<br>foto</div>
            </div>
            @endif
            <p class="foto-label">Foto Pendaftar</p>
        </div>
        <div class="data-col">
            <table>
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $user->name }}</td>
                </tr>
                <tr>
                    <td class="label">Username</td>
                    <td class="colon">:</td>
                    <td class="value" style="font-family:'Courier New',monospace">{{ '@' . $user->username }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Kelamin</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $profile->gender === 'laki-laki' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Lahir</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $profile->birth_date ? $profile->birth_date->isoFormat('D MMMM YYYY') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Sekolah</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $profile->school_name }}</td>
                </tr>
                <tr>
                    <td class="label">Kelas</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $profile->grade_class }}</td>
                </tr>
                <tr>
                    <td class="label">No HP Orangtua/Wali</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $profile->guardian_phone }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td class="colon">:</td>
                    <td class="value" style="max-width:200px;word-wrap:break-word;">{{ $profile->address }}</td>
                </tr>
            </table>
        </div>
    </div>

    <hr class="divider">

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">
            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            <div class="footer-text">Terdaftar: {{ $tglDaftar }}</div>
            <div class="footer-formal">Dokumen sah hasil sistem pendaftaran &middot; Dicetak: {{ $tglCetak }}</div>
        </div>
        <div class="footer-right">
            <div class="qr-wrap">{!! $qrSvg !!}</div>
            <p class="qr-label">Scan cek status</p>
        </div>
    </div>

</div>
</body>
</html>
