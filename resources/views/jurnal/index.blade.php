@extends('layouts.app')

@section('title', 'Jurnal Harian')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6"
     x-data="jurnalPage({
        date: '{{ $date->toDateString() }}',
        today: '{{ $today->toDateString() }}',
        csrf: '{{ csrf_token() }}',
        verseKey: '{{ $weekMeta['key'] }}',
        state: {
            pl: {{ $entry?->pl_checked ? 'true' : 'false' }},
            pb: {{ $entry?->pb_checked ? 'true' : 'false' }},
            verse: {{ $verseChecked ? 'true' : 'false' }},
            life: {{ json_encode($checkedItemIds) }}
        }
     })">

    <div class="bg-gradient-to-br from-[#1e3a5f] to-[#2d5282] text-white shadow rounded-xl p-5 mb-4">
        <div class="flex items-center justify-between gap-2 mb-2">
            <div>
                <h1 class="text-xl font-bold">Halo, {{ auth()->user()->name }} 👋</h1>
                <p class="text-sm text-white/80">{{ $date->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                @if(($streak ?? 0) > 0)
                <p class="text-xs mt-2 text-[#c9a84c] font-semibold">🔥 Streak: {{ $streak }} hari berturut-turut</p>
                @endif
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('jurnal.index', ['date' => $date->copy()->subDay()->toDateString()]) }}"
                   class="px-3 py-2 rounded bg-white/15 hover:bg-white/25 text-sm" title="Hari sebelumnya">&larr;</a>
                @if($date->lt($today))
                    <a href="{{ route('jurnal.index', ['date' => $date->copy()->addDay()->toDateString()]) }}"
                       class="px-3 py-2 rounded bg-white/15 hover:bg-white/25 text-sm" title="Hari berikutnya">&rarr;</a>
                @else
                    <span class="px-3 py-2 rounded bg-white/5 text-white/30 text-sm cursor-not-allowed">&rarr;</span>
                @endif
                @if(!$isToday)
                    <a href="{{ route('jurnal.index') }}" class="ml-2 px-3 py-2 rounded bg-[#c9a84c] text-[#1e3a5f] font-semibold text-sm">Hari ini</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Pembacaan Alkitab --}}
    <div class="bg-white shadow rounded-xl p-5 mb-4">
        <h2 class="text-lg font-bold text-[#1e3a5f] mb-3">1. Pembacaan Alkitab</h2>
        @if($schedule)
            <div class="grid sm:grid-cols-2 gap-3">
                <label class="flex items-start gap-3 p-3 rounded-lg border hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" class="mt-1 w-5 h-5" :checked="state.pl" @change="toggle('pl', null, $event.target.checked)">
                    <div>
                        <div class="font-semibold text-sm text-gray-800">Perjanjian Lama</div>
                        <div class="text-sm text-gray-600">{{ $schedule->pl_porsi ?: '—' }}</div>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-lg border hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" class="mt-1 w-5 h-5" :checked="state.pb" @change="toggle('pb', null, $event.target.checked)">
                    <div>
                        <div class="font-semibold text-sm text-gray-800">Perjanjian Baru</div>
                        <div class="text-sm text-gray-600">{{ $schedule->pb_porsi ?: '—' }}</div>
                    </div>
                </label>
            </div>
        @else
            <p class="text-sm text-gray-500 italic">Porsi Alkitab belum tersedia untuk tanggal ini.</p>
            <div class="grid sm:grid-cols-2 gap-3 mt-3">
                <label class="flex items-center gap-3 p-3 rounded-lg border hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" class="w-5 h-5" :checked="state.pl" @change="toggle('pl', null, $event.target.checked)">
                    <span class="text-sm font-semibold">Perjanjian Lama</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-lg border hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" class="w-5 h-5" :checked="state.pb" @change="toggle('pb', null, $event.target.checked)">
                    <span class="text-sm font-semibold">Perjanjian Baru</span>
                </label>
            </div>
        @endif
    </div>

    {{-- Hafal Ayat --}}
    <div class="bg-white shadow rounded-xl p-5 mb-4">
        <h2 class="text-lg font-bold text-[#1e3a5f] mb-1">2. Hafal Ayat Mingguan</h2>
        <p class="text-xs text-gray-500 mb-3">Minggu ke-{{ $weekMeta['minggu'] }} bulan {{ $date->locale('id')->isoFormat('MMMM Y') }}</p>
        @if($verse)
            <div class="p-3 rounded-lg bg-yellow-50 border border-yellow-200 mb-3">
                <div class="font-bold text-[#1e3a5f]">{{ $verse->referensi }}</div>
                <div class="text-sm text-gray-700 mt-1 whitespace-pre-line">{{ $verse->isi }}</div>
            </div>
            <label class="flex items-center gap-3 p-3 rounded-lg border hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" class="w-5 h-5" :checked="state.verse" @change="toggle('verse', null, $event.target.checked)">
                <span class="text-sm font-semibold">Sudah hafal ayat minggu ini</span>
            </label>
        @else
            <p class="text-sm text-gray-500 italic">Ayat hafalan belum ditetapkan untuk minggu ini.</p>
        @endif
    </div>

    {{-- Jadwal Kehidupan --}}
    <div class="bg-white shadow rounded-xl p-5 mb-4">
        <h2 class="text-lg font-bold text-[#1e3a5f] mb-3">3. Jadwal Kehidupan</h2>

        @php
            $kategoriList = [
                'kerohanian' => 'Kerohanian',
                'pendidikan' => 'Pendidikan',
                'karakter'   => 'Karakter',
            ];
        @endphp

        @foreach($kategoriList as $kKey => $kLabel)
            <div class="mb-4">
                <h3 class="text-sm font-bold text-gray-600 uppercase tracking-wide mb-2">{{ $kLabel }}</h3>
                @if(($lifeItems[$kKey] ?? collect())->isEmpty())
                    <p class="text-sm text-gray-400 italic pl-2">Belum ada item.</p>
                @else
                    <div class="space-y-2">
                        @foreach($lifeItems[$kKey] as $item)
                            <label class="flex items-center gap-3 p-2 rounded border hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="w-5 h-5"
                                    :checked="state.life.includes({{ $item->id }})"
                                    @change="toggle('life', {{ $item->id }}, $event.target.checked)">
                                <span class="text-sm">{{ $item->label }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div x-show="msg" x-transition class="fixed bottom-4 right-4 bg-gray-800 text-white text-sm px-4 py-2 rounded-lg shadow"
         x-text="msg" style="display:none"></div>
</div>

@push('scripts')
<script>
function jurnalPage(cfg) {
    return {
        date: cfg.date,
        today: cfg.today,
        csrf: cfg.csrf,
        verseKey: cfg.verseKey,
        state: cfg.state,
        msg: '',
        showMsg(m) {
            this.msg = m;
            clearTimeout(this._t);
            this._t = setTimeout(() => this.msg = '', 2200);
        },
        async toggle(type, itemId, checked) {
            const prev = this._snap(type, itemId);
            this._apply(type, itemId, checked);
            try {
                const res = await fetch('{{ route('jurnal.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ type, item_id: itemId, date: this.date, checked }),
                });
                if (!res.ok) throw new Error('Gagal menyimpan');
                this.showMsg('Tersimpan');
            } catch (e) {
                this._apply(type, itemId, prev);
                this.showMsg('Gagal menyimpan, coba lagi.');
            }
        },
        _snap(type, itemId) {
            if (type === 'life') return this.state.life.includes(itemId);
            return this.state[type];
        },
        _apply(type, itemId, checked) {
            if (type === 'life') {
                const has = this.state.life.includes(itemId);
                if (checked && !has) this.state.life.push(itemId);
                if (!checked && has) this.state.life = this.state.life.filter(x => x !== itemId);
            } else {
                this.state[type] = checked;
            }
        },
    }
}
</script>
@endpush
@endsection
