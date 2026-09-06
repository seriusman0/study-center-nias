@extends('layouts.app')
@section('title', 'Beranda - Study Center Nias')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 space-y-8">

    {{-- MENTOR: Fitur khusus mentor --}}
    @if(auth()->user()->hasRole('mentor'))
    <div class="space-y-4">

        {{-- Presensi Mentor --}}
        <div style="background:#fff;border-radius:20px;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:20px;">
            {{-- Header --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:42px;height:42px;border-radius:50%;background:#ecfdf5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" stroke="#059669" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <p style="font-size:10px;font-weight:600;color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;margin:0 0 2px;">Kehadiran Anda</p>
                    <h2 style="font-size:15px;font-weight:700;color:#111827;margin:0;line-height:1.2;">Presensi Mentor</h2>
                </div>
            </div>
            {{-- Status --}}
            @if($mentorPresensiToday)
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:12px;background:#ecfdf5;border-radius:14px;">
                <div style="width:28px;height:28px;border-radius:50%;background:#059669;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="12" height="12" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div>
                    <p style="font-size:12px;font-weight:700;color:#065f46;margin:0;">Sudah Presensi Hari Ini</p>
                    <p style="font-size:11px;color:#059669;margin:2px 0 0;">
                        Datang: <strong>{{ \Carbon\Carbon::parse($mentorPresensiToday->jam_datang)->format('H:i') }}</strong>
                        @if($mentorPresensiToday->jam_pulang)&nbsp;·&nbsp; Pulang: <strong>{{ \Carbon\Carbon::parse($mentorPresensiToday->jam_pulang)->format('H:i') }}</strong>@endif
                    </p>
                </div>
            </div>
            @else
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:12px;background:#fafafa;border-radius:14px;">
                <div style="width:28px;height:28px;border-radius:50%;background:#fbbf24;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="12" height="12" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div>
                    <p style="font-size:12px;font-weight:700;color:#374151;margin:0;">Belum Presensi Hari Ini</p>
                    <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">Catat kehadiranmu sekarang</p>
                </div>
            </div>
            @endif
            {{-- Footer --}}
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                <p style="font-size:11px;color:#9ca3af;margin:0;">Total presensi tercatat: <strong style="color:#374151;">{{ $mentorPresensiCount }}x</strong></p>
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('mentor-presensi.index') }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border:1.5px solid #d1d5db;background:#fff;color:#374151;font-size:12px;font-weight:500;border-radius:10px;text-decoration:none;">
                        <svg width="13" height="13" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Riwayat
                    </a>
                    <a href="{{ route('mentor-presensi.create') }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:#059669;color:#fff;font-size:12px;font-weight:600;border-radius:10px;text-decoration:none;">
                        <svg width="13" height="13" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Catat Kehadiran
                    </a>
                </div>
            </div>
        </div>

        {{-- Shortcut Grid --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <a href="{{ route('mentor.jurnal.index') }}"
               style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:16px;display:flex;flex-direction:column;gap:10px;text-decoration:none;">
                <div style="width:40px;height:40px;border-radius:12px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </div>
                <div>
                    <p style="font-size:13px;font-weight:700;color:#111827;margin:0;line-height:1.3;">Jurnal Siswa</p>
                    <p style="font-size:11px;color:#9ca3af;margin:3px 0 0;">Laporan &amp; progress</p>
                </div>
            </a>
            <a href="{{ route('mentor.kelas-master.index') }}"
               style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:16px;display:flex;flex-direction:column;gap:10px;text-decoration:none;">
                <div style="width:40px;height:40px;border-radius:12px;background:#fefce8;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div>
                    <p style="font-size:13px;font-weight:700;color:#111827;margin:0;line-height:1.3;">Kelas Master</p>
                    <p style="font-size:11px;color:#9ca3af;margin:3px 0 0;">Kelola kelas</p>
                </div>
            </a>
        </div>

        {{-- Presensi Siswa --}}
        <div style="background:#fff;border-radius:20px;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:20px;">
            {{-- Header --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:42px;height:42px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <p style="font-size:10px;font-weight:600;color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;margin:0 0 2px;">Absensi Kelas</p>
                    <h2 style="font-size:15px;font-weight:700;color:#111827;margin:0;line-height:1.2;">Presensi Siswa</h2>
                </div>
            </div>
            {{-- Status / Tabel --}}
            @if($presensiSiswaToday->isNotEmpty())
            <div style="overflow-x:auto;border-radius:12px;border:1px solid #e5e7eb;margin-bottom:14px;">
                <table style="width:100%;font-size:12px;border-collapse:collapse;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th style="padding:8px 12px;text-align:left;font-weight:600;color:#6b7280;border-bottom:1px solid #e5e7eb;">No</th>
                            <th style="padding:8px 12px;text-align:left;font-weight:600;color:#6b7280;border-bottom:1px solid #e5e7eb;">Kelas</th>
                            <th style="padding:8px 12px;text-align:center;font-weight:600;color:#6b7280;border-bottom:1px solid #e5e7eb;">Siswa</th>
                            <th style="padding:8px 12px;text-align:center;font-weight:600;color:#6b7280;border-bottom:1px solid #e5e7eb;">Jam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($presensiSiswaToday as $i => $sesi)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:8px 12px;color:#9ca3af;">{{ $i + 1 }}</td>
                            <td style="padding:8px 12px;font-weight:600;color:#111827;">{{ $sesi->kelasMaster?->nama ?? $sesi->kelas ?? '—' }}</td>
                            <td style="padding:8px 12px;text-align:center;color:#374151;">{{ $sesi->students->count() }}</td>
                            <td style="padding:8px 12px;text-align:center;color:#9ca3af;">{{ $sesi->jam_mulai ? \Carbon\Carbon::parse($sesi->jam_mulai)->format('H:i') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:12px;background:#fafafa;border-radius:14px;">
                <div style="width:28px;height:28px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="12" height="12" fill="none" stroke="#6b7280" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div>
                    <p style="font-size:12px;font-weight:700;color:#374151;margin:0;">Belum Ada Presensi Hari Ini</p>
                    <p style="font-size:11px;color:#6b7280;margin:2px 0 0;">Tambah absensi kelas sekarang</p>
                </div>
            </div>
            @endif
            {{-- Footer --}}
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                <p style="font-size:11px;color:#9ca3af;margin:0;">Total sesi tercatat: <strong style="color:#374151;">{{ $presensiSiswaCount }}x</strong></p>
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('presensi.index') }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border:1.5px solid #d1d5db;background:#fff;color:#374151;font-size:12px;font-weight:500;border-radius:10px;text-decoration:none;">
                        <svg width="13" height="13" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Riwayat
                    </a>
                    <a href="{{ route('presensi.create') }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:#2563eb;color:#fff;font-size:12px;font-weight:600;border-radius:10px;text-decoration:none;">
                        <svg width="13" height="13" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Input Presensi
                    </a>
                </div>
            </div>
        </div>

    </div>
    @endif

    {{-- 0. Announcements --}}
    @if(!empty($announcements) && $announcements->isNotEmpty())
    <div class="space-y-3">
        @foreach($announcements as $ann)
        @php
            $colors = [
                'info'    => ['bg' => '#eff6ff', 'border' => '#3b82f6', 'icon' => '#3b82f6', 'text' => '#1e40af'],
                'success' => ['bg' => '#f0fdf4', 'border' => '#22c55e', 'icon' => '#16a34a', 'text' => '#15803d'],
                'warning' => ['bg' => '#fffbeb', 'border' => '#f59e0b', 'icon' => '#d97706', 'text' => '#92400e'],
                'danger'  => ['bg' => '#fef2f2', 'border' => '#ef4444', 'icon' => '#dc2626', 'text' => '#991b1b'],
            ];
            $c = $colors[$ann->type] ?? $colors['info'];
        @endphp
        <div id="ann-card-{{ $ann->id }}"
             style="background:{{ $c['bg'] }};border-left:4px solid {{ $c['border'] }};border-radius:1rem;padding:1rem 1.25rem;position:relative;display:flex;gap:.75rem;align-items:flex-start;">
            {{-- Icon --}}
            <div style="flex-shrink:0;margin-top:.1rem">
                @if($ann->type === 'info')
                <svg width="20" height="20" fill="none" stroke="{{ $c['icon'] }}" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                @elseif($ann->type === 'success')
                <svg width="20" height="20" fill="none" stroke="{{ $c['icon'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($ann->type === 'warning')
                <svg width="20" height="20" fill="none" stroke="{{ $c['icon'] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                @else
                <svg width="20" height="20" fill="none" stroke="{{ $c['icon'] }}" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                @endif
            </div>
            {{-- Content --}}
            <div style="flex:1;min-width:0">
                <p style="font-weight:700;font-size:.875rem;color:{{ $c['text'] }};margin-bottom:.25rem">{{ $ann->title }}</p>
                <p style="font-size:.8125rem;color:{{ $c['text'] }};opacity:.85;line-height:1.5">{{ $ann->content }}</p>
            </div>
            {{-- Dismiss --}}
            <button onclick="dismissAnnCard({{ $ann->id }})"
                    style="flex-shrink:0;background:none;border:none;cursor:pointer;color:{{ $c['icon'] }};opacity:.6;padding:.25rem;line-height:1"
                    title="Tutup">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        @endforeach
    </div>
    @endif

    @if(!auth()->user()->hasRole('mentor'))
    {{-- 1. QR Code --}}
    <div style="background:#fff;border-radius:24px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #e5e7eb;padding:40px 32px;display:flex;flex-direction:column;align-items:center;text-align:center;">
        <h2 style="font-size:18px;font-weight:800;color:#111827;margin:0 0 8px;">QR Code Absensi</h2>
        <p style="font-size:13px;color:#9ca3af;font-weight:500;margin:0 0 28px;">Tunjukkan kode ini ke mentor saat absensi</p>
        <div style="background:#f9fafb;padding:16px;border-radius:20px;border:1px solid #e5e7eb;display:inline-block;margin-bottom:20px;">
            <div style="width:180px;height:180px;">{!! $qrHtml !!}</div>
        </div>
        <p style="font-size:15px;font-weight:700;color:#007a5c;letter-spacing:.08em;text-transform:uppercase;margin:0;">{{ $user->name }}</p>

        {{-- CTA --}}
        <div style="padding-top:24px; display:flex; flex-direction:column; gap:12px; width:100%;">
            @if(auth()->user()->hasRole('college'))
            <a href="{{ route('college-jurnal.index') }}"
               style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:16px;background:#007a5c;color:#fff;font-weight:800;font-size:14px;border-radius:9999px;text-decoration:none;box-shadow:0 4px 14px rgba(0,122,92,.3);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                Mulai Isi Jurnal College
            </a>
            @endif
            
            @if(auth()->user()->hasRole('scholarship_teenager'))
            <a href="{{ route('scholarship-teenager-jurnal.index') }}"
               style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:16px;background:#007a5c;color:#fff;font-weight:800;font-size:14px;border-radius:9999px;text-decoration:none;box-shadow:0 4px 14px rgba(0,122,92,.3);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                Mulai Isi Jurnal Remaja Beasiswa
            </a>
            @endif

            @if(auth()->user()->hasRole('student'))
            <a href="{{ route('jurnal.index') }}"
               style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:16px;background:#007a5c;color:#fff;font-weight:800;font-size:14px;border-radius:9999px;text-decoration:none;box-shadow:0 4px 14px rgba(0,122,92,.3);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                Mulai Isi Jurnal Remaja SC
            </a>
            @endif
            
            @if(!auth()->user()->hasRole(['college', 'scholarship_teenager', 'student']))
            <a href="{{ route('jurnal.index') }}"
               style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:16px;background:#007a5c;color:#fff;font-weight:800;font-size:14px;border-radius:9999px;text-decoration:none;box-shadow:0 4px 14px rgba(0,122,92,.3);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                Mulai Isi Jurnal
            </a>
            @endif
        </div>
    </div>

    {{-- 2. Jurnal Hari Ini & Galeri --}}
    @php
        $jurnalUrl  = auth()->user()->hasRole('college') ? route('college-jurnal.index') : route('jurnal.index');
        $plDone     = (bool) $todayEntry?->pl_checked;
        $pbDone     = (bool) $todayEntry?->pb_checked;
        $lifePct    = $totalLifeItems > 0 ? round($lifeChecksToday / $totalLifeItems * 100) : 0;
        $hasAnyProgress = $todayEntry || $lifeChecksToday > 0;
    @endphp
    <div style="background:#fff;border-radius:24px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #e5e7eb;overflow:hidden;">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#007a5c,#34d399);padding:24px 32px;position:relative;overflow:hidden;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
            <div style="position:absolute;top:-16px;right:-16px;width:128px;height:128px;background:rgba(255,255,255,.1);border-radius:50%;filter:blur(24px);"></div>
            <h2 style="color:#fff;font-size:16px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin:0;">JURNAL HARI INI</h2>
            @if($bibleItem)
                <p style="color:rgba(255,255,255,.85);font-size:13px;font-weight:500;margin:4px 0 0;">Hari ke-{{ $dayNo }}</p>
            @endif
        </div>

        <div style="padding:24px 24px 32px;">
            @if($bibleItem)
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:0;">
                {{-- PL --}}
                <div style="border-radius:16px;border:1px solid #e5e7eb;background:#fff;padding:16px;display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;{{ $plDone ? 'border:2px solid #059669;background:#ecfdf5;color:#059669;' : 'border:2px solid #e5e7eb;background:#f9fafb;' }}">
                        @if($plDone)
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        @endif
                    </div>
                    <div style="min-width:0;">
                        <p style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin:0 0 3px;">PERJANJIAN LAMA</p>
                        <p style="font-size:13px;font-weight:700;color:#111827;margin:0;">{{ $bibleItem->pl_text ?: '—' }}</p>
                    </div>
                </div>
                {{-- PB --}}
                <div style="border-radius:16px;border:1px solid #e5e7eb;background:#fff;padding:16px;display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;{{ $pbDone ? 'border:2px solid #059669;background:#ecfdf5;color:#059669;' : 'border:2px solid #e5e7eb;background:#f9fafb;' }}">
                        @if($pbDone)
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        @endif
                    </div>
                    <div style="min-width:0;">
                        <p style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin:0 0 3px;">PERJANJIAN BARU</p>
                        <p style="font-size:13px;font-weight:700;color:#111827;margin:0;">{{ $bibleItem->pb_text ?: '—' }}</p>
                    </div>
                </div>
            </div>
            @else
            <div style="border-radius:16px;border:2px dashed #e5e7eb;background:#f9fafb;padding:24px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
                <p style="font-size:13px;color:#9ca3af;font-weight:500;margin:0;">Porsi baca Alkitab belum tersedia untuk hari ini.</p>
            </div>
            @endif

            {{-- Progress --}}
            @if($totalLifeItems > 0)
            <div style="margin-top:20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <span style="font-size:13px;font-weight:700;color:#374151;">Progress Jadwal Kehidupan</span>
                    <span style="font-size:13px;font-weight:800;{{ $lifePct === 100 ? 'color:#007a5c;' : 'color:#9ca3af;' }}">{{ $lifeChecksToday }}/{{ $totalLifeItems }}</span>
                </div>
                <div style="background:#e5e7eb;border-radius:9999px;height:8px;overflow:hidden;">
                    <div style="height:8px;border-radius:9999px;transition:width .7s ease-out;{{ $lifePct === 100 ? 'background:#059669;' : 'background:#34d399;' }}width:{{ $lifePct }}%;"></div>
                </div>
            </div>
            @endif

            {{-- Galeri Kegiatan --}}
            <div style="margin-top:28px;padding-top:24px;border-top:1px solid #f3f4f6;">
                <h3 style="font-size:13px;font-weight:700;color:#111827;margin:0 0 14px;text-align:center;">Galeri Kegiatan</h3>

                @if($photos->isEmpty())
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;opacity:.25;margin-bottom:16px;">
                    <div style="aspect-ratio:1;background:#d1d5db;border-radius:12px;"></div>
                    <div style="aspect-ratio:1;background:#d1d5db;border-radius:12px;margin-top:8px;"></div>
                    <div style="aspect-ratio:1;background:#d1d5db;border-radius:12px;"></div>
                </div>
                <div style="display:flex;justify-content:center;opacity:.2;margin-bottom:8px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                @else
                <div class="swiper galeri-swiper" style="border-radius:16px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);">
                    <div class="swiper-wrapper">
                        @foreach($photos as $foto)
                        <div class="swiper-slide" style="position:relative;">
                            <img src="{{ asset('storage/' . $foto) }}" alt="Foto Kegiatan" style="width:100%;height:192px;object-fit:cover;display:block;">
                            <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.2),transparent);"></div>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
                <div style="display:flex;justify-content:center;margin-top:14px;opacity:.2;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                @endif
            </div>
        </div>
    </div>

    @endif {{-- end non-mentor block --}}

    {{-- 3. Rekan Mahasiswa (college only) --}}
    @if(auth()->user()->hasRole('college') && $collegeUsers->isNotEmpty())
    <div style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:24px;">
        <h2 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;">Jurnal Rekan Mahasiswa</h2>
        <div style="display:flex;flex-direction:column;gap:4px;">
            @foreach($collegeUsers as $cu)
            <a href="{{ route('college-jurnal.other', $cu->id) }}"
               style="display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:12px;text-decoration:none;background:#fff;">
                <img src="{{ $cu->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($cu->name).'&background=007a5c&color=fff' }}"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb;flex-shrink:0;" alt="{{ $cu->name }}">
                <span style="font-weight:600;color:#111827;font-size:14px;flex:1;min-width:0;">{{ $cu->name }}</span>
                <span style="color:#9ca3af;font-size:12px;flex-shrink:0;">Lihat Jurnal &rarr;</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 4. Blog Cabang --}}
    @if($blogs->count())
    <div style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:24px;">
        <h2 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;">Artikel Terbaru</h2>
        <div style="display:flex;flex-direction:column;gap:4px;">
            @foreach($blogs as $blog)
            <a href="{{ route('blog.show', $blog->slug) }}"
               style="display:flex;gap:12px;padding:10px 12px;border-radius:12px;text-decoration:none;">
                <div style="flex:1;min-width:0;">
                    <h3 style="font-weight:600;color:#111827;font-size:14px;line-height:1.4;margin:0 0 3px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $blog->title }}</h3>
                    <p style="font-size:11px;color:#9ca3af;margin:0;">{{ $blog->published_at->format('d M Y') }} · {{ $blog->cabang?->nama ?? 'Study Center' }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif



</div>
@endsection

@push('scripts')
@if($photos->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
new Swiper('.galeri-swiper', {
    loop: true,
    autoplay: { delay: 2000, disableOnInteraction: false },
    pagination: { el: '.swiper-pagination', clickable: true },
});
</script>
@endif
<script>
function dismissAnnCard(id) {
    var el = document.getElementById('ann-card-' + id);
    if (el) { el.style.transition = 'opacity .3s'; el.style.opacity = '0'; setTimeout(function(){ el.remove(); }, 300); }
    fetch('/announcements/' + id + '/dismiss', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' }
    });
}
</script>
@endpush
