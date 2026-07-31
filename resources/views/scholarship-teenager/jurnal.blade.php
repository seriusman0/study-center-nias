@extends('layouts.app')

@section('title', 'Jurnal Harian')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6"
     x-data="stJurnalPage({
        date: '{{ $date->toDateString() }}',
        today: '{{ $today->toDateString() }}',
        csrf: '{{ csrf_token() }}',
        formOpen: {{ $formOpen ? 'true' : 'false' }},
        readOnly: {{ isset($readOnly) && $readOnly ? 'true' : 'false' }},
        state: {
            pl: {{ $entry?->pl_checked ? 'true' : 'false' }},
            pb: {{ $entry?->pb_checked ? 'true' : 'false' }},
            life: {{ json_encode($checkedItemIds) }},
            study: @json($studyState)
        }
     })">

    {{-- Read-only banner --}}
    @if(isset($readOnly) && $readOnly)
    <div class="bg-sc-ink-100 border border-sc-line rounded-xl p-3 mb-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-sc-ink-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <div>
            <div class="font-semibold text-sm text-sc-ink-800">Mode Baca</div>
            <div class="text-xs text-sc-ink-500">Jurnal milik {{ $readOnlyUser->name }}</div>
        </div>
        <a href="{{ route('beranda') }}" class="ml-auto text-xs text-sc-teal-700 font-semibold hover:underline flex-shrink-0">Kembali</a>
    </div>
    @endif

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-sc-teal-700 to-sc-teal-600 text-white shadow-sc-3 rounded-2xl p-5 mb-4 relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-28 opacity-10 pointer-events-none">
            <svg viewBox="0 0 100 100" aria-hidden="true">
                <path d="M30,55 L50,10 L70,55 Z" fill="#e0c020"/>
                <rect x="25" y="60" width="50" height="10" fill="#e0c020"/>
                <rect x="25" y="72" width="50" height="10" fill="#f19121"/>
            </svg>
        </div>
        <div class="flex items-center justify-between gap-2 mb-2 relative">
            <div>
                <h1 class="font-display text-2xl">{{ isset($readOnly) && $readOnly ? $readOnlyUser->name : 'Halo, '.auth()->user()->name }}</h1>
                <p class="text-sm text-white/85">{{ $date->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                @if(($streak ?? 0) > 0)
                <div class="inline-flex items-center gap-1.5 mt-3 bg-sc-orange-100 text-sc-orange-700 border border-sc-orange-300 px-3 py-1 rounded-full text-xs font-bold">
                    🔥 Streak {{ $streak }} hari berturut-turut
                </div>
                @endif
            </div>
            @if(!(isset($readOnly) && $readOnly))
            <div class="flex items-center gap-1">
                <a href="{{ route('scholarship-teenager-jurnal.index', ['date' => $date->copy()->subDay()->toDateString()]) }}"
                   class="px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm">&larr;</a>
                @if($date->lt($today))
                    <a href="{{ route('scholarship-teenager-jurnal.index', ['date' => $date->copy()->addDay()->toDateString()]) }}"
                       class="px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm">&rarr;</a>
                @else
                    <span class="px-3 py-2 rounded-lg bg-white/5 text-white/30 text-sm cursor-not-allowed">&rarr;</span>
                @endif
                @if(!$isToday)
                    <a href="{{ route('scholarship-teenager-jurnal.index') }}" class="ml-2 px-3 py-2 rounded-lg bg-sc-orange-500 text-white font-semibold text-sm hover:bg-sc-orange-600">Hari ini</a>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Form window locked banner --}}
    @if($isToday && !$formOpen && !(isset($readOnly) && $readOnly))
    <div class="bg-sc-orange-50 border border-sc-orange-300 text-sc-orange-800 rounded-xl p-4 mb-4 flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <div class="font-semibold text-sm">Form jurnal belum dibuka</div>
            <div class="text-xs mt-0.5">Pengisian hanya tersedia pukul {{ substr($config->form_open_time, 0, 5) }}–{{ substr($config->form_close_time, 0, 5) }}</div>
        </div>
    </div>
    @endif

    {{-- Section 1: Pembacaan Alkitab --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4 {{ isset($readOnly) && $readOnly ? 'pointer-events-none' : '' }}" :class="!formOpen && '{{ $isToday ? 'opacity-60 pointer-events-none' : '' }}'">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">1</span>
            Pembacaan Alkitab
        </h2>
        @if($bibleItem)
        <p class="text-xs text-sc-ink-500 mb-3 ml-9">Hari ke-{{ $dayNo }} &mdash; <span class="font-medium text-sc-ink-700">{{ $bibleItem->pl_text }}</span> / <span class="font-medium text-sc-ink-700">{{ $bibleItem->pb_text }}</span></p>
        @else
        <p class="text-xs text-sc-ink-400 mb-3 ml-9">Jadwal hari ke-{{ $dayNo }} belum diisi admin.</p>
        @endif

        <div class="grid sm:grid-cols-2 gap-3 mb-3">
            <label class="flex items-start gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition"
                   :class="state.pl && 'border-sc-teal-400 bg-sc-teal-50'">
                <input type="checkbox" class="mt-1 w-5 h-5 accent-sc-teal-600" :checked="state.pl"
                    @change="toggle('pl', null, $event.target.checked)">
                <div>
                    <div class="font-semibold text-sm text-sc-ink-900">Perjanjian Lama</div>
                    @if($bibleItem)
                    <div class="text-xs text-sc-ink-500">{{ $bibleItem->pl_text }}</div>
                    @endif
                </div>
            </label>
            <label class="flex items-start gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition"
                   :class="state.pb && 'border-sc-teal-400 bg-sc-teal-50'">
                <input type="checkbox" class="mt-1 w-5 h-5 accent-sc-teal-600" :checked="state.pb"
                    @change="toggle('pb', null, $event.target.checked)">
                <div>
                    <div class="font-semibold text-sm text-sc-ink-900">Perjanjian Baru</div>
                    @if($bibleItem)
                    <div class="text-xs text-sc-ink-500">{{ $bibleItem->pb_text }}</div>
                    @endif
                </div>
            </label>
        </div>

        {{-- Boolean items in pembacaan (e.g. Upload Alkitab) --}}
        @if(isset($lifeItems['pembacaan']))
            @foreach($lifeItems['pembacaan'] as $item)
                @if(!in_array($item->label, ['Perjanjian Lama', 'Perjanjian Baru']))
                @if($item->response_type === 'boolean')
                <div class="flex items-center justify-between p-3 rounded-lg border border-sc-line mb-2">
                    <span class="text-sm font-medium text-sc-ink-900">{{ $item->label }}</span>
                    <div class="flex gap-2">
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="state.life.includes({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-100'"
                            @click="toggleBoolean({{ $item->id }}, true)">Sudah</button>
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="!state.life.includes({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-400 hover:bg-sc-teal-100'"
                            @click="toggleBoolean({{ $item->id }}, false)">Belum</button>
                    </div>
                </div>
                @else
                <label class="flex items-center gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition mb-2">
                    <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                        :checked="state.life.includes({{ $item->id }})"
                        @change="toggle('life', {{ $item->id }}, $event.target.checked)">
                    <span class="text-sm">{{ $item->label }}</span>
                </label>
                @endif
                @endif
            @endforeach
        @endif
    </div>

    {{-- Section 2: Sidang-Sidang Gereja --}}
    @if(isset($lifeItems['sidang']) && $lifeItems['sidang']->isNotEmpty())
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4 {{ isset($readOnly) && $readOnly ? 'pointer-events-none' : '' }}"
         :class="!formOpen && '{{ $isToday ? 'opacity-60 pointer-events-none' : '' }}'">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">2</span>
            Sidang-Sidang Gereja
        </h2>
        <p class="text-xs text-sc-ink-500 mb-3 ml-9">Opsional &mdash; bisa pilih lebih dari satu</p>
        <div class="space-y-2">
            @foreach($lifeItems['sidang'] as $item)
                @if($item->response_type === 'boolean')
                <div class="flex items-center justify-between p-3 rounded-lg border border-sc-line">
                    <span class="text-sm font-medium text-sc-ink-900">{{ $item->label }}</span>
                    <div class="flex gap-2">
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="state.life.includes({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-100'"
                            @click="toggleBoolean({{ $item->id }}, true)">Sudah</button>
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="!state.life.includes({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-400 hover:bg-sc-teal-100'"
                            @click="toggleBoolean({{ $item->id }}, false)">Belum</button>
                    </div>
                </div>
                @else
                <label class="flex items-center gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                    <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                        :checked="state.life.includes({{ $item->id }})"
                        @change="toggle('life', {{ $item->id }}, $event.target.checked)">
                    <span class="text-sm">{{ $item->label }}</span>
                </label>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Section 3: Rohani & Pelayanan --}}
    @if(isset($lifeItems['rohani']) && $lifeItems['rohani']->isNotEmpty())
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4 {{ isset($readOnly) && $readOnly ? 'pointer-events-none' : '' }}"
         :class="!formOpen && '{{ $isToday ? 'opacity-60 pointer-events-none' : '' }}'">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">3</span>
            Rohani & Pelayanan
        </h2>
        <div class="space-y-2">
            @foreach($lifeItems['rohani'] as $item)
                @if($item->response_type === 'boolean')
                <div class="flex items-center justify-between p-3 rounded-lg border border-sc-line">
                    <span class="text-sm font-medium text-sc-ink-900">{{ $item->label }}</span>
                    <div class="flex gap-2">
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="state.life.includes({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-100'"
                            @click="toggleBoolean({{ $item->id }}, true)">Sudah</button>
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="!state.life.includes({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-400 hover:bg-sc-teal-100'"
                            @click="toggleBoolean({{ $item->id }}, false)">Belum</button>
                    </div>
                </div>

                @elseif($item->response_type === 'time_range')
                <div class="p-3 rounded-lg border border-sc-line" x-data="{
                    itemId: {{ $item->id }},
                    jamMulai: '{{ isset($studyState[$item->id]) ? $studyState[$item->id]['jam_mulai'] : '' }}',
                    jamSelesai: '{{ isset($studyState[$item->id]) ? $studyState[$item->id]['jam_selesai'] : '' }}',
                    tipe: '{{ isset($studyState[$item->id]) ? $studyState[$item->id]['tipe'] : 'mandiri' }}',
                    get totalMenit() {
                        if (!this.jamMulai || !this.jamSelesai) return 0;
                        const [hm, mm] = this.jamMulai.split(':').map(Number);
                        const [hs, ms] = this.jamSelesai.split(':').map(Number);
                        return Math.max(0, (hs * 60 + ms) - (hm * 60 + mm));
                    },
                    get totalLabel() {
                        const t = this.totalMenit;
                        if (t <= 0) return '';
                        const h = Math.floor(t / 60), m = t % 60;
                        return h > 0 ? h + ' jam ' + (m > 0 ? m + ' mnt' : '') : m + ' mnt';
                    },
                    async save(parent) {
                        await parent.saveStudy(this.itemId, this.jamMulai, this.jamSelesai, this.tipe);
                    }
                }">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-sc-ink-900">{{ $item->label }}</span>
                        <span class="text-xs text-sc-teal-600 font-semibold" x-text="totalLabel"></span>
                    </div>
                    <div class="text-xs text-sc-ink-400 mb-2">Di luar jam kuliah &mdash; mandiri atau kelompok</div>
                    <div class="flex flex-wrap gap-2 items-center">
                        <div class="flex items-center gap-1">
                            <label class="text-xs text-sc-ink-500">Mulai</label>
                            <input type="time" x-model="jamMulai" @change="save($root)"
                                class="border border-sc-line rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-400">
                        </div>
                        <div class="flex items-center gap-1">
                            <label class="text-xs text-sc-ink-500">Selesai</label>
                            <input type="time" x-model="jamSelesai" @change="save($root)"
                                class="border border-sc-line rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-sc-teal-400">
                        </div>
                        <div class="flex gap-1">
                            <button type="button" @click="tipe='mandiri'; save($root)"
                                class="px-3 py-1 rounded-lg text-xs font-semibold transition"
                                :class="tipe==='mandiri' ? 'bg-sc-teal-600 text-white' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-50'">Mandiri</button>
                            <button type="button" @click="tipe='kelompok'; save($root)"
                                class="px-3 py-1 rounded-lg text-xs font-semibold transition"
                                :class="tipe==='kelompok' ? 'bg-sc-teal-600 text-white' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-50'">Kelompok</button>
                        </div>
                    </div>
                </div>

                @else
                <label class="flex items-center gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                    <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                        :checked="state.life.includes({{ $item->id }})"
                        @change="toggle('life', {{ $item->id }}, $event.target.checked)">
                    <span class="text-sm">{{ $item->label }}</span>
                </label>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Section 4: Foto Saat Belajar --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4"
         x-data="stFotoBelajar({ date: '{{ $date->toDateString() }}', csrf: '{{ csrf_token() }}', existing: {{ json_encode($fotoUrl) }}, readOnly: {{ isset($readOnly) && $readOnly ? 'true' : 'false' }} })">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">4</span>
            Foto Saat Belajar
            <span class="text-xs font-normal text-sc-ink-400 ml-1">(opsional)</span>
        </h2>

        <div x-show="preview || current" class="mb-3" style="display:none">
            <img :src="preview || current" alt="Foto belajar"
                 class="rounded-xl max-h-72 w-full object-cover border border-sc-line">
        </div>

        <div class="flex flex-wrap gap-2 items-center" x-show="!readOnly">
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
        <p x-show="!readOnly && !current && !preview" class="text-xs text-sc-ink-400 mt-2" style="display:none">Belum ada foto.</p>
        <p x-show="readOnly && !current" class="text-xs text-sc-ink-400 mt-2" style="display:none">Tidak ada foto.</p>
        <p x-show="error" x-text="error" class="text-xs text-red-500 mt-2" style="display:none"></p>
        <p x-show="!readOnly" class="text-xs text-sc-ink-400 mt-2" style="display:none">Format: JPG, PNG, WebP. Maks. 4 MB.</p>
    </div>

    <div x-show="msg" x-transition class="fixed bottom-4 right-4 bg-sc-ink-900 text-white text-sm px-4 py-2 rounded-lg shadow-sc-3"
         x-text="msg" style="display:none"></div>
</div>

@push('scripts')
<script>
function stJurnalPage(cfg) {
    return {
        date: cfg.date,
        today: cfg.today,
        csrf: cfg.csrf,
        formOpen: cfg.formOpen,
        readOnly: cfg.readOnly || false,
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
                const res = await fetch('/jurnal-scholarship-teenager/toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ type, item_id: itemId, date: this.date, checked }),
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menyimpan');
                this.showMsg('Tersimpan');
            } catch (e) {
                this._apply(type, itemId, prev);
                this.showMsg(e.message || 'Gagal menyimpan, coba lagi.');
            }
        },
        toggleBoolean(itemId, val) {
            this.toggle('life', itemId, val === true);
        },
        async saveStudy(itemId, jamMulai, jamSelesai, tipe) {
            try {
                const res = await fetch('/jurnal-scholarship-teenager/toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ type: 'study', item_id: itemId, date: this.date, jam_mulai: jamMulai || null, jam_selesai: jamSelesai || null, tipe }),
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal menyimpan');
                this.state.study[itemId] = { jam_mulai: jamMulai, jam_selesai: jamSelesai, tipe };
                this.showMsg('Tersimpan');
            } catch (e) {
                this.showMsg(e.message || 'Gagal menyimpan, coba lagi.');
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

function stFotoBelajar({ date, csrf, existing, readOnly }) {
    return {
        readOnly: readOnly || false,
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
                const res = await fetch('/jurnal-scholarship-teenager/foto', { method: 'POST', body: form });
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
                const res = await fetch('/jurnal-scholarship-teenager/foto', {
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
