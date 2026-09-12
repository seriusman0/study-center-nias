<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Semua QR Cabang</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; margin: 0; padding: 0; }
        .page-break { page-break-after: always; }
        .cabang-title { text-align: center; margin-top: 20px; margin-bottom: 20px; font-size: 24px; font-weight: bold; color: #1e3a5f; }
        .grid { width: 100%; border-collapse: separate; border-spacing: 15px; }
        .grid td {
            width: 33%;
            border: 2px solid #1e3a5f;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            vertical-align: top;
        }
        .qr-img { margin-bottom: 10px; width: 120px; height: 120px; }
        .name { font-weight: bold; font-size: 14px; margin-bottom: 5px; color: #111; }
        .meta { font-size: 11px; color: #555; }
        .id { font-size: 9px; color: #999; margin-top: 5px; }
    </style>
</head>
<body>
    @foreach($usersByCabang as $cabangName => $users)
        <div class="cabang-title">Cabang: {{ $cabangName }}</div>
        
        <table class="grid">
            <tr>
            @foreach($users as $index => $user)
                @if($index > 0 && $index % 3 == 0)
                    </tr><tr>
                @endif
                <td>
                    <!-- using base64 svg for dompdf compatibility -->
                    <img class="qr-img" src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(120)->generate($user->id)) }}" />
                    <div class="name">{{ $user->name }}</div>
                    <div class="meta">{{ $user->username }}</div>
                    <div class="id">ID: {{ $user->id }}</div>
                </td>
            @endforeach
            <!-- fill empty cells if needed to maintain grid -->
            @php $remainder = count($users) % 3; @endphp
            @if($remainder > 0)
                @for($i = 0; $i < (3 - $remainder); $i++)
                    <td style="border: none;"></td>
                @endfor
            @endif
            </tr>
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
