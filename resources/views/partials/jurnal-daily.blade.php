{{--
  Shared daily journal partial for College and Scholarship Teenager.
  Required variables (passed from controller via HasJurnalDailyActions):
    $routePrefix    — e.g. 'college-jurnal' or 'scholarship-teenager-jurnal'
    $date, $today, $isToday, $formOpen, $config, $dayNo, $bibleItem
    $entry, $lifeItems, $checkedItemIds, $studyLogs, $studyState
    $streak, $fotoUrl
    $readOnly (optional bool), $readOnlyUser (optional User)
--}}

@php
$kitabList = [
    'Kejadian','Keluaran','Imamat','Bilangan','Ulangan','Yosua','Hakim-hakim','Rut','1 Samuel','2 Samuel',
    '1 Raja-raja','2 Raja-raja','1 Tawarikh','2 Tawarikh','Ezra','Nehemia','Ester','Ayub','Mazmur','Amsal',
    'Pengkhotbah','Kidung Agung','Yesaya','Yeremia','Ratapan','Yehezkiel','Daniel','Hosea','Yoel','Amos','Obaja',
    'Yunus','Mikha','Nahum','Habakuk','Zefanya','Hagai','Zakharia','Maleakhi',
    'Matius','Markus','Lukas','Yohanes','Kisah Para Rasul','Roma','1 Korintus','2 Korintus','Galatia','Efesus',
    'Filipi','Kolose','1 Tesalonika','2 Tesalonika','1 Timotius','2 Timotius','Titus','Filemon','Ibrani',
    'Yakobus','1 Petrus','2 Petrus','1 Yohanes','2 Yohanes','3 Yohanes','Yudas','Wahyu'
];
@endphp
@php
    $jsFnPrefix = \Illuminate\Support\Str::camel($routePrefix);
    $isReadOnly = isset($readOnly) && $readOnly;
    // Derive JS function prefix from routePrefix for unique Alpine fn names per page
    $jsFnPrefix = \Illuminate\Support\Str::camel($routePrefix);
@endphp

<div class="max-w-3xl mx-auto px-4 py-6"
     x-data="{{ $jsFnPrefix }}Page({
        date: '{{ $date->toDateString() }}',
        today: '{{ $today->toDateString() }}',
        csrf: '{{ csrf_token() }}',
        formOpen: {{ $formOpen ? 'true' : 'false' }},
        readOnly: {{ $isReadOnly ? 'true' : 'false' }},
        toggleUrl: '{{ route($routePrefix . '.toggle', [], false) }}',
        fotoUrl: '{{ route($routePrefix . '.foto.upload', [], false) }}',
        verseRef: '{{ addslashes($verseRef ?? '') }}',
        state: {
            pl: {{ $entry?->pl_checked ? 'true' : 'false' }},
            pb: {{ $entry?->pb_checked ? 'true' : 'false' }},
            verse_check: {{ $verseChecked ? 'true' : 'false' }},
            life: {{ json_encode($checkedItemIds) }}.map(Number),
            study: @json($studyState)
        },
        lifeValues: @json($checkedValues ?? new stdClass())
     })">

    {{-- Read-only banner --}}
    @if($isReadOnly)
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
                <h1 class="font-display text-2xl">{{ $isReadOnly ? $readOnlyUser->name : 'Halo, '.auth()->user()->name }}</h1>
                <p class="text-sm text-white/85">{{ $date->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                @if(($streak ?? 0) > 0)
                <div class="inline-flex items-center gap-1.5 mt-3 bg-sc-orange-100 text-sc-orange-700 border border-sc-orange-300 px-3 py-1 rounded-full text-xs font-bold">
                    🔥 Streak {{ $streak }} hari berturut-turut
                </div>
                @endif
            </div>
            @if(!$isReadOnly)
            <div class="flex items-center gap-1">
                <a href="{{ route($routePrefix . '.index', ['date' => $date->copy()->subDay()->toDateString()]) }}"
                   class="px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm">&larr;</a>
                @if($date->lt($today))
                    <a href="{{ route($routePrefix . '.index', ['date' => $date->copy()->addDay()->toDateString()]) }}"
                       class="px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm">&rarr;</a>
                @else
                    <span class="px-3 py-2 rounded-lg bg-white/5 text-white/30 text-sm cursor-not-allowed">&rarr;</span>
                @endif
                @if(!$isToday)
                    <a href="{{ route($routePrefix . '.index') }}" class="ml-2 px-3 py-2 rounded-lg bg-sc-orange-500 text-white font-semibold text-sm hover:bg-sc-orange-600">Hari ini</a>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Form window locked banner --}}
    @if($isToday && !$formOpen && !$isReadOnly)
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
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4 {{ $isReadOnly ? 'pointer-events-none' : '' }}" :class="!formOpen && '{{ $isToday ? 'opacity-60 pointer-events-none' : '' }}'">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">
            @php $secNo = 1; @endphp
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">{{ $secNo++ }}</span>
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
                <input type="checkbox" class="mt-1 w-5 h-5 accent-sc-teal-600" x-model="state.pl"
                    @change="toggle('pl', null, state.pl)">
                <div>
                    <div class="font-semibold text-sm text-sc-ink-900">Perjanjian Lama</div>
                    @if($bibleItem)
                    <div class="text-xs text-sc-ink-500">{{ $bibleItem->pl_text }}</div>
                    @endif
                </div>
            </label>
            <label class="flex items-start gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition"
                   :class="state.pb && 'border-sc-teal-400 bg-sc-teal-50'">
                <input type="checkbox" class="mt-1 w-5 h-5 accent-sc-teal-600" x-model="state.pb"
                    @change="toggle('pb', null, state.pb)">
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
                            :class="hasLife({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-100'"
                            @click="toggleLife({{ $item->id }}, true)">Sudah</button>
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="!hasLife({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-400 hover:bg-sc-teal-100'"
                            @click="toggleLife({{ $item->id }}, false)">Belum</button>
                    </div>
                </div>
                @else
                <label class="flex items-center gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition mb-2">
                    <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                        :value="{{ $item->id }}" x-model="state.life"
                        @change="toggle('life', {{ $item->id }}, hasLife({{ $item->id }}))">
                    <span class="text-sm">{{ $item->label }}</span>
                </label>
                @endif
                @endif
            @endforeach
        @endif
    </div>

    @if($showVerse ?? true)
    {{-- Hafal Ayat --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4 {{ $isReadOnly ? 'pointer-events-none' : '' }}"
         :class="!formOpen && '{{ $isToday ? 'opacity-60 pointer-events-none' : '' }}'"
         x-data="{{ $jsFnPrefix }}HafalAyat({{ $jsFnPrefix }}Page_cfg)">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">{{ $secNo++ }}</span>
            Hafal Ayat Mingguan
        </h2>
        @if($bibleItem)
            <p class="text-xs text-sc-ink-500 mb-3 ml-9">
                Dari porsi hari ini: <span class="font-medium text-sc-teal-700">{{ implode(' / ', array_filter([$bibleItem->pl_text, $bibleItem->pb_text])) }}</span>
            </p>
        @else
            <p class="text-xs text-sc-ink-500 mb-3 ml-9">Pilih satu ayat dari porsi bacaan hari ini.</p>
        @endif
        <div class="flex flex-wrap gap-2 items-end ml-9">
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
            @unless($isReadOnly)
            <button type="button" x-show="kitab || pasal || ayat" @click="clear()"
                class="px-3 py-2 rounded-lg bg-sc-ink-100 text-sc-ink-500 text-sm hover:bg-sc-ink-200 transition">
                Hapus
            </button>
            @endunless
        </div>
        <div class="ml-9">
            <p x-show="saved" x-transition class="text-xs text-sc-teal-600 mt-2 font-medium" style="display:none">
                Tersimpan: <span x-text="saved"></span>
            </p>
            <label class="flex items-center gap-3 mt-3 p-2 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                <input type="checkbox" class="w-5 h-5 accent-sc-teal-600" x-model="state.verse_check" @change="toggle('verse_check', null, state.verse_check)">
                <span class="text-sm font-medium">Sudah hafal ayat ini</span>
            </label>
        </div>
    </div>
    @endif
    {{-- Section 3: Sidang-Sidang Gereja --}}
    @if(isset($lifeItems['sidang']) && $lifeItems['sidang']->isNotEmpty())
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4 {{ $isReadOnly ? 'pointer-events-none' : '' }}"
         :class="!formOpen && '{{ $isToday ? 'opacity-60 pointer-events-none' : '' }}'">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">{{ $secNo++ }}</span>
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
                            :class="hasLife({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-100'"
                            @click="toggleLife({{ $item->id }}, true)">Sudah</button>
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="!hasLife({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-400 hover:bg-sc-teal-100'"
                            @click="toggleLife({{ $item->id }}, false)">Belum</button>
                    </div>
                </div>
                @else
                <label class="flex items-center gap-3 p-3 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                    <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                        :value="{{ $item->id }}" x-model="state.life"
                        @change="toggle('life', {{ $item->id }}, hasLife({{ $item->id }}))">
                    <span class="text-sm">{{ $item->label }}</span>
                </label>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Section 4: Rohani & Pelayanan --}}
    @if(isset($lifeItems['rohani']) && $lifeItems['rohani']->isNotEmpty())
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4 {{ $isReadOnly ? 'pointer-events-none' : '' }}"
         :class="!formOpen && '{{ $isToday ? 'opacity-60 pointer-events-none' : '' }}'">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">{{ $secNo++ }}</span>
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
                            :class="hasLife({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-100'"
                            @click="toggleLife({{ $item->id }}, true)">Sudah</button>
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="!hasLife({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-400 hover:bg-sc-teal-100'"
                            @click="toggleLife({{ $item->id }}, false)">Belum</button>
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
                        :value="{{ $item->id }}" x-model="state.life"
                        @change="toggle('life', {{ $item->id }}, hasLife({{ $item->id }}))">
                    <span class="text-sm">{{ $item->label }}</span>
                </label>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Section Prajurit --}}
    @if(isset($lifeItems['prajurit']) && $lifeItems['prajurit']->isNotEmpty())
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4 {{ $isReadOnly ? 'pointer-events-none' : '' }}"
         :class="!formOpen && '{{ $isToday ? 'opacity-60 pointer-events-none' : '' }}'">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">{{ $secNo++ }}</span>
            Jurnal Prajurit
        </h2>
        <div class="space-y-2">
            @foreach($lifeItems['prajurit'] as $item)
                @if($item->response_type === 'boolean')
                <div class="flex items-center justify-between p-3 rounded-lg border border-sc-line">
                    <span class="text-sm font-medium text-sc-ink-900">{{ $item->label }}</span>
                    <div class="flex gap-2">
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="hasLife({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-600 hover:bg-sc-teal-100'"
                            @click="toggleLife({{ $item->id }}, true)">Sudah</button>
                        <button type="button"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold transition"
                            :class="!hasLife({{ $item->id }}) ? 'bg-sc-teal-600 text-white shadow-sc-focus' : 'bg-sc-ink-100 text-sc-ink-400 hover:bg-sc-teal-100'"
                            @click="toggleLife({{ $item->id }}, false)">Belum</button>
                    </div>
                </div>
                @elseif($item->response_type === 'number')
                <div class="flex items-center justify-between p-3 rounded-lg border border-sc-line">
                    <span class="text-sm font-medium text-sc-ink-900">{{ $item->label }}</span>
                    <input type="number" 
                        class="w-24 text-center border-sc-line rounded-lg text-sm py-1.5 focus:ring-sc-teal-500 focus:border-sc-teal-500"
                        placeholder="0"
                        :value="lifeValues[{{ $item->id }}] ?? ''"
                        @change="saveLifeValue({{ $item->id }}, $event.target.value)"
                    />
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif


    {{-- Section 5: Foto Saat Belajar --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4"
         x-data="{{ $jsFnPrefix }}Foto({ date: '{{ $date->toDateString() }}', csrf: '{{ csrf_token() }}', existing: {{ $fotoUrl ? json_encode($fotoUrl) : 'null' }}, readOnly: {{ $isReadOnly ? 'true' : 'false' }} })">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">{{ $secNo++ }}</span>
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
(function() {
    const toggleUrl = '{{ route($routePrefix . '.toggle', [], false) }}';
    const fotoEndpoint = '{{ route($routePrefix . '.foto.upload', [], false) }}';

    window['{{ $jsFnPrefix }}Page'] = function(cfg) {
        window['{{ $jsFnPrefix }}Page_cfg'] = cfg;
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
                    const res = await fetch(toggleUrl, {
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
            toggleLife(itemId, val) {
                this.toggle('life', itemId, val === true);
            },
            async saveLifeValue(itemId, value) {
                if (!value) return;
                this.lifeValues[itemId] = value;
                try {
                    const res = await fetch(toggleUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ type: 'life', item_id: itemId, date: this.date, checked: true, value: value }),
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message || 'Gagal menyimpan');
                    if (!this.hasLife(itemId)) this.state.life.push(Number(itemId));
                    this.showMsg('Tersimpan');
                } catch (e) {
                    this.showMsg(e.message || 'Gagal menyimpan, coba lagi.');
                }
            },
            async saveStudy(itemId, jamMulai, jamSelesai, tipe) {
                try {
                    const res = await fetch(toggleUrl, {
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
            hasLife(itemId) {
                return this.state.life.some(x => Number(x) === Number(itemId));
            },
            toggleLife(itemId, val) {
                if (this.hasLife(itemId) === val) return; // Already in target state
                this.toggle('life', itemId, val === true);
            },
            _snap(type, itemId) {
                if (type === 'life') return this.hasLife(itemId);
                return this.state[type];
            },
            _apply(type, itemId, checked) {
                if (type === 'life') {
                    const has = this.hasLife(itemId);
                    if (checked && !has) this.state.life = [...this.state.life, itemId];
                    if (!checked && has) this.state.life = this.state.life.filter(x => Number(x) !== Number(itemId));
                } else {
                    this.state[type] = checked;
                }
            },
        };
    };
    window['{{ $jsFnPrefix }}HafalAyat'] = function(cfg) {
        function parse(ref) {
            if (!ref) return { kitab: '', pasal: '', ayat: '' };
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
                    const res = await fetch(cfg.toggleUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ type: 'verse', date: cfg.date, verse_ref: verseRef }),
                    });
                    if (!res.ok) throw new Error();
                    this.saved = verseRef || '';
                } catch {
                    // silent fail
                }
            },
        };
    };


    window['{{ $jsFnPrefix }}Foto'] = function({ date, csrf, existing, readOnly }) {
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
                    const res = await fetch(fotoEndpoint, { method: 'POST', body: form });
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
                    const res = await fetch(fotoEndpoint, {
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
    };
})();
</script>
@endpush
