@extends('layouts.app')
@section('title', 'Presensi Saya')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-sc-ink-900">Presensi Mentor Saya</h1>
            <p class="text-sm text-sc-ink-500">Catatan kehadiran & sesi mengajar.</p>
        </div>
        <a href="{{ route('mentor-presensi.create') }}" class="inline-flex items-center gap-1 px-4 py-2 bg-sc-teal-600 hover:bg-sc-teal-700 text-white rounded-lg shadow-sc-2 text-sm font-semibold transition">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg>
            Buat Presensi
        </a>
    </div>

    @if(!auth()->user()->cabang_id)
        <div class="mb-4 p-4 bg-sc-yellow-100 border border-sc-yellow-300 rounded-lg text-sm text-sc-yellow-700">
            Akun Anda belum terkait cabang. Hubungi admin untuk dapat membuat presensi.
        </div>
    @endif

    <div class="bg-white shadow-sc-1 border border-sc-line rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-sc-teal-50 text-sc-teal-800 text-left">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Datang</th>
                    <th class="px-4 py-3">Pulang</th>
                    <th class="px-4 py-3 text-center">Murid</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr class="border-t border-sc-line hover:bg-sc-teal-50/50 transition">
                    <td class="px-4 py-3 text-sc-ink-700">{{ $r->tanggal->locale('id')->isoFormat('ddd, D MMM Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-sc-ink-900">{{ $r->kelas?->nama ?? '—' }}</div>
                        <div class="text-xs text-sc-ink-500">{{ $r->cabang?->nama }}</div>
                    </td>
                    <td class="px-4 py-3 text-sc-ink-700">{{ substr($r->jam_datang, 0, 5) }}</td>
                    <td class="px-4 py-3 text-sc-ink-700">{{ substr($r->jam_pulang, 0, 5) }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-sc-teal-700">{{ $r->jumlah_murid }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($r->canEdit())
                            <a href="{{ route('mentor-presensi.edit', $r) }}" class="text-sc-teal-700 hover:underline text-xs font-semibold">Edit</a>
                            <form method="POST" action="{{ route('mentor-presensi.destroy', $r) }}" class="inline"
                                onsubmit="return confirm('Hapus presensi ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-2 font-semibold">Hapus</button>
                            </form>
                        @else
                            <span class="text-xs text-sc-ink-300" title="Hanya 24 jam pertama">terkunci</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-sc-ink-500">Belum ada catatan presensi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-sc-line">{{ $records->links() }}</div>
    </div>
</div>
@endsection
