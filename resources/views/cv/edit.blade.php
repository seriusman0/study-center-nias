@extends('layouts.app')

@section('title', 'Edit CV - Study Center Nias')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10" x-data="cvEditor()">
    <h1 class="text-2xl font-bold text-[#1e3a5f] mb-8">Edit CV</h1>

    <form method="POST" action="{{ route('cv.update') }}" class="space-y-8">
        @csrf

        {{-- Template --}}
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="font-semibold mb-3">Template</h2>
            <div class="flex gap-3">
                @foreach(['template1','template2','template3'] as $t)
                <label class="cursor-pointer">
                    <input type="radio" name="template" value="{{ $t }}"
                           {{ ($cv->template ?? 'template1') === $t ? 'checked' : '' }} class="sr-only">
                    <div class="border-2 rounded-lg px-4 py-2 text-sm font-medium transition
                               {{ ($cv->template ?? 'template1') === $t ? 'border-[#1e3a5f] text-[#1e3a5f]' : 'border-gray-200 hover:border-gray-400' }}">
                        {{ ucfirst($t) }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Pendidikan --}}
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Pendidikan</h2>
                <button type="button" @click="addPendidikan()"
                        class="text-sm px-3 py-1 bg-[#1e3a5f] text-white rounded-lg">+ Tambah</button>
            </div>
            <template x-for="(item, i) in pendidikan" :key="i">
                <div class="border rounded-xl p-4 mb-3 space-y-2">
                    <input type="hidden" :name="`pendidikan[${i}][jenjang]`" :value="item.jenjang">
                    <input type="hidden" :name="`pendidikan[${i}][institusi]`" :value="item.institusi">
                    <input type="hidden" :name="`pendidikan[${i}][tahun_lulus]`" :value="item.tahun_lulus">
                    <div class="grid grid-cols-3 gap-2">
                        <input x-model="item.jenjang" placeholder="Jenjang (S1, SMA...)"
                               class="border rounded-lg px-3 py-2 text-sm outline-[#1e3a5f]">
                        <input x-model="item.institusi" placeholder="Nama Institusi"
                               class="border rounded-lg px-3 py-2 text-sm outline-[#1e3a5f]">
                        <input x-model="item.tahun_lulus" type="number" placeholder="Tahun Lulus"
                               class="border rounded-lg px-3 py-2 text-sm outline-[#1e3a5f]">
                    </div>
                    <button type="button" @click="pendidikan.splice(i,1)"
                            class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                </div>
            </template>
        </div>

        {{-- Pengalaman --}}
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Pengalaman</h2>
                <button type="button" @click="addPengalaman()"
                        class="text-sm px-3 py-1 bg-[#1e3a5f] text-white rounded-lg">+ Tambah</button>
            </div>
            <template x-for="(item, i) in pengalaman" :key="i">
                <div class="border rounded-xl p-4 mb-3 space-y-2">
                    <input type="hidden" :name="`pengalaman[${i}][posisi]`" :value="item.posisi">
                    <input type="hidden" :name="`pengalaman[${i}][deskripsi]`" :value="item.deskripsi">
                    <input type="hidden" :name="`pengalaman[${i}][tahun]`" :value="item.tahun">
                    <div class="grid grid-cols-2 gap-2">
                        <input x-model="item.posisi" placeholder="Posisi/Jabatan"
                               class="border rounded-lg px-3 py-2 text-sm outline-[#1e3a5f]">
                        <input x-model="item.tahun" placeholder="Tahun (2020-2022)"
                               class="border rounded-lg px-3 py-2 text-sm outline-[#1e3a5f]">
                    </div>
                    <textarea x-model="item.deskripsi" placeholder="Deskripsi singkat..." rows="2"
                              class="w-full border rounded-lg px-3 py-2 text-sm outline-[#1e3a5f] resize-none"></textarea>
                    <button type="button" @click="pengalaman.splice(i,1)"
                            class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                </div>
            </template>
        </div>

        {{-- Keterampilan --}}
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="font-semibold mb-3">Keterampilan</h2>
            <div class="flex flex-wrap gap-2 mb-3">
                <template x-for="(skill, i) in keterampilan" :key="i">
                    <div class="flex items-center gap-1 bg-[#1e3a5f]/10 text-[#1e3a5f] px-3 py-1 rounded-full text-sm">
                        <input type="hidden" :name="`keterampilan[${i}]`" :value="skill">
                        <span x-text="skill"></span>
                        <button type="button" @click="keterampilan.splice(i,1)" class="ml-1 text-red-400">×</button>
                    </div>
                </template>
            </div>
            <div class="flex gap-2">
                <input x-model="newSkill" @keydown.enter.prevent="addSkill()"
                       placeholder="Tambah keterampilan, Enter..."
                       class="flex-1 border rounded-lg px-3 py-2 text-sm outline-[#1e3a5f]">
                <button type="button" @click="addSkill()"
                        class="px-3 py-2 bg-[#1e3a5f] text-white rounded-lg text-sm">+</button>
            </div>
        </div>

        {{-- Portofolio --}}
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="font-semibold mb-3">Portofolio</h2>
            <textarea name="portofolio" rows="4"
                      class="w-full border rounded-xl px-4 py-3 outline-[#1e3a5f] text-sm resize-none"
                      placeholder="Deskripsi portofolio atau link...">{{ old('portofolio', $cv?->portofolio) }}</textarea>
        </div>

        <button type="submit"
                class="w-full py-3 bg-[#1e3a5f] text-white rounded-xl font-semibold hover:bg-[#2d5282] transition">
            Simpan CV
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
function cvEditor() {
    return {
        pendidikan: @json($cv?->pendidikan ?? []),
        pengalaman: @json($cv?->pengalaman ?? []),
        keterampilan: @json($cv?->keterampilan ?? []),
        newSkill: '',
        addPendidikan() {
            this.pendidikan.push({ jenjang: '', institusi: '', tahun_lulus: '' });
        },
        addPengalaman() {
            this.pengalaman.push({ posisi: '', deskripsi: '', tahun: '' });
        },
        addSkill() {
            if (this.newSkill.trim()) {
                this.keterampilan.push(this.newSkill.trim());
                this.newSkill = '';
            }
        },
    };
}
</script>
@endpush
