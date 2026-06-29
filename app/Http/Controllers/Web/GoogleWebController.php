<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleWebController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Login Google gagal.']);
        }

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            $user->update(['google_id' => $googleUser->id]);
            if (! $user->is_active) {
                return redirect('/login')->withErrors(['email' => 'Akun tidak aktif.']);
            }
        } else {
            $user = User::create([
                'google_id' => $googleUser->id,
                'name'      => $googleUser->name,
                'username'  => Str::slug($googleUser->name) . '-' . Str::random(4),
                'email'     => $googleUser->email,
                'avatar'    => $googleUser->avatar,
                'is_active' => true,
            ]);
            $guestRole = Role::where('name', 'guest')->first();
            if ($guestRole) {
                $user->roles()->attach($guestRole->id);
            }
        }

        Auth::login($user);
        return redirect('/')->with('success', 'Selamat datang, ' . $user->name . '!');
    }
}
