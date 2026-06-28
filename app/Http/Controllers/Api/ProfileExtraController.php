<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProfileExtraController extends Controller
{
    public function kartuNama(string $username): JsonResponse
    {
        $user = User::where('username', $username)
            ->where('is_active', true)
            ->where('profile_public', true)
            ->with(['roles:id,name', 'cabang:id,nama', 'socialLinks'])
            ->firstOrFail();

        return response()->json([
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'avatar'   => $user->avatar,
                'bio'      => $user->bio,
                'role'     => $user->roles->pluck('name')->first(),
                'cabang'   => $user->cabang?->nama,
            ],
            'social_links' => $user->socialLinks,
        ]);
    }
}
