<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu QR Cabang – {{ $cabang->nama }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #fff; padding: 20px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            justify-content: center;
        }
        .card {
            border: 2px solid #1e3a5f;
            border-radius: 12px;
            padding: 24px 28px;
            text-align: center;
            width: 260px;
            margin: 0 auto;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .card .name { font-size: 16px; font-weight: bold; color: #111; margin: 10px 0 4px; }
        .card .meta { font-size: 12px; color: #555; margin-bottom: 14px; }
        .card .qr svg { display: block; margin: 0 auto; }
        .card .id { font-size: 10px; color: #999; margin-top: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; color: #1e3a5f; margin-bottom: 8px; }
        .header p { font-size: 14px; color: #555; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .header { margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>QR Code - Cabang {{ $cabang->nama }}</h1>
        <p>Total Pengguna: {{ $users->count() }}</p>
        <div class="no-print" style="margin-top:16px">
            <button onclick="window.print()" style="padding:8px 20px;cursor:pointer;background:#1e3a5f;color:white;border:none;border-radius:4px;font-weight:bold;">Cetak Semua</button>
            <button onclick="window.close()" style="padding:8px 20px;cursor:pointer;background:#ccc;color:black;border:none;border-radius:4px;margin-left:8px;">Tutup</button>
        </div>
    </div>
    
    <div class="grid">
        @forelse($users as $user)
        <div class="card">
            <div class="qr">
                {!! QrCode::size(180)->generate($user->id) !!}
            </div>
            <div class="name">{{ $user->name }}</div>
            <div class="meta">{{ $user->username }}</div>
            <div class="id">ID: {{ $user->id }}</div>
        </div>
        @empty
        <div style="text-align: center; grid-column: 1 / -1; padding: 50px; color: #777;">
            Tidak ada pengguna aktif di cabang ini.
        </div>
        @endforelse
    </div>
</body>
</html>
