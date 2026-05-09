@extends('layouts.app')

@section('title', 'Name Tag - ' . $user->name)

@push('head')
<style>
    .nt-shell { max-width: 720px; margin: 0 auto; }
    .nt-controls {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 16px 20px; margin-bottom: 20px;
        display: flex; flex-wrap: wrap; gap: 12px; align-items: end;
    }
    .nt-controls label { font-size: 12px; color: #4b5563; font-weight: 600; display:block; margin-bottom: 4px; }
    .nt-controls input, .nt-controls select {
        border: 1px solid #d1d5db; border-radius: 8px; padding: 6px 10px; font-size: 13px;
    }
    .nt-controls .grow { flex: 1; text-align: right; }
    .nt-controls button {
        background: #1e3a5f; color: #fff; border: none; border-radius: 8px;
        padding: 8px 18px; font-weight: 600; cursor: pointer; font-size: 13px;
    }
    .nt-controls button:hover { background: #2d5282; }

    .nt-stage {
        background: #e5e7eb; padding: 32px 16px; border-radius: 16px;
        display: flex; justify-content: center;
    }

    .nametag {
        width: var(--nt-w, 8.5cm);
        height: var(--nt-h, 5.5cm);
        position: relative;
        background: #f5efe2;
        overflow: hidden;
        padding: 4mm 5mm;
        display: flex; flex-direction: column;
        box-shadow: 0 8px 24px rgba(0,0,0,.15);
        border-radius: 2px;
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
    }
    .nametag .corner-tl { position: absolute; top: 0; left: 0; width: 14mm; height: 14mm; }
    .nametag .corner-tl::before {
        content: ''; position: absolute; top: -2mm; left: 6mm;
        width: 0; height: 0;
        border-left: 3mm solid transparent; border-right: 3mm solid transparent;
        border-top: 8mm solid #1e6b3a;
    }
    .nametag .corner-tr { position: absolute; top: 0; right: 0; width: 18mm; height: 14mm; }
    .nametag .corner-bl { position: absolute; bottom: 0; left: 0; width: 16mm; height: 14mm; }
    .nametag .corner-tr svg, .nametag .corner-bl svg { width: 100%; height: 100%; display: block; }

    .nametag .head { display: flex; align-items: center; gap: 2mm; position: relative; z-index: 2; }
    .nametag .head img.logo { width: 11mm; height: 11mm; object-fit: contain; }
    .nametag .titles { line-height: 1.1; }
    .nametag .title-main { font-size: 11pt; font-weight: 800; color: #5d3a14; letter-spacing: .5px; }
    .nametag .title-sub  { font-size: 6pt;  color: #1e6b3a; font-weight: 700; letter-spacing: .3px; }
    .nametag .title-tag  { font-size: 5.5pt; color: #1e6b3a; font-style: italic; }

    .nametag .divider { height: 1pt; background: #1e6b3a; margin: 2mm 0 3mm; position: relative; z-index: 1; }
    .nametag .body {
        flex: 1; display: flex; flex-direction: column;
        justify-content: center; gap: 1.5mm; padding-left: 2mm; position: relative; z-index: 2;
    }
    .nametag .row-line { display: flex; align-items: baseline; gap: 2mm; font-size: 9pt; }
    .nametag .row-line .label { font-weight: 700; color: #2d2d2d; min-width: 18mm; }
    .nametag .row-line .colon { color: #2d2d2d; }
    .nametag .row-line .value { color: #2d2d2d; font-weight: 500; flex: 1; }

    @media print {
        @page { size: A4; margin: 8mm; }
        body * { visibility: hidden; }
        .nt-stage, .nt-stage * { visibility: visible; }
        .nt-stage { position: absolute; top: 0; left: 0; right: 0; padding: 0; background: #fff; border: none; }
        .nametag { box-shadow: none; }
        .nt-controls, nav, footer { display: none !important; }
    }
</style>
@endpush

@section('content')
@php $sp = $user->studentProfile; @endphp
<div class="nt-shell px-4 py-8">
    <h1 class="text-2xl font-bold text-[#1e3a5f] mb-2">Name Tag</h1>
    <p class="text-gray-500 text-sm mb-6">Atur ukuran, lalu cetak atau simpan sebagai PDF.</p>

    <div class="nt-controls">
        <div>
            <label>Lebar (cm)</label>
            <input type="number" id="ntW" step="0.1" min="5" max="15" value="8.5" style="width:90px">
        </div>
        <div>
            <label>Tinggi (cm)</label>
            <input type="number" id="ntH" step="0.1" min="3" max="15" value="5.5" style="width:90px">
        </div>
        <div>
            <label>Preset</label>
            <select id="ntPreset">
                <option value="8.5,5.5" selected>Default 8.5 x 5.5 cm</option>
                <option value="9,5.5">9 x 5.5 cm</option>
                <option value="10,6">10 x 6 cm</option>
                <option value="8.5,5">8.5 x 5 cm</option>
            </select>
        </div>
        <div class="grow">
            <button type="button" onclick="window.print()">
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div class="nt-stage">
        <div class="nametag" id="nameTag">
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
                    <span class="value">{{ $user->name }}</span>
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
    </div>
</div>

<script>
(function() {
    var card = document.getElementById('nameTag');
    var w = document.getElementById('ntW');
    var h = document.getElementById('ntH');
    var preset = document.getElementById('ntPreset');
    function apply() {
        card.style.setProperty('--nt-w', w.value + 'cm');
        card.style.setProperty('--nt-h', h.value + 'cm');
        card.style.width  = w.value + 'cm';
        card.style.height = h.value + 'cm';
    }
    w.addEventListener('input', apply);
    h.addEventListener('input', apply);
    preset.addEventListener('change', function(e) {
        var p = e.target.value.split(',');
        w.value = p[0]; h.value = p[1]; apply();
    });
    apply();
})();
</script>
@endsection
