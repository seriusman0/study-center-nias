<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Name Tag - {{ $students->count() }} Siswa</title>
    <style>
        @page { size: A4 {{ $orientation === 'landscape' ? 'landscape' : 'portrait' }}; margin: 8mm; }
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
            max-width: {{ $orientation === 'landscape' ? '297mm' : '210mm' }};
            margin: 0 auto;
        }

        /* ── Base nametag ── */
        .nametag {
            position: relative;
            background: #f5efe2;
            overflow: hidden;
            border: 1px dashed #cbd5e1;
            border-radius: 2px;
            padding: 4mm 5mm;
            display: flex; flex-direction: column;
            page-break-inside: avoid;
        }

        /* ── Shared decorative corners ── */
        .corner-tl { position: absolute; top: 0; left: 0; width: 14mm; height: 14mm; overflow: hidden; }
        .corner-tl::before {
            content: ''; position: absolute; top: -2mm; left: 6mm;
            width: 0; height: 0;
            border-left: 3mm solid transparent; border-right: 3mm solid transparent;
            border-top: 8mm solid #1e6b3a;
        }
        .corner-tr { position: absolute; top: 0; right: 0; width: 18mm; height: 14mm; }
        .corner-bl { position: absolute; bottom: 0; left: 0; width: 16mm; height: 14mm; }
        .corner-tr svg, .corner-bl svg { width: 100%; height: 100%; display: block; }

        /* ── Shared header ── */
        .head { display: flex; align-items: center; gap: 2mm; position: relative; z-index: 2; }
        .head img.logo { width: 11mm; height: 11mm; object-fit: contain; }
        .head .titles { line-height: 1.1; }
        .head .title-main { font-size: 11pt; font-weight: 800; color: #5d3a14; letter-spacing: .5px; }
        .head .title-sub  { font-size: 6pt; color: #1e6b3a; font-weight: 700; letter-spacing: .3px; }
        .head .title-tag  { font-size: 5.5pt; color: #1e6b3a; font-style: italic; }
        .divider { height: 1pt; background: #1e6b3a; margin: 2mm 0 3mm; position: relative; z-index: 1; }

        /* ── Standard template body ── */
        .body {
            flex: 1; display: flex; flex-direction: column;
            justify-content: center; gap: 1.5mm;
            padding-left: 2mm; position: relative; z-index: 2;
        }
        .row-line { display: flex; align-items: baseline; gap: 2mm; font-size: 9pt; }
        .row-line .label { font-weight: 700; color: #2d2d2d; min-width: 18mm; }
        .row-line .colon { color: #2d2d2d; }
        .row-line .value { color: #2d2d2d; font-weight: 500; flex: 1; }

        /* ── with_photo template ── */
        .nametag-photo { padding: 3mm 4mm; }
        .photo-body {
            flex: 1; display: flex; gap: 3mm; align-items: flex-start;
            position: relative; z-index: 2;
        }
        .photo-box {
            flex-shrink: 0;
            width: 20mm; height: 26.7mm; /* 3:4 ratio */
            overflow: hidden;
            border: 1px solid #cbd5e1;
            background: #fff;
        }
        .photo-img {
            width: 100%; height: 100%;
            object-fit: cover; object-position: center top;
        }
        .photo-placeholder {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px dashed #94a3b8;
            color: #94a3b8; font-size: 6pt; text-align: center; line-height: 1.3;
        }
        .info-col { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 1.5mm; }
        .info-name { font-size: 9pt; font-weight: 700; color: #1e3a5f; line-height: 1.2; margin-bottom: 1mm; }
        .info-row { display: flex; gap: 1mm; font-size: 7.5pt; color: #2d2d2d; }
        .info-label { font-weight: 600; min-width: 14mm; }
        .info-sep   { margin-right: 1mm; }
        .info-val   { flex: 1; }

        /* ── landscape template ── */
        .nametag-landscape {
            flex-direction: row !important;
            padding: 3mm !important;
            gap: 3mm;
        }
        .ls-photo {
            flex-shrink: 0;
            width: 18mm; height: 24mm; /* 3:4 */
            overflow: hidden;
            border: 1px solid #cbd5e1;
            background: #fff;
            align-self: center;
        }
        .photo-img-ls {
            width: 100%; height: 100%;
            object-fit: cover; object-position: center top;
        }
        .photo-placeholder-ls {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px dashed #94a3b8;
            color: #94a3b8; font-size: 5.5pt; text-align: center; line-height: 1.3;
        }
        .ls-content { flex: 1; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
        .ls-header { display: flex; align-items: center; gap: 2mm; }
        .logo-sm { width: 9mm; height: 9mm; object-fit: contain; }
        .ls-brand { line-height: 1.1; }
        .ls-title { font-size: 9pt; font-weight: 800; color: #5d3a14; }
        .ls-sub   { font-size: 5pt; color: #1e6b3a; font-weight: 700; }
        .ls-divider { height: 1pt; background: #1e6b3a; margin: 1.5mm 0; }
        .ls-name { font-size: 9pt; font-weight: 700; color: #1e3a5f; line-height: 1.2; margin-bottom: 1.5mm; }
        .ls-meta { display: flex; flex-wrap: wrap; gap: 1mm; }
        .ls-badge {
            font-size: 6pt; background: #e8f4f0; color: #1e6b3a;
            padding: 0.5mm 2mm; border-radius: 2px; font-weight: 600;
        }
        .ls-badge-cabang { background: #fef3e2; color: #5d3a14; }

        /* ── portrait_photo_large template ── */
        .nametag-lg { padding: 4mm 5mm; }
        .lg-photo-wrap {
            position: relative; z-index: 2;
            display: flex; justify-content: center; margin-bottom: 3mm;
        }
        .lg-photo-box {
            width: 38mm; height: 50.7mm; /* 3:4 ratio */
            overflow: hidden;
            border: 1.5px solid #cbd5e1;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,.1);
        }
        .lg-photo-box img { width: 100%; height: 100%; object-fit: cover; object-position: center top; }
        .lg-photo-placeholder {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            border: 2px dashed #94a3b8; color: #94a3b8;
            font-size: 7pt; text-align: center; line-height: 1.4;
        }
        .lg-info { position: relative; z-index: 2; }
        .lg-name {
            font-size: 12pt; font-weight: 800; color: #1e3a5f;
            text-align: center; line-height: 1.2; margin-bottom: 2mm;
        }
        .lg-rows { display: flex; flex-direction: column; gap: 1mm; }
        .lg-row { display: flex; align-items: baseline; gap: 1.5mm; font-size: 8pt; }
        .lg-label { font-weight: 700; color: #2d2d2d; min-width: 15mm; }
        .lg-colon { color: #2d2d2d; }
        .lg-val { color: #2d2d2d; font-weight: 500; flex: 1; }
        .lg-footer {
            position: relative; z-index: 2;
            display: flex; justify-content: flex-end; align-items: flex-end;
            margin-top: auto; padding-top: 2mm;
        }
        .lg-qr { width: 18mm; height: 18mm; overflow: hidden; }

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
            {{ $width }} &times; {{ $height }} cm
        </div>
        <div>
            <button type="button" class="btn" onclick="window.print()">
                Cetak / Simpan PDF
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.close()">Tutup</button>
        </div>
    </div>

    <div class="sheet">
        @foreach($cards as $cardHtml)
        {!! $cardHtml !!}
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
