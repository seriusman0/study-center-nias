<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        background: #fff;
        width: 595px;
        height: 420px;
        overflow: hidden;
    }
    .card {
        width: 595px;
        height: 420px;
        border: 2px solid #0e8e6d;
        display: block;
    }
    .header {
        background-color: #0e8e6d;
        color: #ffffff;
        padding: 10px 20px;
        display: block;
        height: 56px;
    }
    .header-inner {
        display: table;
        width: 100%;
    }
    .header-left {
        display: table-cell;
        vertical-align: middle;
    }
    .header-right {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
    }
    .header h1 {
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin: 0;
    }
    .header p {
        font-size: 9px;
        opacity: 0.85;
        margin: 2px 0 0;
    }
    .badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.5);
        color: #fff;
        font-size: 9px;
        font-weight: bold;
        padding: 3px 10px;
        border-radius: 20px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .body {
        padding: 14px 20px;
        display: table;
        width: 100%;
        height: 300px;
    }
    .foto-col {
        display: table-cell;
        width: 110px;
        vertical-align: top;
        padding-right: 16px;
    }
    .foto-col img {
        width: 100px;
        height: 130px;
        object-fit: cover;
        border: 2px solid #0e8e6d;
        border-radius: 4px;
        display: block;
    }
    .foto-label {
        text-align: center;
        font-size: 8px;
        color: #666;
        margin-top: 4px;
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
        padding: 3px 4px;
        vertical-align: top;
        font-size: 10.5px;
    }
    .data-col td.label {
        color: #555;
        width: 130px;
        white-space: nowrap;
    }
    .data-col td.colon {
        width: 10px;
        color: #555;
    }
    .data-col td.value {
        font-weight: bold;
        color: #1a1a1a;
    }
    .divider {
        border: none;
        border-top: 1px solid #d4ede6;
        margin: 8px 20px;
    }
    .footer {
        padding: 8px 20px 10px;
        display: table;
        width: 100%;
    }
    .footer-left {
        display: table-cell;
        vertical-align: middle;
    }
    .footer-right {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
    }
    .status-badge {
        display: inline-block;
        background: #fff7e0;
        border: 1.5px solid #f59e0b;
        color: #b45309;
        font-size: 9px;
        font-weight: bold;
        padding: 3px 10px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .footer-text {
        font-size: 8.5px;
        color: #555;
    }
    .footer-url {
        font-size: 8px;
        color: #0e8e6d;
        font-family: "Courier New", monospace;
        margin-top: 2px;
    }
    .reg-date {
        font-size: 9px;
        color: #777;
    }
    .watermark {
        font-size: 7.5px;
        color: #aaa;
        margin-top: 2px;
    }
</style>
</head>
<body>
@php
    $profile = $user->studentProfile;
    $fotoBase64 = null;
    if ($profile->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->photo)) {
        $fotoBytes   = \Illuminate\Support\Facades\Storage::disk('public')->get($profile->photo);
        $mime        = mime_content_type(\Illuminate\Support\Facades\Storage::disk('public')->path($profile->photo));
        $fotoBase64  = 'data:' . $mime . ';base64,' . base64_encode($fotoBytes);
    }
    $statusLabel = match($profile->status ?? 'pending') {
        'diterima'  => 'DITERIMA',
        'ditolak'   => 'DITOLAK',
        'perbaikan' => 'PERLU PERBAIKAN',
        default     => 'MENUNGGU VALIDASI',
    };
    $cekUrl = url('/cek-pendaftaran/' . $user->username);
    $tglDaftar = $user->created_at->isoFormat('D MMMM YYYY');
@endphp

<div class="card">

    {{-- Header --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <h1>STUDY CENTER NIAS</h1>
                <p>Sebagai Rumah Kedua Komunitas Remaja Sehat, Positif dan Berprestasi</p>
            </div>
            <div class="header-right">
                <span class="badge">Kartu Pendaftaran Siswa</span>
            </div>
        </div>
    </div>

    {{-- Body --}}
    <div class="body">
        <div class="foto-col">
            @if($fotoBase64)
            <img src="{{ $fotoBase64 }}" alt="Foto Pendaftar">
            @else
            <div style="width:100px;height:130px;border:2px solid #0e8e6d;border-radius:4px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;">
                <span style="font-size:8px;color:#aaa;text-align:center">Tidak ada<br>foto</span>
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
            <span class="status-badge">{{ $statusLabel }}</span>
            <div class="footer-text" style="margin-top:4px">Terdaftar: {{ $tglDaftar }}</div>
            <div class="watermark">Kartu ini adalah bukti pendaftaran, belum merupakan bukti penerimaan.</div>
        </div>
        <div class="footer-right">
            <div class="footer-text">Pantau status pendaftaran:</div>
            <div class="footer-url">{{ $cekUrl }}</div>
        </div>
    </div>

</div>
</body>
</html>
