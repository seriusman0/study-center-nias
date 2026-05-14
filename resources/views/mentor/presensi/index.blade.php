@extends('layouts.app')
@section('title', 'Presensi Saya')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-[#1e3a5f]">Presensi Mentor Saya</h1>
            <p class="text-sm text-gray-500">Catatan kehadiran & sesi mengajar.</p>
        </div>
        <a href="{{ route('mentor-presensi.create') }}" class="px-4 py-2 bg-[#1e3a5f] text-white rounded shadow text-sm font-semibold">
            + Buat Presensi
        </a>
    </div>

    @if(!auth()->user()->cabang_id)
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
            Akun Anda belum terkait cabang. Hubungi admin untuk dapat membuat presensi.
        </div>
    @endif

    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Kelas</th>
                    <th class="px-4 py-2">Datang</th>
                    <th class="px-4 py-2">Pulang</th>
                    <th class="px-4 py-2 text-center">Murid</th>
                    <th class="px-4 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $r->tanggal->locale('id')->isoFormat('ddd, D MMM Y') }}</td>
                    <td class="px-4 py-2">
                        <div class="font-semibold">{{ $r->kelas?->nama ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $r->cabang?->nama }}</div>
                    </td>
                    <td class="px-4 py-2">{{ substr($r->jam_datang, 0, 5) }}</td>
                    <td class="px-4 py-2">{{ substr($r->jam_pulang, 0, 5) }}</td>
                    <td class="px-4 py-2 text-center">{{ $r->jumlah_murid }}</td>
                    <td class="px-4 py-2 text-right">
                        @if($r->canEdit())
                            <a href="{{ route('mentor-presensi.edit', $r) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('mentor-presensi.destroy', $r) }}" class="inline"
                                onsubmit="return confirm('Hapus presensi ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-2">Hapus</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400" title="Hanya 24 jam pertama">terkunci</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">Belum ada catatan presensi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">{{ $records->links() }}</div>
    </div>
</div>
@endsection
