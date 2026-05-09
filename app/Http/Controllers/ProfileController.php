<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSocialLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProfileController extends Controller
{
    public function show(string $username): JsonResponse
    {
        $user = User::where('username', $username)
            ->where('is_active', true)
            ->where('profile_public', true)
            ->with(['role:id,name', 'cabang:id,nama,slug', 'socialLinks'])
            ->firstOrFail();

        $blogs = $user->blogs()
            ->whereNotNull('published_at')
            ->with('cabang:id,nama,slug', 'tags:id,name,slug')
            ->latest('published_at')
            ->get(['id', 'title', 'slug', 'image', 'published_at', 'cabang_id']);

        return response()->json([
            'user' => $user,
            'blogs' => $blogs,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'cabang_id' => ['nullable', 'exists:cabangs,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'profile_public' => ['sometimes', 'boolean'],
            'cv_enabled' => ['sometimes', 'boolean'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.platform' => ['required', 'in:instagram,whatsapp,email,facebook'],
            'social_links.*.value' => ['required', 'string', 'max:255'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $userFields = Arr::except($validated, ['social_links']);
        $user->update($userFields);

        if (isset($validated['social_links'])) {
            $user->socialLinks()->delete();
            foreach ($validated['social_links'] as $link) {
                if (!empty($link['value'])) {
                    UserSocialLink::create([
                        'user_id' => $user->id,
                        'platform' => $link['platform'],
                        'value' => $link['value'],
                    ]);
                }
            }
        }

        return response()->json($user->fresh()->load('role', 'cabang', 'socialLinks'));
    }
}
