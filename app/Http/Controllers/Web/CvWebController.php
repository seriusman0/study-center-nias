<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CvWebController extends Controller
{
    public function show(string $username)
    {
        $user = User::where('username', $username)
            ->where('is_active', true)
            ->where('cv_enabled', true)
            ->with(['role', 'cabang', 'cvData', 'socialLinks'])
            ->firstOrFail();

        return view('cv.show', compact('user'));
    }

    public function kartuNama(string $username)
    {
        $user = User::where('username', $username)
            ->where('is_active', true)
            ->with(['role', 'cabang', 'socialLinks'])
            ->firstOrFail();

        return view('cv.kartu-nama', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user()->load('cvData');
        $cv = $user->cvData;
        return view('cv.edit', compact('user', 'cv'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'pendidikan'   => 'nullable|array',
            'pengalaman'   => 'nullable|array',
            'keterampilan' => 'nullable|array',
            'portofolio'   => 'nullable|string',
            'template'     => 'nullable|string|in:template1,template2,template3',
        ]);

        $user->cvData()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'pendidikan'   => $request->pendidikan ?? [],
                'pengalaman'   => $request->pengalaman ?? [],
                'keterampilan' => $request->keterampilan ?? [],
                'portofolio'   => $request->portofolio,
                'template'     => $request->template ?? 'template1',
            ]
        );

        return redirect()->route('cv.edit')->with('success', 'CV diperbarui!');
    }
}
