@extends('layouts.app')

@section('title', 'Masuk - Study Center Nias')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-sc-teal-800 mb-2 text-center">Masuk</h1>
        <p class="text-sc-ink-500 text-sm text-center mb-6">Masuk ke akun Study Center Nias</p>



        <div id="accountSwitcher" class="hidden mb-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Akun Tersimpan</h2>
            <div id="savedAccountsList" class="space-y-2">
                <!-- Accounts injected via JS -->
            </div>
            <div class="mt-4 flex items-center justify-between pt-4 border-t border-gray-100">
                <button type="button" id="showLoginFormBtn" class="text-sm text-sc-teal-600 font-medium hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Masuk ke akun lain
                </button>
            </div>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login', [], false) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email atau Username</label>
                <input type="text" name="login" id="loginInput" required autofocus value="{{ old('login') }}"
                       autocomplete="username"
                       class="w-full border border-sc-line rounded-xl px-4 py-3 focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 text-sm"
                       placeholder="email@contoh.com / username">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" id="passwordInput" required
                       autocomplete="current-password"
                       class="w-full border border-sc-line rounded-xl px-4 py-3 focus:outline-none focus:border-sc-teal-600 focus:ring-2 focus:ring-sc-teal-600/20 text-sm"
                       placeholder="••••••••">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-gray-600">
                    <input type="checkbox" name="save_login" id="saveLoginInfo" value="1" class="rounded border-gray-300" checked>
                    Simpan info login
                </label>
                <label class="flex items-center gap-2 text-gray-600 hidden">
                    <input type="checkbox" name="remember" value="1" class="rounded border-gray-300">
                    Ingat saya
                </label>
            </div>
            <div id="loginError" class="text-red-500 text-sm hidden"></div>
            <button type="submit" id="loginSubmitBtn"
                    class="w-full py-3 bg-sc-teal-600 text-white rounded-xl font-semibold hover:bg-sc-teal-700 transition flex justify-center items-center gap-2">
                Masuk
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-sc-teal-700 font-medium hover:underline">Daftar</a>
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const accountSwitcher = document.getElementById('accountSwitcher');
    const savedAccountsList = document.getElementById('savedAccountsList');
    const showLoginFormBtn = document.getElementById('showLoginFormBtn');
    const loginError = document.getElementById('loginError');
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');

    // Load saved accounts
    let savedAccounts = [];
    try {
        savedAccounts = JSON.parse(localStorage.getItem('sc_saved_accounts') || '[]');
    } catch (e) {}

    function renderSavedAccounts() {
        if (savedAccounts.length === 0) {
            accountSwitcher.classList.add('hidden');
            loginForm.classList.remove('hidden');
            return;
        }

        accountSwitcher.classList.remove('hidden');
        loginForm.classList.add('hidden');
        savedAccountsList.innerHTML = '';

        savedAccounts.forEach((account) => {
            const el = document.createElement('div');
            el.className = 'flex items-center justify-between p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition cursor-pointer group';
            
            // Left side (avatar + name)
            const left = document.createElement('div');
            left.className = 'flex items-center gap-3 flex-1';
            left.onclick = () => fastLogin(account.token);
            
            const avatar = document.createElement('div');
            avatar.className = 'w-10 h-10 rounded-full bg-sc-teal-100 text-sc-teal-700 flex items-center justify-center font-bold overflow-hidden flex-shrink-0';
            if (account.avatar) {
                let avatarUrl = account.avatar;
                if (avatarUrl.includes('/storage/http')) {
                    avatarUrl = avatarUrl.substring(avatarUrl.indexOf('http', avatarUrl.indexOf('/storage/') + 1));
                }
                avatar.innerHTML = `<img src="${avatarUrl}" class="w-full h-full object-cover">`;
            } else {
                avatar.textContent = account.name.charAt(0).toUpperCase();
            }
            
            const info = document.createElement('div');
            info.innerHTML = `
                <div class="text-sm font-semibold text-gray-800">${account.name}</div>
                <div class="text-xs text-gray-500">@${account.username}</div>
            `;
            
            left.appendChild(avatar);
            left.appendChild(info);

            // Right side (Login button & delete)
            const right = document.createElement('div');
            right.className = 'flex items-center gap-2';

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'p-2 text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100';
            delBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
            delBtn.onclick = (e) => {
                e.stopPropagation();
                savedAccounts = savedAccounts.filter(a => a.username !== account.username);
                localStorage.setItem('sc_saved_accounts', JSON.stringify(savedAccounts));
                renderSavedAccounts();
            };

            const loginBtn = document.createElement('button');
            loginBtn.type = 'button';
            loginBtn.className = 'px-4 py-1.5 bg-sc-teal-50 text-sc-teal-700 text-sm font-medium rounded-lg hover:bg-sc-teal-100 transition';
            loginBtn.textContent = 'Masuk';
            loginBtn.onclick = (e) => {
                e.stopPropagation();
                fastLogin(account.token, loginBtn);
            };

            right.appendChild(delBtn);
            right.appendChild(loginBtn);
            
            el.appendChild(left);
            el.appendChild(right);
            savedAccountsList.appendChild(el);
        });
    }

    renderSavedAccounts();

    showLoginFormBtn.addEventListener('click', () => {
        accountSwitcher.classList.add('hidden');
        loginForm.classList.remove('hidden');
        document.getElementById('loginInput').focus();
    });

    async function fastLogin(token, btn = null) {
        if (btn) {
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            btn.disabled = true;
        }
        try {
            const res = await fetch('{{ route("login.fast", [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: token })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Token kedaluwarsa. Silakan login manual.');
                // Remove invalid token
                savedAccounts = savedAccounts.filter(a => a.token !== token);
                localStorage.setItem('sc_saved_accounts', JSON.stringify(savedAccounts));
                renderSavedAccounts();
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
            if (btn) {
                btn.textContent = 'Masuk';
                btn.disabled = false;
            }
        }
    }

    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        loginError.classList.add('hidden');
        const originalText = loginSubmitBtn.innerHTML;
        loginSubmitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        loginSubmitBtn.disabled = true;

        try {
            const formData = new FormData(loginForm);
            const res = await fetch(loginForm.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();
            
            if (res.ok && data.success) {
                if (data.token) {
                    // Update saved accounts
                    savedAccounts = savedAccounts.filter(a => a.username !== data.user.username);
                    savedAccounts.unshift({
                        token: data.token,
                        name: data.user.name,
                        username: data.user.username,
                        avatar: data.user.avatar
                    });
                    localStorage.setItem('sc_saved_accounts', JSON.stringify(savedAccounts));
                }
                window.location.href = data.redirect;
            } else {
                loginError.textContent = data.message || 'Login gagal.';
                loginError.classList.remove('hidden');
                loginSubmitBtn.innerHTML = originalText;
                loginSubmitBtn.disabled = false;
            }
        } catch (e) {
            loginError.textContent = 'Terjadi kesalahan jaringan.';
            loginError.classList.remove('hidden');
            loginSubmitBtn.innerHTML = originalText;
            loginSubmitBtn.disabled = false;
        }
    });
});
</script>
@endsection
