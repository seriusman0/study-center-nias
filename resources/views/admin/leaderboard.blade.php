@extends('layouts.admin')
@section('page-title', 'Leaderboard')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
@endpush

@section('content')
<style>
.lb-wrap { max-width: 960px; }

.lb-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; flex-wrap:wrap; gap:.75rem; }
.lb-header-left { display:flex; align-items:center; gap:.75rem; }
.lb-trophy { font-size:2.2rem; line-height:1; }
.lb-title { font-size:1.3rem; font-weight:800; color:#1a1a2e; margin:0; }
.lb-subtitle { font-size:.75rem; color:#868e96; margin:0; }
.lb-export-btns { display:flex; gap:.5rem; }
.lb-btn-export {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.38rem .9rem; border-radius:.5rem; font-size:.78rem; font-weight:600;
    cursor:pointer; border:none; transition:all .15s;
}
.lb-btn-export.pdf  { background:#e03131; color:#fff; }
.lb-btn-export.pdf:hover  { background:#c92a2a; }
.lb-btn-export.png  { background:#1971c2; color:#fff; }
.lb-btn-export.png:hover  { background:#1864ab; }

.lb-role-tabs { display:flex; gap:.4rem; margin-bottom:1rem; flex-wrap:wrap; }
.lb-role-tab {
    padding:.35rem 1rem; border-radius:.5rem; font-size:.8rem; font-weight:700;
    text-decoration:none; border:1.5px solid #dee2e6; background:#f8f9fa; color:#495057;
    transition:all .15s;
}
.lb-role-tab.active  { background:#1a1a2e; color:#fff; border-color:#1a1a2e; }
.lb-role-tab:hover:not(.active) { background:#e9ecef; color:#212529; text-decoration:none; }

.lb-filters { background:#fff; border:1px solid #e9ecef; border-radius:.75rem; padding:1rem 1.25rem; margin-bottom:1.25rem; }
.lb-filters form { display:flex; flex-wrap:wrap; gap:.6rem; align-items:flex-end; }
.lb-filters .form-group { margin:0; min-width:140px; }
.lb-filters label { font-size:.68rem; font-weight:700; text-transform:uppercase; color:#868e96; margin-bottom:.2rem; display:block; letter-spacing:.05em; }
.lb-filters select,.lb-filters input[type=date] { font-size:.82rem; padding:.32rem .55rem; border:1px solid #dee2e6; border-radius:.4rem; height:auto; }
.lb-filters .btn { font-size:.82rem; padding:.32rem .9rem; border-radius:.45rem; font-weight:600; }

.lb-metric-tabs { display:flex; gap:.4rem; margin-bottom:1.25rem; flex-wrap:wrap; }
.lb-metric-tab {
    padding:.32rem .9rem; border-radius:999px; font-size:.78rem; font-weight:600;
    text-decoration:none; border:1.5px solid #dee2e6; background:#f8f9fa; color:#495057;
    transition:all .15s;
}
.lb-metric-tab.active  { background:#1a1a2e; color:#fff; border-color:#1a1a2e; }
.lb-metric-tab:hover:not(.active) { background:#e9ecef; color:#212529; text-decoration:none; }

.lb-podium-wrap {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
    border-radius: 1.25rem;
    padding: 2rem 1.5rem 0;
    margin-bottom: 1.5rem;
    overflow: hidden;
    position: relative;
}
.lb-podium-wrap::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.lb-podium-title {
    text-align: center;
    color: rgba(255,255,255,.5);
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .12em;
    margin-bottom: 1.75rem;
}
.lb-podium-stage { display: flex; justify-content: center; align-items: flex-end; gap: 0; }
.lb-podium-person { display: flex; flex-direction: column; align-items: center; position: relative; }

.lb-podium-avatar-wrap { position: relative; margin-bottom: .6rem; }
.lb-podium-avatar {
    border-radius: 50%; object-fit: cover;
    border: 3px solid rgba(255,255,255,.25);
    background: #dee2e6;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; color: #495057;
}
.lb-podium-person.rank1 .lb-podium-avatar { width:100px; height:100px; border-color:#ffd43b; border-width:4px; box-shadow: 0 0 0 6px rgba(255,212,59,.3), 0 12px 40px rgba(0,0,0,.5); font-size:2.5rem; }
.lb-podium-person.rank2 .lb-podium-avatar { width:70px; height:70px; border-color:#ced4da; font-size:1.7rem; }
.lb-podium-person.rank3 .lb-podium-avatar { width:70px; height:70px; border-color:#cd7f32; font-size:1.7rem; }

.lb-podium-badge {
    position: absolute; bottom: -4px; right: -4px;
    width: 22px; height: 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; border: 2px solid #1a1a2e;
}
.lb-podium-person.rank1 .lb-podium-badge { background:#ffd43b; }
.lb-podium-person.rank2 .lb-podium-badge { background:#ced4da; }
.lb-podium-person.rank3 .lb-podium-badge { background:#cd7f32; }

.lb-podium-name { font-size:.78rem; font-weight:700; color:#fff; text-align:center; max-width:90px; line-height:1.3; margin-bottom:.2rem; }
.lb-podium-score { font-size:.68rem; color:rgba(255,255,255,.6); font-weight:600; margin-bottom:.75rem; }

.lb-podium-block {
    border-radius: .6rem .6rem 0 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 800; color: rgba(255,255,255,.7);
    min-width: 80px;
}
.lb-podium-person.rank1 .lb-podium-block { height: 72px; background: linear-gradient(180deg,#ffd43b,#f59f00); color:#7d4e00; min-width:100px; }
.lb-podium-person.rank2 .lb-podium-block { height: 52px; background: linear-gradient(180deg,#dee2e6,#adb5bd); color:#495057; }
.lb-podium-person.rank3 .lb-podium-block { height: 40px; background: linear-gradient(180deg,#cd7f32,#a0522d); color:#fff; }

.lb-table-wrap { background:#fff; border:1px solid #e9ecef; border-radius:.9rem; overflow:hidden; }
.lb-table { width:100%; border-collapse:collapse; }
.lb-table thead tr { background: linear-gradient(90deg,#f8f9fa,#f1f3f5); border-bottom:2px solid #dee2e6; }
.lb-table thead th { padding:.65rem 1rem; font-size:.68rem; font-weight:800; text-transform:uppercase; color:#495057; letter-spacing:.07em; white-space:nowrap; }
.lb-table tbody tr { border-bottom:1px solid #f1f3f5; transition:background .12s; }
.lb-table tbody tr:hover { background:#f8f9fa; }
.lb-table td { padding:.7rem 1rem; vertical-align:middle; }
.lb-table tbody tr:last-child { border-bottom:none; }
.lb-table tbody tr.top1 { background: linear-gradient(90deg, #fff9db, #fff); }
.lb-table tbody tr.top2 { background: linear-gradient(90deg, #f8f9fa, #fff); }
.lb-table tbody tr.top3 { background: linear-gradient(90deg, #fff4e6, #fff); }

.lb-rank { font-size:1rem; font-weight:800; color:#adb5bd; width:36px; text-align:center; }
.lb-rank.gold   { color:#f59f00; }
.lb-rank.silver { color:#868e96; }
.lb-rank.bronze { color:#c8771c; }

.lb-avatar { width:34px; height:34px; border-radius:50%; object-fit:cover; background:#e9ecef; flex-shrink:0; }
.lb-avatar-init { width:34px; height:34px; border-radius:50%; background:#e9ecef; font-weight:700; font-size:.9rem; color:#495057; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.lb-name { font-weight:700; font-size:.85rem; color:#212529; }
.lb-username { font-size:.7rem; color:#adb5bd; }
.lb-cabang { font-size:.7rem; background:#e7f5ff; color:#1971c2; padding:.1rem .55rem; border-radius:999px; white-space:nowrap; font-weight:600; }

.lb-score-main { font-size:1.05rem; font-weight:800; color:#1971c2; }
.lb-score-zero { font-size:1.05rem; font-weight:700; color:#ced4da; }
.lb-stat { text-align:center; font-size:.84rem; font-weight:700; color:#495057; }
.lb-stat-zero { text-align:center; font-size:.84rem; font-weight:500; color:#ced4da; }
.lb-bar { height:5px; border-radius:3px; background:#e9ecef; margin-top:3px; min-width:50px; max-width:80px; margin-left:auto; margin-right:auto; }
.lb-bar-fill { height:100%; border-radius:3px; transition:width .4s; }

.lb-empty { text-align:center; padding:3rem 1rem; color:#adb5bd; }
.lb-footer { font-size:.7rem; color:#adb5bd; margin-top:.6rem; text-align:right; }
</style>

<div class="lb-wrap" id="lb-main">

{{-- Header --}}
<div class="lb-header">
    <div class="lb-header-left">
        <div class="lb-trophy">🏆</div>
        <div>
            <div class="lb-title">Leaderboard Jurnal</div>
            <div class="lb-subtitle">Peringkat berdasarkan laporan jurnal harian</div>
        </div>
    </div>
    <div class="lb-export-btns">
        <button class="lb-btn-export pdf" onclick="exportPDF()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Export PDF
        </button>
        <button class="lb-btn-export png" onclick="exportPNG()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            Export PNG
        </button>
    </div>
</div>

@php
    $roleTabs = [
        'student'              => 'Siswa',
        'college'              => 'College',
        'scholarship_teenager' => 'Remaja Beasiswa',
    ];
    $metricTabs = [
        'score'      => '⭐ Total Skor',
        'pl_count'   => '📖 PL',
        'pb_count'   => '📚 PB',
        'life_count' => '✅ Life Checks',
    ];
    $barColors = ['score'=>'#1971c2','pl_count'=>'#2f9e44','pb_count'=>'#e67700','life_count'=>'#ae3ec9'];
    $baseParams = array_filter(['date_from' => $dateFrom, 'date_to' => $dateTo, 'cabang_id' => $cabangId, 'metric' => $metric]);
    $maxScore = isset($students) ? ($students->max($metric) ?: 1) : 1;
    $uniqueCabangs = isset($students) ? $students->pluck('cabang_name')->filter()->unique() : collect();
    $singleCabang  = $uniqueCabangs->count() === 1 ? $uniqueCabangs->first() : null;
@endphp

{{-- Role tabs --}}
<div class="lb-role-tabs">
    @foreach($roleTabs as $key => $label)
        <a href="{{ route('admin.leaderboard', array_merge(array_filter(['date_from' => $dateFrom, 'date_to' => $dateTo, 'cabang_id' => $cabangId, 'metric' => $metric]), ['role' => $key])) }}"
           class="lb-role-tab {{ $role === $key ? 'active' : '' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Filters --}}
<div class="lb-filters">
    <form method="GET" action="{{ route('admin.leaderboard') }}">
        <input type="hidden" name="role" value="{{ $role }}">
        <input type="hidden" name="metric" value="{{ $metric }}">
        @if($role === 'student')
        <div class="form-group">
            <label>Cabang</label>
            <select name="cabang_id" class="form-control form-control-sm">
                <option value="">Semua Cabang</option>
                @foreach($cabangs as $cabang)
                    <option value="{{ $cabang->id }}" {{ $cabangId == $cabang->id ? 'selected' : '' }}>{{ $cabang->nama }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="form-group">
            <label>Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="form-group">
            <label>Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-filter mr-1"></i>Filter
        </button>
        <a href="{{ route('admin.leaderboard', ['role' => $role]) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </form>
</div>

{{-- Metric tabs --}}
<div class="lb-metric-tabs">
    @foreach($metricTabs as $key => $label)
        <a href="{{ route('admin.leaderboard', array_merge($baseParams, ['metric' => $key, 'role' => $role])) }}"
           class="lb-metric-tab {{ $metric === $key ? 'active' : '' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Export wrapper --}}
<div id="lb-export-target">

@if(!isset($students) || $students->isEmpty())
<div class="lb-table-wrap">
    <div class="lb-empty">
        <div style="font-size:2.5rem;margin-bottom:.5rem">📭</div>
        <div>Belum ada data untuk filter ini.</div>
    </div>
</div>
@else

{{-- Podium Top 3 --}}
@php $top3Eligible = $students->filter(fn($s) => $s->$metric > 0)->take(3); @endphp
@if($top3Eligible->count() >= 2)
@php $p1 = $top3Eligible->get(0); $p2 = $top3Eligible->get(1); $p3 = $top3Eligible->get(2); @endphp
<div class="lb-podium-wrap" id="lb-podium">
    <div class="lb-podium-title">✦ Top Performers ✦</div>
    <div class="lb-podium-stage">

        @if($p2)
        <div class="lb-podium-person rank2" style="margin-right:-4px">
            <div class="lb-podium-avatar-wrap">
                @if($p2->avatar)
                    <img src="{{ $p2->avatar }}" class="lb-podium-avatar" alt="{{ $p2->name }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="lb-podium-avatar" style="display:none;width:70px;height:70px;font-size:1.7rem;color:#1a1a2e;background:#ced4da">{{ mb_strtoupper(mb_substr($p2->name,0,1)) }}</div>
                @else
                    <div class="lb-podium-avatar" style="width:70px;height:70px;font-size:1.7rem;color:#1a1a2e;background:#ced4da">{{ mb_strtoupper(mb_substr($p2->name,0,1)) }}</div>
                @endif
                <div class="lb-podium-badge">🥈</div>
            </div>
            <div class="lb-podium-name">{{ $p2->name }}</div>
            <div class="lb-podium-score">{{ number_format($p2->$metric) }} poin</div>
            <div class="lb-podium-block" style="width:100%">2</div>
        </div>
        @endif

        @if($p1)
        <div class="lb-podium-person rank1" style="z-index:2">
            <div class="lb-podium-avatar-wrap">
                @if($p1->avatar)
                    <img src="{{ $p1->avatar }}" class="lb-podium-avatar" alt="{{ $p1->name }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="lb-podium-avatar" style="display:none;width:100px;height:100px;font-size:2.5rem">{{ mb_strtoupper(mb_substr($p1->name,0,1)) }}</div>
                @else
                    <div class="lb-podium-avatar" style="width:100px;height:100px;font-size:2.5rem">{{ mb_strtoupper(mb_substr($p1->name,0,1)) }}</div>
                @endif
                <div class="lb-podium-badge">🥇</div>
            </div>
            <div class="lb-podium-name">{{ $p1->name }}</div>
            <div class="lb-podium-score">{{ number_format($p1->$metric) }} poin</div>
            <div class="lb-podium-block" style="width:100%">1</div>
        </div>
        @endif

        @if($p3)
        <div class="lb-podium-person rank3" style="margin-left:-4px">
            <div class="lb-podium-avatar-wrap">
                @if($p3->avatar)
                    <img src="{{ $p3->avatar }}" class="lb-podium-avatar" alt="{{ $p3->name }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="lb-podium-avatar" style="display:none;width:70px;height:70px;font-size:1.7rem;color:#1a1a2e;background:#e8c49a">{{ mb_strtoupper(mb_substr($p3->name,0,1)) }}</div>
                @else
                    <div class="lb-podium-avatar" style="width:70px;height:70px;font-size:1.7rem;color:#1a1a2e;background:#e8c49a">{{ mb_strtoupper(mb_substr($p3->name,0,1)) }}</div>
                @endif
                <div class="lb-podium-badge">🥉</div>
            </div>
            <div class="lb-podium-name">{{ $p3->name }}</div>
            <div class="lb-podium-score">{{ number_format($p3->$metric) }} poin</div>
            <div class="lb-podium-block" style="width:100%">3</div>
        </div>
        @endif

    </div>
</div>
@endif

@if($singleCabang)
<div style="font-size:.75rem;color:#1971c2;font-weight:600;margin-bottom:.5rem;padding-left:.25rem;">
    📍 Menampilkan data cabang: <strong>{{ $singleCabang }}</strong>
</div>
@endif

<div class="lb-table-wrap">
    <table class="lb-table">
        <thead>
            <tr>
                <th style="width:44px;text-align:center">#</th>
                <th>Nama</th>
                @if(!$singleCabang && $role === 'student')<th>Cabang</th>@endif
                <th style="text-align:center">PL</th>
                <th style="text-align:center">PB</th>
                <th style="text-align:center">Life</th>
                <th style="text-align:center">Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $s)
            @php
                $rank = $i + 1;
                $rankClass = $rank===1 ? 'gold' : ($rank===2 ? 'silver' : ($rank===3 ? 'bronze' : ''));
                $rowClass  = $rank===1 ? 'top1' : ($rank===2 ? 'top2' : ($rank===3 ? 'top3' : ''));
                $pct = $maxScore > 0 ? round($s->$metric / $maxScore * 100) : 0;
            @endphp
            <tr class="{{ $rowClass }}">
                <td style="text-align:center">
                    <div class="lb-rank {{ $rankClass }}">
                        @if($rank===1)🥇@elseif($rank===2)🥈@elseif($rank===3)🥉@else {{ $rank }}@endif
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:.6rem">
                        @if($s->avatar)
                            <img src="{{ $s->avatar }}" class="lb-avatar" alt="{{ $s->name }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="lb-avatar-init" style="display:none">{{ mb_strtoupper(mb_substr($s->name,0,1)) }}</div>
                        @else
                            <div class="lb-avatar-init">{{ mb_strtoupper(mb_substr($s->name,0,1)) }}</div>
                        @endif
                        <div>
                            <div class="lb-name">{{ $s->name }}</div>
                            <div class="lb-username">{{ $s->username }}</div>
                        </div>
                    </div>
                </td>
                @if(!$singleCabang && $role === 'student')
                <td>
                    @if($s->cabang_name)
                        <span class="lb-cabang">{{ $s->cabang_name }}</span>
                    @else
                        <span style="color:#ced4da;font-size:.75rem">—</span>
                    @endif
                </td>
                @endif
                <td class="{{ $s->pl_count > 0 ? 'lb-stat' : 'lb-stat-zero' }}">{{ $s->pl_count }}</td>
                <td class="{{ $s->pb_count > 0 ? 'lb-stat' : 'lb-stat-zero' }}">{{ $s->pb_count }}</td>
                <td class="{{ $s->life_count > 0 ? 'lb-stat' : 'lb-stat-zero' }}">{{ $s->life_count }}</td>
                <td style="text-align:center">
                    @if($s->score > 0)
                        <div class="lb-score-main">{{ number_format($s->score) }}</div>
                        <div class="lb-bar">
                            <div class="lb-bar-fill" style="width:{{ $pct }}%;background:{{ $barColors[$metric] }}"></div>
                        </div>
                    @else
                        <div class="lb-score-zero">0</div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="lb-footer">
    Skor = PL + PB + Life Checks &nbsp;|&nbsp; {{ $students->count() }} pengguna
    @if($cabangId && $role === 'student') &nbsp;|&nbsp; Cabang: {{ $cabangs->firstWhere('id', $cabangId)?->nama }} @endif
    @if($dateFrom || $dateTo) &nbsp;|&nbsp; {{ $dateFrom ?? '—' }} s/d {{ $dateTo ?? '—' }} @endif
</div>

@endif
</div>{{-- #lb-export-target --}}
</div>{{-- .lb-wrap --}}

@push('scripts')
<script>
function exportPNG() {
    const el = document.getElementById('lb-export-target');
    html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#f4f6f9' }).then(canvas => {
        const a = document.createElement('a');
        a.download = 'leaderboard-{{ now()->format("Ymd") }}.png';
        a.href = canvas.toDataURL('image/png');
        a.click();
    });
}

function exportPDF() {
    const el = document.getElementById('lb-export-target');
    html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#f4f6f9' }).then(canvas => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const w = pdf.internal.pageSize.getWidth();
        const h = (canvas.height / canvas.width) * w;
        pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, w, h);
        pdf.save('leaderboard-{{ now()->format("Ymd") }}.pdf');
    });
}
</script>
@endpush
@endsection
