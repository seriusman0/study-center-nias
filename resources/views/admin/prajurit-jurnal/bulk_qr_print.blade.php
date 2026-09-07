<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Massal Prajurit</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #fff; padding: 20px; }
        .grid-container {
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
        }
        .card .name { font-size: 16px; font-weight: bold; color: #111; margin: 10px 0 4px; }
        .card .meta { font-size: 12px; color: #555; margin-bottom: 14px; }
        .card .qr svg { display: block; margin: 0 auto; }
        .card .id { font-size: 10px; color: #999; margin-top: 10px; }
        
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { padding: 10px 24px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .grid-container {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }
            .card {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak Semua</button>
    </div>
    
    <div class="grid-container">
        @foreach($users as $user)
        <div class="card">
            <div class="qr">
                {!! QrCode::size(180)->generate($user->id) !!}
            </div>
            <div class="name">{{ $user->name }}</div>
            <div class="meta">{{ $user->username }}</div>
            <div class="id">ID: {{ $user->id }}</div>
        </div>
        @endforeach
    </div>
</body>
</html>
