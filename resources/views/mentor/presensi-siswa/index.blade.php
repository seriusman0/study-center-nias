@extends('layouts.app')
@section('title', 'Presensi Siswa - Study Center Nias')

@section('content')
<div style="max-width:720px;margin:0 auto;padding:24px 16px;display:flex;flex-direction:column;gap:20px;">

    {{-- Page Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <div>
            <h1 style="font-size:22px;font-weight:700;color:#111827;margin:0;line-height:1.2;">Presensi Siswa</h1>
            <p style="font-size:12px;color:#9ca3af;margin:4px 0 0;">{{ auth()->user()->cabang?->nama ?? 'Cabang Anda' }}</p>
        </div>
        <a href="{{ route('presensi.create') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;background:#2563eb;color:#fff;font-size:13px;font-weight:600;border-radius:12px;text-decoration:none;white-space:nowrap;flex-shrink:0;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Catat Presensi
        </a>
    </div>

    {{-- Filter Card --}}
    <form method="GET" id="filterForm"
          style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.05);padding:16px;">
        <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kelas / materi..."
                   style="flex:1;min-width:140px;border:1px solid #d1d5db;border-radius:10px;padding:8px 12px;font-size:13px;color:#111827;outline:none;">
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                   style="border:1px solid #d1d5db;border-radius:10px;padding:8px 10px;font-size:13px;color:#374151;outline:none;">
            <span style="font-size:11px;color:#9ca3af;flex-shrink:0;">s/d</span>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                   style="border:1px solid #d1d5db;border-radius:10px;padding:8px 10px;font-size:13px;color:#374151;outline:none;">
            <button type="submit"
                    style="padding:8px 16px;background:#059669;color:#fff;font-size:13px;font-weight:600;border:none;border-radius:10px;cursor:pointer;white-space:nowrap;">
                Filter
            </button>
            <a href="{{ route('presensi.index') }}"
               style="padding:8px 16px;border:1.5px solid #d1d5db;background:#fff;color:#374151;font-size:13px;font-weight:500;border-radius:10px;text-decoration:none;white-space:nowrap;">
                Reset
            </a>
        </div>
    </form>

    {{-- Data Card --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">

        {{-- Card Header --}}
        <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:13px;font-weight:600;color:#374151;margin:0;">{{ $presensi->total() }} catatan presensi</p>
            <a href="{{ route('presensi.report', request()->query()) }}"
               style="font-size:12px;font-weight:600;color:#059669;text-decoration:none;">Laporan Lengkap</a>
        </div>

        {{-- Rows --}}
        @forelse($presensi as $p)
        <div style="padding:14px 18px;border-bottom:1px solid #f9fafb;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                <div style="flex:1;min-width:0;">
                    <p style="font-size:14px;font-weight:600;color:#111827;margin:0;">{{ $p->kelas }}</p>
                    <p style="font-size:11px;color:#9ca3af;margin:3px 0 0;">
                        {{ $p->tanggal->format('d M Y') }}
                        &nbsp;·&nbsp; {{ substr($p->jam_mulai,0,5) }}–{{ substr($p->jam_selesai,0,5) }}
                    </p>
                    @if($p->materi)
                    <p style="font-size:11px;color:#6b7280;margin:4px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->materi }}</p>
                    @endif
                    <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                        <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:#dcfce7;color:#15803d;font-weight:500;">Hadir {{ $p->hadir_count }}</span>
                        @if($p->izin_count > 0)<span style="font-size:11px;padding:2px 8px;border-radius:20px;background:#fef9c3;color:#a16207;font-weight:500;">Izin {{ $p->izin_count }}</span>@endif
                        @if($p->sakit_count > 0)<span style="font-size:11px;padding:2px 8px;border-radius:20px;background:#dbeafe;color:#1d4ed8;font-weight:500;">Sakit {{ $p->sakit_count }}</span>@endif
                        @if($p->alpha_count > 0)<span style="font-size:11px;padding:2px 8px;border-radius:20px;background:#fee2e2;color:#b91c1c;font-weight:500;">Alpha {{ $p->alpha_count }}</span>@endif
                    </div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <a href="{{ route('presensi.show', $p->id) }}"
                       style="padding:6px 12px;font-size:11px;font-weight:600;color:#059669;border:1.5px solid #a7f3d0;border-radius:8px;text-decoration:none;background:#fff;">
                        Lihat
                    </a>
                    <a href="{{ route('presensi.edit', $p->id) }}"
                       style="padding:6px 12px;font-size:11px;font-weight:600;color:#d97706;border:1.5px solid #fde68a;border-radius:8px;text-decoration:none;background:#fff;">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('presensi.destroy', $p->id) }}"
                          onsubmit="return confirm('Hapus presensi ini?')" style="display:inline;margin:0;">
                        @csrf @method('DELETE')
                        <button type="submit"
                                style="padding:6px 12px;font-size:11px;font-weight:600;color:#dc2626;border:1.5px solid #fecaca;border-radius:8px;background:#fff;cursor:pointer;">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        {{-- Empty State --}}
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 24px;">
            <svg width="72" height="72" viewBox="0 0 72 72" fill="none" style="margin-bottom:16px;opacity:.35;">
                <rect x="14" y="10" width="44" height="52" rx="6" fill="#e5e7eb"/>
                <rect x="22" y="22" width="28" height="3" rx="1.5" fill="#9ca3af"/>
                <rect x="22" y="31" width="20" height="3" rx="1.5" fill="#9ca3af"/>
                <rect x="22" y="40" width="24" height="3" rx="1.5" fill="#9ca3af"/>
                <circle cx="53" cy="53" r="12" fill="#d1d5db"/>
                <line x1="49" y1="53" x2="57" y2="53" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
                <line x1="53" y1="49" x2="53" y2="57" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <p style="font-size:14px;font-weight:600;color:#6b7280;margin:0;">Belum ada presensi</p>
            <p style="font-size:12px;color:#9ca3af;margin:6px 0 0;">Mulai dengan mencatat presensi kelas pertama Anda</p>
        </div>
        @endforelse

        {{-- Pagination --}}
        @if($presensi->hasPages())
        <div style="padding:12px 18px;border-top:1px solid #f3f4f6;">
            {{ $presensi->withQueryString()->links() }}
        </div>
        @endif

    </div>

</div>
@endsection
