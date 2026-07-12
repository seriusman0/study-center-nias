@extends('layouts.app')

@section('title', 'Edit Profil - Study Center Nias')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-sc-ink-900 mb-8">Edit Profil</h1>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow-sc-2 border border-sc-line p-8 space-y-5">
        @csrf

        {{-- Avatar --}}
        <div class="flex items-center gap-4">
            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background=007a5c&color=fff' }}"
                 class="w-20 h-20 rounded-full object-cover ring-2 ring-sc-orange-500" alt="">
            <div>
                <label class="block text-sm font-medium mb-1 text-sc-ink-900">Foto Profil</label>
                <input type="file" name="avatar" accept="image/*" class="text-sm">
            </div>
        </div>

        {{-- Username (readonly) --}}
        <div>
            <label class="block text-sm font-medium mb-1 text-sc-ink-900">Username</label>
            <input type="text" value="{{ $user->username }}" disabled
                   class="w-full border border-sc-line rounded-xl px-4 py-3 text-sm bg-gray-50 text-sc-ink-500 cursor-not-allowed">
            <p class="text-xs text-sc-ink-400 mt-1">Username tidak dapat diubah.</p>
        </div>

        {{-- Nama --}}
        <div>
            <label class="block text-sm font-medium mb-1 text-sc-ink-900">Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full border border-sc-line rounded-xl px-4 py-3 focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 text-sm">
        </div>

        {{-- Bio --}}
        <div>
            <label class="block text-sm font-medium mb-1 text-sc-ink-900">Bio</label>
            <textarea name="bio" rows="3"
                      class="w-full border border-sc-line rounded-xl px-4 py-3 focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 text-sm resize-none"
                      placeholder="Cerita singkat tentang kamu...">{{ old('bio', $user->bio) }}</textarea>
        </div>

        {{-- Cabang (disabled) --}}
        <div>
            <label class="block text-sm font-medium mb-1 text-sc-ink-900">Cabang</label>
            <input type="text" value="{{ $user->cabang?->nama ?? 'Belum ditentukan' }}" disabled
                   class="w-full border border-sc-line rounded-xl px-4 py-3 text-sm bg-gray-50 text-sc-ink-500 cursor-not-allowed">
            <p class="text-xs text-sc-ink-400 mt-1">Cabang hanya dapat diubah oleh admin.</p>
        </div>

        {{-- Social Links --}}
        <div>
            <label class="block text-sm font-medium mb-2 text-sc-ink-900">Media Sosial</label>
            @foreach($platforms as $i => $platform)
            @php $existing = $user->socialLinks->firstWhere('platform', $platform); @endphp
            <div class="flex items-center gap-2 mb-2">
                <span class="w-24 text-sm text-sc-ink-700 capitalize">{{ $platform }}</span>
                <input type="hidden" name="social_links[{{ $i }}][platform]" value="{{ $platform }}">
                <input type="text" name="social_links[{{ $i }}][value]"
                       value="{{ old("social_links.$i.value", $existing?->value) }}"
                       class="flex-1 border border-sc-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-sc-teal-600"
                       placeholder="{{ $platform === 'email' ? 'email@contoh.com' : ($platform === 'whatsapp' ? '08...' : 'URL profil') }}">
            </div>
            @endforeach
        </div>

        {{-- Visibility --}}
        <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="profile_public" value="1"
                       {{ old('profile_public', $user->profile_public) ? 'checked' : '' }}
                       class="rounded accent-sc-teal-600">
                <span class="text-sm">Profil publik</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="cv_enabled" value="1"
                       {{ old('cv_enabled', $user->cv_enabled) ? 'checked' : '' }}
                       class="rounded accent-sc-teal-600">
                <span class="text-sm">CV dapat diakses</span>
            </label>
        </div>

        {{-- Ganti Password --}}
        <div class="border-t border-sc-line pt-5">
            <h2 class="text-base font-semibold text-sc-ink-900 mb-4">Ganti Password</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-sc-ink-900">Password Saat Ini</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="w-full border border-sc-line rounded-xl px-4 py-3 focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 text-sm"
                           placeholder="Kosongkan jika tidak ingin ganti password">
                    @error('current_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-sc-ink-900">Password Baru</label>
                    <input type="password" name="new_password" autocomplete="new-password"
                           class="w-full border border-sc-line rounded-xl px-4 py-3 focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 text-sm"
                           placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-sc-ink-900">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" autocomplete="new-password"
                           class="w-full border border-sc-line rounded-xl px-4 py-3 focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 text-sm"
                           placeholder="Ulangi password baru">
                    @error('new_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <button type="submit"
                class="w-full py-3 bg-sc-teal-600 hover:bg-sc-teal-700 text-white rounded-xl font-semibold transition">
            Simpan Perubahan
        </button>
    </form>
</div>
@endsection
