<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthWebController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

        $user = Auth::user();
        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun tidak aktif.']);
        }

        $request->session()->regenerate();
        return redirect()->intended('/')->with('success', 'Selamat datang kembali!');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $guestRole = Role::where('name', 'guest')->first();

        $user = User::create([
            'name'     => $request->name,
            'username' => Str::slug($request->name) . '-' . Str::random(4),
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => $guestRole?->id,
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Akun berhasil dibuat!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
