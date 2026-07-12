<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu QR – {{ $user->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card {
            border: 2px solid #1e3a5f;
            border-radius: 12px;
            padding: 24px 28px;
            text-align: center;
            width: 260px;
        }
        .card .logo { font-size: 13px; color: #1e3a5f; font-weight: bold; letter-spacing: 1px; margin-bottom: 10px; text-transform: uppercase; }
        .card .name { font-size: 16px; font-weight: bold; color: #111; margin: 10px 0 4px; }
        .card .meta { font-size: 12px; color: #555; margin-bottom: 14px; }
        .card .qr svg { display: block; margin: 0 auto; }
        .card .id { font-size: 10px; color: #999; margin-top: 10px; }
        @media print {
            body { min-height: unset; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div>
        <div class="card">
            <div class="logo">Study Center NIAS</div>
            <div class="qr">
                {!! QrCode::size(180)->generate($user->id) !!}
            </div>
            <div class="name">{{ $user->name }}</div>
            <div class="meta">{{ $user->username }}</div>
            <div class="id">ID: {{ $user->id }}</div>
        </div>
        <div class="no-print" style="text-align:center;margin-top:16px">
            <button onclick="window.print()" style="padding:8px 20px;cursor:pointer">Cetak</button>
        </div>
    </div>
</body>
</html>
