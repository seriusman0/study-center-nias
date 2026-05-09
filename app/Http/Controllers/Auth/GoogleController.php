<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (!$user) {
            $user = User::create([
                'google_id'         => $googleUser->getId(),
                'name'              => $googleUser->getName(),
                'username'          => $this->generateUsername($googleUser->getName()),
                'email'             => $googleUser->getEmail(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'is_active'         => true,
            ]);
            $studentRole = Role::where('name', 'student')->first();
            if ($studentRole) {
                $user->roles()->attach($studentRole->id);
            }
        } else {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar() ?? $user->avatar,
            ]);
        }

        if (!$user->is_active) {
            return redirect(env('FRONTEND_URL') . '/login?error=account_disabled');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect(env('FRONTEND_URL') . '/auth/callback?token=' . $token);
    }

    private function generateUsername(string $name): string
    {
        $base = Str::slug($name, '');
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }
        return $username;
    }
}
