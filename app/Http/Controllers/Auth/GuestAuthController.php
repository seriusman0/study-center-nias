<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class GuestAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ]);

        $guestRole = Role::where('name', 'guest')->first();
        $base = Str::slug($validated['name'], '');
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        $user = User::create([
            'name'      => $validated['name'],
            'username'  => $username,
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'is_active' => true,
        ]);

        if ($guestRole) {
            $user->roles()->attach($guestRole->id);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles', 'cabang'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login'    => ['required_without:email', 'nullable', 'string'],
            'email'    => ['required_without:login', 'nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credential = $validated['login'] ?? $validated['email'];
        $user = User::where('email', $credential)
            ->orWhere('username', $credential)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Username/email atau password salah.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun dinonaktifkan.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles', 'cabang'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles', 'cabang', 'socialLinks');
        $payload = $user->toArray();
        $payload['role_names'] = $user->roles->pluck('name')->all();
        return response()->json($payload);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $current = $user->currentAccessToken();
        if ($current) {
            $current->delete();
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'user'  => $user->load('roles', 'cabang'),
            'token' => $token,
        ]);
    }
}
