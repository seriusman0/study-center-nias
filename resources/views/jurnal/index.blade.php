@extends('layouts.app')

@section('title', 'Jurnal Harian')

@php
$kitabList = [
    // Perjanjian Lama
    'Kejadian','Keluaran','Imamat','Bilangan','Ulangan',
    'Yosua','Hakim-hakim','Rut','1 Samuel','2 Samuel',
    '1 Raja-raja','2 Raja-raja','1 Tawarikh','2 Tawarikh',
    'Ezra','Nehemia','Ester','Ayub','Mazmur','Amsal',
    'Pengkhotbah','Kidung Agung','Yesaya','Yeremia','Ratapan',
    'Yehezkiel','Daniel','Hosea','Yoel','Amos','Obaja',
    'Yunus','Mikha','Nahum','Habakuk','Zefanya','Hagai',
    'Zakharia','Maleakhi',
    // Perjanjian Baru
    'Matius','Markus','Lukas','Yohanes','Kisah Para Rasul',
    'Roma','1 Korintus','2 Korintus','Galatia','Efesus',
    'Filipi','Kolose','1 Tesalonika','2 Tesalonika',
    '1 Timotius','2 Timotius','Titus','Filemon','Ibrani',
    'Yakobus','1 Petrus','2 Petrus',
    '1 Yohanes','2 Yohanes','3 Yohanes','Yudas','Wahyu',
];
@endphp
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
    @unless(auth()->user()->hasRole('student'))
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
    @endunless

    {{-- Hafal Ayat --}}
    @unless(auth()->user()->hasRole('student'))
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
                <select x-model="kitab" @change="save()"
                    class="border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-400">
                    <option value="">— Pilih kitab —</option>
                    @foreach($kitabList as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
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
    </div>
    @endunless

    {{-- Jadwal Kehidupan --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">{{ auth()->user()->hasRole('student') ? '1' : '3' }}</span>
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
                            @if(auth()->user()->hasRole('student') && $item->label === 'Baca Alkitab')
                                <div class="p-3 rounded-lg border border-sc-line">
                                    <div class="text-sm font-semibold text-sc-ink-700 mb-2">
                                        Baca Alkitab
                                        @if($bibleItem)<span class="text-xs font-normal text-sc-ink-500 ml-1">— Hari ke-{{ $dayNo }}</span>@endif
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-2">
                                        <label class="flex items-start gap-2 p-2 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                                            <input type="checkbox" class="mt-1 w-5 h-5 accent-sc-teal-600" :checked="state.pl" @change="toggle('pl', null, $event.target.checked)">
                                            <div>
                                                <div class="font-semibold text-sm text-sc-ink-900">Perjanjian Lama</div>
                                                @if($bibleItem)<div class="text-sm text-sc-ink-700">{{ $bibleItem->pl_text ?: '—' }}</div>@endif
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-2 p-2 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                                            <input type="checkbox" class="mt-1 w-5 h-5 accent-sc-teal-600" :checked="state.pb" @change="toggle('pb', null, $event.target.checked)">
                                            <div>
                                                <div class="font-semibold text-sm text-sc-ink-900">Perjanjian Baru</div>
                                                @if($bibleItem)<div class="text-sm text-sc-ink-700">{{ $bibleItem->pb_text ?: '—' }}</div>@endif
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @elseif(auth()->user()->hasRole('student') && $item->label === 'Hafal Ayat')
                                <div class="p-3 rounded-lg border border-sc-line" x-data="hafalAyat(jurnalPage_cfg)">
                                    <div class="text-sm font-semibold text-sc-ink-700 mb-2">Hafal Ayat</div>
                                    @if($bibleItem)
                                        <p class="text-xs text-sc-ink-500 mb-2">
                                            Dari porsi hari ini: <span class="font-medium text-sc-teal-700">{{ implode(' / ', array_filter([$bibleItem->pl_text, $bibleItem->pb_text])) }}</span>
                                        </p>
                                    @else
                                        <p class="text-xs text-sc-ink-500 mb-2">Pilih satu ayat dari porsi bacaan hari ini.</p>
                                    @endif
                                    <div class="flex flex-wrap gap-2 items-end">
                                        <div class="flex flex-col gap-1">
                                            <label class="text-xs text-sc-ink-500 font-medium">Kitab</label>
                                            <select x-model="kitab" @change="save()"
                                                class="border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-400">
                                                <option value="">— Pilih kitab —</option>
                                                @foreach($kitabList as $k)
                                                    <option value="{{ $k }}">{{ $k }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="text-xs text-sc-ink-500 font-medium">Pasal</label>
                                            <input type="number" x-model="pasal" min="1" max="150" placeholder="1" @blur="save()"
                                                class="border border-sc-line rounded-lg px-3 py-2 text-sm w-20 focus:outline-none focus:ring-2 focus:ring-sc-teal-400">
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="text-xs text-sc-ink-500 font-medium">Ayat</label>
                                            <input type="number" x-model="ayat" min="1" max="200" placeholder="1" @blur="save()"
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
                                </div>
                            @else
                                <label class="flex items-center gap-3 p-2 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                                    <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                                        :checked="state.life.includes({{ $item->id }})"
                                        @change="toggle('life', {{ $item->id }}, $event.target.checked)">
                                    <span class="text-sm">{{ $item->label }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Foto Saat Belajar --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4"
         x-data="fotoBelajar({ date: '{{ $date->toDateString() }}', csrf: '{{ csrf_token() }}', existing: {{ $entry?->foto_belajar ? json_encode(asset('storage/' . $entry->foto_belajar)) : 'null' }} })">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">
                {{ auth()->user()->hasRole('student') ? '2' : '4' }}
            </span>
            Foto Saat Belajar
            <span class="text-xs font-normal text-sc-ink-400 ml-1">(opsional)</span>
        </h2>

        {{-- Preview area --}}
        <div x-show="preview || current" class="mb-3" style="display:none">
            <img :src="preview || current" alt="Foto belajar"
                 class="rounded-xl max-h-72 w-full object-cover border border-sc-line">
        </div>

        <div class="flex flex-wrap gap-2 items-center">
            <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-sc-teal-700 text-white text-sm font-semibold hover:bg-sc-teal-800 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span x-text="current || preview ? 'Ganti Foto' : 'Upload Foto'"></span>
                <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden" @change="onFile($event)">
            </label>
            <button type="button" x-show="preview" @click="upload()"
                    :disabled="uploading"
                    class="px-4 py-2 rounded-lg bg-sc-orange-500 text-white text-sm font-semibold hover:bg-sc-orange-600 transition disabled:opacity-50">
                <span x-text="uploading ? 'Menyimpan...' : 'Simpan Foto'"></span>
            </button>
            <button type="button" x-show="current && !preview" @click="remove()"
                    class="px-4 py-2 rounded-lg bg-sc-ink-100 text-sc-ink-600 text-sm hover:bg-sc-ink-200 transition">
                Hapus Foto
            </button>
            <button type="button" x-show="preview" @click="cancelPreview()"
                    class="px-4 py-2 rounded-lg bg-sc-ink-100 text-sc-ink-500 text-sm hover:bg-sc-ink-200 transition">
                Batal
            </button>
        </div>
        <p x-show="error" x-text="error" class="text-xs text-red-500 mt-2" style="display:none"></p>
        <p class="text-xs text-sc-ink-400 mt-2">Format: JPG, PNG, WebP. Maks. 4 MB.</p>
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
                const res = await fetch('/jurnal/toggle', {
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
                const res = await fetch('/jurnal/toggle', {
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

function fotoBelajar({ date, csrf, existing }) {
    return {
        current: existing,
        preview: null,
        file: null,
        uploading: false,
        error: '',
        onFile(e) {
            const f = e.target.files[0];
            if (!f) return;
            const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowed.includes(f.type)) {
                this.error = 'Format tidak didukung. Gunakan JPG, PNG, atau WebP.';
                return;
            }
            if (f.size > 4 * 1024 * 1024) {
                this.error = 'Ukuran file melebihi 4 MB.';
                return;
            }
            this.error = '';
            this.file = f;
            const reader = new FileReader();
            reader.onload = (ev) => { this.preview = ev.target.result; };
            reader.readAsDataURL(f);
        },
        cancelPreview() {
            this.preview = null;
            this.file = null;
            this.error = '';
        },
        async upload() {
            if (!this.file || this.uploading) return;
            this.uploading = true;
            this.error = '';
            try {
                const form = new FormData();
                form.append('foto', this.file);
                form.append('date', date);
                form.append('_token', csrf);
                const res = await fetch('/jurnal/foto', { method: 'POST', body: form });
                const json = await res.json();
                if (!res.ok || !json.ok) throw new Error(json.message || 'Gagal upload');
                this.current = json.url;
                this.preview = null;
                this.file = null;
            } catch (e) {
                this.error = e.message || 'Gagal upload, coba lagi.';
            } finally {
                this.uploading = false;
            }
        },
        async remove() {
            if (!confirm('Hapus foto ini?')) return;
            try {
                const res = await fetch('/jurnal/foto', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ date }),
                });
                if (!res.ok) throw new Error();
                this.current = null;
            } catch {
                alert('Gagal menghapus foto.');
            }
        },
    };
}
</script>
@endpush
@endsection
