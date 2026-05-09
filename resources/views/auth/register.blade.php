@extends('layouts.app')

@section('title', 'Daftar - Study Center Nias')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-[#1e3a5f] mb-2 text-center">Daftar Akun Tamu</h1>
        <p class="text-gray-500 text-sm text-center mb-6">
            Bergabung untuk membaca dan berkomentar di blog Study Center Nias
        </p>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                       class="w-full border rounded-xl px-4 py-3 outline-[#1e3a5f] text-sm"
                       placeholder="Nama kamu">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}"
                       class="w-full border rounded-xl px-4 py-3 outline-[#1e3a5f] text-sm"
                       placeholder="email@contoh.com">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password (min. 8 karakter)</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full border rounded-xl px-4 py-3 outline-[#1e3a5f] text-sm"
                       placeholder="••••••••">
            </div>
            <button type="submit"
                    class="w-full py-3 bg-[#1e3a5f] text-white rounded-xl font-semibold hover:bg-[#2d5282] transition">
                Daftar Sekarang
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-[#1e3a5f] font-medium hover:underline">Masuk</a>
        </p>
        <p class="text-center text-xs text-gray-400 mt-2">
            Pengurus internal?
            <a href="{{ route('auth.google') }}" class="text-[#1e3a5f] hover:underline">Masuk dengan Google</a>
        </p>
    </div>
</div>
@endsection
