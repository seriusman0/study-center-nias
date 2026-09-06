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
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->input('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field     => $login,
            'password' => $request->input('password'),
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Email/username atau password salah.'], 401);
            }
            return back()->withErrors(['login' => 'Email/username atau password salah.'])->withInput();
        }

        $user = Auth::user();
        if (! $user->is_active) {
            Auth::logout();
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Akun tidak aktif.'], 403);
            }
            return back()->withErrors(['login' => 'Akun tidak aktif.']);
        }

        $request->session()->regenerate();

        $redirect = '/';
        if ($user->hasRole(['admin', 'fulltimer'])) {
            $redirect = route('admin.dashboard');
        } elseif ($user->hasRole(['student', 'college', 'scholarship_teenager', 'mentor', 'prajurit'])) {
            $redirect = route('beranda');
        }

        \Log::info('Login redirect debug:', [
            'username' => $user->username,
            'roles' => $user->roles->pluck('name')->toArray(),
            'has_prajurit' => $user->hasRole('prajurit'),
            'redirect' => $redirect
        ]);

        if ($request->wantsJson()) {
            $token = null;
            if ($request->boolean('save_login')) {
                $token = $user->createToken('fast-login')->plainTextToken;
            }
            return response()->json([
                'success' => true,
                'redirect' => $redirect,
                'token' => $token,
                'user' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->avatar
                ]
            ]);
        }

        return redirect()->intended($redirect);
    }

    public function fastLogin(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->token);
        
        if (!$token || !$token->tokenable) {
            return response()->json(['message' => 'Token tidak valid atau sudah kadaluarsa.'], 401);
        }

        $user = $token->tokenable;
        if (! $user->is_active) {
            return response()->json(['message' => 'Akun tidak aktif.'], 403);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $redirect = '/';
        if ($user->hasRole(['admin', 'fulltimer'])) {
            $redirect = route('admin.dashboard');
        } elseif ($user->hasRole(['student', 'college', 'scholarship_teenager', 'mentor', 'prajurit'])) {
            $redirect = route('beranda');
        }

        return response()->json([
            'success' => true,
            'redirect' => $redirect
        ]);
    }

    public function pilihRegistrasi()
    {
        return view('auth.pilih-registrasi');
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
            'name'      => $request->name,
            'username'  => Str::slug($request->name) . '-' . Str::random(4),
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        if ($guestRole) {
            $user->roles()->attach($guestRole->id);
        }

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
