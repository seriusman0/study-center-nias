<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\User;
use App\Models\UserSocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProfileWebController extends Controller
{
    public function show(string $username)
    {
        $user = User::where('username', $username)
            ->where('is_active', true)
            ->where('profile_public', true)
            ->with(['role', 'cabang', 'socialLinks'])
            ->firstOrFail();

        $blogs = $user->blogs()
            ->whereNotNull('published_at')
            ->with(['cabang', 'tags'])
            ->latest('published_at')
            ->get();

        return view('profile.show', compact('user', 'blogs'));
    }

    public function edit()
    {
        $user = auth()->user()->load(['cabang', 'socialLinks']);
        $cabangs = Cabang::all();
        $platforms = ['instagram', 'whatsapp', 'email', 'facebook'];
        return view('profile.edit', compact('user', 'cabangs', 'platforms'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'bio'            => 'nullable|string|max:500',
            'cabang_id'      => 'nullable|exists:cabangs,id',
            'avatar'         => 'nullable|image|max:2048',
            'profile_public' => 'sometimes|boolean',
            'cv_enabled'     => 'sometimes|boolean',
            'social_links'   => 'nullable|array',
            'social_links.*.platform' => 'required|in:instagram,whatsapp,email,facebook',
            'social_links.*.value'    => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $validated['profile_public'] = $request->boolean('profile_public');
        $validated['cv_enabled']     = $request->boolean('cv_enabled');

        $userFields = Arr::except($validated, ['social_links']);
        $user->update($userFields);

        if ($request->has('social_links')) {
            $user->socialLinks()->delete();
            foreach (($validated['social_links'] ?? []) as $link) {
                if (! empty($link['value'])) {
                    UserSocialLink::create([
                        'user_id'  => $user->id,
                        'platform' => $link['platform'],
                        'value'    => $link['value'],
                    ]);
                }
            }
        }

        return redirect()->route('profile.show', $user->username)->with('success', 'Profil diperbarui!');
    }
}
