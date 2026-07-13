@extends('layouts.app')

@section('title', 'Jurnal Harian')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6"
     x-data="jurnalPage({
        date: '{{ $date->toDateString() }}',
        today: '{{ $today->toDateString() }}',
        csrf: '{{ csrf_token() }}',
        weekKey: '{{ $weekKey }}',
        verseRef: {{ $verseRef ? json_encode($verseRef) : 'null' }},
        state: {
            pl: {{ $entry?->pl_checked ? 'true' : 'false' }},
            pb: {{ $entry?->pb_checked ? 'true' : 'false' }},
            life: {{ json_encode($checkedItemIds) }}
        }
     })">

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-sc-teal-700 to-sc-teal-600 text-white shadow-sc-3 rounded-2xl p-5 mb-4 relative overflow-hidden">
        <svg viewBox="0 0 100 100" class="absolute -right-4 -top-4 w-32 opacity-10 pointer-events-none" aria-hidden="true">
            <path d="M30,55 L50,10 L70,55 Z" fill="#e0c020"/>
            <rect x="25" y="60" width="50" height="10" fill="#e0c020"/>
            <rect x="25" y="72" width="50" height="10" fill="#f19121"/>
        </svg>
        <div class="flex items-center justify-between gap-2 mb-2 relative">
            <div>
                <h1 class="font-display text-2xl">Halo, {{ auth()->user()->name }} 👋</h1>
                <p class="text-sm text-white/85">{{ $date->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                @if(($streak ?? 0) > 0)
                <div class="inline-flex items-center gap-1.5 mt-3 bg-sc-orange-100 text-sc-orange-700 border border-sc-orange-300 px-3 py-1 rounded-full text-xs font-bold">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                    Streak {{ $streak }} hari berturut-turut
                </div>
                @endif
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('jurnal.index', ['date' => $date->copy()->subDay()->toDateString()]) }}"
                   class="px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm" title="Hari sebelumnya">&larr;</a>
                @if($date->lt($today))
                    <a href="{{ route('jurnal.index', ['date' => $date->copy()->addDay()->toDateString()]) }}"
                       class="px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm" title="Hari berikutnya">&rarr;</a>
                @else
                    <span class="px-3 py-2 rounded-lg bg-white/5 text-white/30 text-sm cursor-not-allowed">&rarr;</span>
                @endif
                @if(!$isToday)
                    <a href="{{ route('jurnal.index') }}" class="ml-2 px-3 py-2 rounded-lg bg-sc-orange-500 text-white font-semibold text-sm hover:bg-sc-orange-600">Hari ini</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Pembacaan Alkitab --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">1</span>
            Pembacaan Alkitab
        </h2>
        @if($bibleItem)
            <p class="text-xs text-sc-ink-500 mb-3 ml-9">Hari ke-{{ $dayNo }}</p>
            <div class="grid sm:grid-cols-2 gap-3">
                <label class="flex items-start gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                    <input type="checkbox" class="mt-1 w-5 h-5 accent-sc-teal-600" :checked="state.pl" @change="toggle('pl', null, $event.target.checked)">
                    <div>
                        <div class="font-semibold text-sm text-sc-ink-900">Perjanjian Lama</div>
                        <div class="text-sm text-sc-ink-700">{{ $bibleItem->pl_text ?: '—' }}</div>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                    <input type="checkbox" class="mt-1 w-5 h-5 accent-sc-teal-600" :checked="state.pb" @change="toggle('pb', null, $event.target.checked)">
                    <div>
                        <div class="font-semibold text-sm text-sc-ink-900">Perjanjian Baru</div>
                        <div class="text-sm text-sc-ink-700">{{ $bibleItem->pb_text ?: '—' }}</div>
                    </div>
                </label>
            </div>
        @else
            <p class="text-sm text-sc-ink-500 italic mb-3 ml-9">Porsi Alkitab belum tersedia untuk tanggal ini.</p>
            <div class="grid sm:grid-cols-2 gap-3">
                <label class="flex items-center gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                    <input type="checkbox" class="w-5 h-5 accent-sc-teal-600" :checked="state.pl" @change="toggle('pl', null, $event.target.checked)">
                    <span class="text-sm font-semibold">Perjanjian Lama</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                    <input type="checkbox" class="w-5 h-5 accent-sc-teal-600" :checked="state.pb" @change="toggle('pb', null, $event.target.checked)">
                    <span class="text-sm font-semibold">Perjanjian Baru</span>
                </label>
            </div>
        @endif
    </div>

    {{-- Hafal Ayat --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4"
         x-data="hafalAyat(jurnalPage_cfg)">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">2</span>
            Hafal Ayat Mingguan
        </h2>
        @if($bibleItem)
            <p class="text-xs text-sc-ink-500 mb-3 ml-9">
                Dari porsi hari ini: <span class="font-medium text-sc-teal-700">{{ implode(' / ', array_filter([$bibleItem->pl_text, $bibleItem->pb_text])) }}</span>
            </p>
        @else
            <p class="text-xs text-sc-ink-500 mb-3 ml-9">Pilih satu ayat dari porsi bacaan hari ini.</p>
        @endif
        <div class="flex flex-wrap gap-2 items-end">
            <div class="flex flex-col gap-1">
                <label class="text-xs text-sc-ink-500 font-medium">Kitab</label>
                <input type="text" x-model="kitab" list="kitab-list"
                    placeholder="mis. Ezra"
                    @blur="save()"
                    class="border border-sc-line rounded-lg px-3 py-2 text-sm w-36 focus:outline-none focus:ring-2 focus:ring-sc-teal-400">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs text-sc-ink-500 font-medium">Pasal</label>
                <input type="number" x-model="pasal" min="1" max="150" placeholder="1"
                    @blur="save()"
                    class="border border-sc-line rounded-lg px-3 py-2 text-sm w-20 focus:outline-none focus:ring-2 focus:ring-sc-teal-400">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs text-sc-ink-500 font-medium">Ayat</label>
                <input type="number" x-model="ayat" min="1" max="200" placeholder="1"
                    @blur="save()"
                    class="border border-sc-line rounded-lg px-3 py-2 text-sm w-20 focus:outline-none focus:ring-2 focus:ring-sc-teal-400">
            </div>
            <button type="button" x-show="kitab || pasal || ayat" @click="clear()"
                class="px-3 py-2 rounded-lg bg-sc-ink-100 text-sc-ink-500 text-sm hover:bg-sc-ink-200 transition">
                Hapus
            </button>
        </div>
        <p x-show="saved" x-transition class="text-xs text-sc-teal-600 mt-2 font-medium" style="display:none">
            Tersimpan: <span x-text="saved"></span>
        </p>
        <datalist id="kitab-list">
            <option value="Kejadian"><option value="Keluaran"><option value="Imamat"><option value="Bilangan"><option value="Ulangan">
            <option value="Yosua"><option value="Hakim-hakim"><option value="Rut"><option value="1 Samuel"><option value="2 Samuel">
            <option value="1 Raja-raja"><option value="2 Raja-raja"><option value="1 Tawarikh"><option value="2 Tawarikh"><option value="Ezra">
            <option value="Nehemia"><option value="Ester"><option value="Ayub"><option value="Mazmur"><option value="Amsal">
            <option value="Pengkhotbah"><option value="Kidung Agung"><option value="Yesaya"><option value="Yeremia"><option value="Ratapan">
            <option value="Yehezkiel"><option value="Daniel"><option value="Hosea"><option value="Yoel"><option value="Amos">
            <option value="Obaja"><option value="Yunus"><option value="Mikha"><option value="Nahum"><option value="Habakuk">
            <option value="Zefanya"><option value="Hagai"><option value="Zakharia"><option value="Maleakhi">
            <option value="Matius"><option value="Markus"><option value="Lukas"><option value="Yohanes"><option value="Kisah">
            <option value="Roma"><option value="1 Korintus"><option value="2 Korintus"><option value="Galatia"><option value="Efesus">
            <option value="Filipi"><option value="Kolose"><option value="1 Tesalonika"><option value="2 Tesalonika">
            <option value="1 Timotius"><option value="2 Timotius"><option value="Titus"><option value="Filemon">
            <option value="Ibrani"><option value="Yakobus"><option value="1 Petrus"><option value="2 Petrus">
            <option value="1 Yohanes"><option value="2 Yohanes"><option value="3 Yohanes"><option value="Yudas"><option value="Wahyu">
        </datalist>
    </div>

    {{-- Jadwal Kehidupan --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">3</span>
            Jadwal Kehidupan
        </h2>

        @php
            $kategoriList = [
                'kerohanian' => 'Kerohanian',
                'pendidikan' => 'Pendidikan',
                'karakter'   => 'Karakter',
            ];
        @endphp

        @foreach($kategoriList as $kKey => $kLabel)
            <div class="mb-4">
                <h3 class="text-xs font-bold text-sc-teal-700 uppercase tracking-wider mb-2">{{ $kLabel }}</h3>
                @if(($lifeItems[$kKey] ?? collect())->isEmpty())
                    <p class="text-sm text-sc-ink-500 italic pl-2">Belum ada item.</p>
                @else
                    <div class="space-y-2">
                        @foreach($lifeItems[$kKey] as $item)
                            <label class="flex items-center gap-3 p-2 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                                <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
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

    <div x-show="msg" x-transition class="fixed bottom-4 right-4 bg-sc-ink-900 text-white text-sm px-4 py-2 rounded-lg shadow-sc-3"
         x-text="msg" style="display:none"></div>
</div>

@push('scripts')
<script>
let jurnalPage_cfg = null;

function jurnalPage(cfg) {
    jurnalPage_cfg = cfg;
    return {
        date: cfg.date,
        today: cfg.today,
        csrf: cfg.csrf,
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

function hafalAyat(cfg) {
    function parse(ref) {
        if (!ref) return { kitab: '', pasal: '', ayat: '' };
        // "Ezra 9:3" → kitab=Ezra, pasal=9, ayat=3
        const m = ref.match(/^(.+?)\s+(\d+):(\d+)$/);
        if (m) return { kitab: m[1], pasal: m[2], ayat: m[3] };
        return { kitab: ref, pasal: '', ayat: '' };
    }
    const parsed = parse(cfg ? cfg.verseRef : null);
    return {
        kitab: parsed.kitab,
        pasal: parsed.pasal,
        ayat:  parsed.ayat,
        saved: cfg && cfg.verseRef ? cfg.verseRef : '',
        async save() {
            if (!this.kitab || !this.pasal || !this.ayat) return;
            const ref = `${this.kitab} ${this.pasal}:${this.ayat}`;
            await this._post(ref);
        },
        async clear() {
            this.kitab = ''; this.pasal = ''; this.ayat = '';
            await this._post(null);
        },
        async _post(verseRef) {
            if (!cfg) return;
            try {
                const res = await fetch('{{ route('jurnal.toggle') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ type: 'verse', date: cfg.date, verse_ref: verseRef }),
                });
                if (!res.ok) throw new Error();
                this.saved = verseRef || '';
            } catch {
                // silent fail — user can retry
            }
        },
    };
}
</script>
@endpush
@endsection
