<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jurnal – {{ $student->name }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1a1a1a; }

.page-header { text-align: center; border-bottom: 2px solid #0d7377; padding-bottom: 6px; margin-bottom: 8px; }
.page-header h1 { font-size: 13px; color: #0d7377; font-weight: bold; }
.page-header .meta { font-size: 8px; color: #555; margin-top: 2px; }

.summary-bar { background: #f0fafa; border: 1px solid #b2d8d8; border-radius: 4px; padding: 5px 10px;
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.summary-bar .pct { font-size: 16px; font-weight: bold; color: #0d7377; }
.summary-bar .detail { font-size: 8px; color: #555; }

table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
thead tr th { background: #0d7377; color: #fff; padding: 3px 4px; text-align: center; font-size: 7.5px; white-space: nowrap; }
thead tr th:first-child { text-align: left; min-width: 60px; }
tbody tr:nth-child(even) { background: #f7fdfd; }
tbody tr td { padding: 2.5px 4px; border-bottom: 1px solid #e0eeee; text-align: center; font-size: 8px; }
tbody tr td:first-child { text-align: left; }
tbody tr.week-header td { background: #ddf0f0; font-weight: bold; font-size: 7.5px; text-align: left; padding: 3px 4px; color: #065a5c; }

.check { color: #1a7a1a; font-weight: bold; }
.cross { color: #b0b0b0; }

.footer { margin-top: 8px; font-size: 7px; color: #888; text-align: right; border-top: 1px solid #ccc; padding-top: 4px; }
</style>
</head>
<body>

<div class="page-header">
    <h1>Jurnal Harian – {{ $student->name }}</h1>
    <div class="meta">
        Cabang: {{ $student->cabang?->nama ?? '—' }} &nbsp;|&nbsp;
        Periode: {{ $from->locale('id')->isoFormat('D MMMM Y') }} s/d {{ $to->locale('id')->isoFormat('D MMMM Y') }}
    </div>
</div>

<div class="summary-bar">
    <div>
        <div class="pct">{{ $matrix['pct'] }}%</div>
        <div class="detail">kepatuhan keseluruhan</div>
    </div>
    <div class="detail" style="text-align:right">
        {{ $matrix['checked'] }} dari {{ $matrix['total'] }} item terisi<br>
        Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}
    </div>
</div>

@php
    $headers = $matrix['headers'];
    $rows    = $matrix['rows'];
    // Group rows by ISO week
    $grouped = [];
    foreach ($rows as $row) {
        $date    = \Carbon\Carbon::parse($row[0]);
        $weekKey = $date->year . '-W' . str_pad($date->isoWeek(), 2, '0', STR_PAD_LEFT);
        $grouped[$weekKey][] = $row;
    }
@endphp

<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            @foreach(array_slice($headers, 1) as $h)
                <th>{{ $h }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($grouped as $weekKey => $weekRows)
            @php $firstDate = \Carbon\Carbon::parse($weekRows[0][0]); @endphp
            <tr class="week-header">
                <td colspan="{{ count($headers) }}">
                    Minggu {{ $firstDate->isoWeek() }} ({{ $firstDate->locale('id')->isoFormat('D MMM') }} – {{ \Carbon\Carbon::parse(end($weekRows)[0])->locale('id')->isoFormat('D MMM Y') }})
                </td>
            </tr>
            @foreach($weekRows as $row)
                @php $d = \Carbon\Carbon::parse($row[0]); @endphp
                <tr>
                    <td>{{ $d->locale('id')->isoFormat('ddd, D MMM') }}</td>
                    @foreach(array_slice($row, 1) as $cell)
                        <td>
                            @if($cell === 'Y')
                                <span class="check">✓</span>
                            @else
                                <span class="cross">–</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<div class="footer">
    Study Center NIAS &bull; Digenerate otomatis dari sistem &bull; {{ now()->toDateTimeString() }}
</div>

</body>
</html>
