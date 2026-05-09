<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Name Tag - {{ $students->count() }} Siswa</title>
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            margin: 0;
            background: #e5e7eb;
            padding: 16px;
        }
        .toolbar {
            position: sticky; top: 0;
            background: #1e3a5f; color: #fff;
            padding: 10px 16px;
            margin: -16px -16px 16px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
        }
        .toolbar .btn {
            background: #c9a84c; color: #1e3a5f;
            border: none; padding: 6px 14px; border-radius: 6px;
            font-weight: 600; cursor: pointer; font-size: 13px;
        }
        .toolbar .btn-secondary {
            background: rgba(255,255,255,.15); color: #fff; margin-left: 8px;
        }
        .sheet {
            display: flex; flex-wrap: wrap; gap: 4mm;
            justify-content: flex-start;
            max-width: 210mm; margin: 0 auto;
        }
        .nametag {
            width: {{ $width }}cm;
            height: {{ $height }}cm;
            position: relative;
            background: #f5efe2;
            overflow: hidden;
            border: 1px dashed #cbd5e1;
            border-radius: 2px;
            padding: 4mm 5mm;
            display: flex; flex-direction: column;
            page-break-inside: avoid;
        }
        .corner-tl {
            position: absolute; top: 0; left: 0;
            width: 14mm; height: 14mm;
            overflow: hidden;
        }
        .corner-tl::before {
            content: ''; position: absolute;
            top: -2mm; left: 6mm;
            width: 0; height: 0;
            border-left: 3mm solid transparent;
            border-right: 3mm solid transparent;
            border-top: 8mm solid #1e6b3a;
        }
        .corner-tr {
            position: absolute; top: 0; right: 0;
            width: 18mm; height: 14mm;
        }
        .corner-tr svg, .corner-bl svg { width: 100%; height: 100%; display: block; }
        .corner-bl {
            position: absolute; bottom: 0; left: 0;
            width: 16mm; height: 14mm;
        }
        .head {
            display: flex; align-items: center; gap: 2mm;
            position: relative; z-index: 2;
        }
        .head img.logo {
            width: 11mm; height: 11mm; object-fit: contain;
        }
        .head .titles { line-height: 1.1; }
        .head .title-main {
            font-size: 11pt; font-weight: 800;
            color: #5d3a14; letter-spacing: .5px;
        }
        .head .title-sub {
            font-size: 6pt; color: #1e6b3a; font-weight: 700;
            letter-spacing: .3px;
        }
        .head .title-tag {
            font-size: 5.5pt; color: #1e6b3a; font-style: italic;
        }
        .divider {
            height: 1pt; background: #1e6b3a; margin: 2mm 0 3mm;
            position: relative; z-index: 1;
        }
        .body {
            flex: 1; display: flex; flex-direction: column;
            justify-content: center; gap: 1.5mm;
            padding-left: 2mm;
            position: relative; z-index: 2;
        }
        .row-line {
            display: flex; align-items: baseline; gap: 2mm;
            font-size: 9pt;
        }
        .row-line .label {
            font-weight: 700; color: #2d2d2d; min-width: 18mm;
        }
        .row-line .colon { color: #2d2d2d; }
        .row-line .value {
            color: #2d2d2d; font-weight: 500;
            border-bottom: .4pt solid transparent;
            flex: 1;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .nametag { border: none; }
            .sheet { gap: 4mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <strong>Name Tag</strong> &middot; {{ $students->count() }} kartu &middot;
            ukuran {{ $width }} &times; {{ $height }} cm
        </div>
        <div>
            <button type="button" class="btn" onclick="window.print()">
                Cetak / Simpan PDF
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.close()">Tutup</button>
        </div>
    </div>

    <div class="sheet">
        @foreach($students as $s)
        @php $sp = $s->studentProfile; @endphp
        <div class="nametag">
            <div class="corner-tl"></div>
            <div class="corner-tr">
                <svg viewBox="0 0 60 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="60,0 30,0 60,30" fill="#e07a25"/>
                    <polygon points="60,30 45,15 60,15" fill="#1e6b3a"/>
                    <polygon points="30,0 45,0 35,12" fill="#c9a84c"/>
                </svg>
            </div>
            <div class="corner-bl">
                <svg viewBox="0 0 60 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="0,50 0,15 35,50" fill="#e07a25"/>
                    <polygon points="0,50 25,50 0,30" fill="#1e6b3a"/>
                    <polygon points="0,15 0,5 18,50 12,50" fill="#c9a84c"/>
                </svg>
            </div>

            <div class="head">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="logo"
                     onerror="this.style.display='none'">
                <div class="titles">
                    <div class="title-main">STUDY CENTER</div>
                    <div class="title-sub">STUDY CENTER KABUPATEN NIAS</div>
                    <div class="title-tag">SECOND HOME FOR THE BETTER FUTURE</div>
                </div>
            </div>
            <div class="divider"></div>

            <div class="body">
                <div class="row-line">
                    <span class="label">Nama</span><span class="colon">:</span>
                    <span class="value">{{ $s->name }}</span>
                </div>
                <div class="row-line">
                    <span class="label">Kelas</span><span class="colon">:</span>
                    <span class="value">{{ $sp?->grade_class ?? '' }}</span>
                </div>
                <div class="row-line">
                    <span class="label">Sekolah</span><span class="colon">:</span>
                    <span class="value">{{ $sp?->school_name ?? '' }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($autoPrint)
    <script>
        window.addEventListener('load', function() {
            setTimeout(function(){ window.print(); }, 400);
        });
    </script>
    @endif
</body>
</html>
