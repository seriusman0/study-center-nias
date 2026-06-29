<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Cabang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CabangController extends Controller
{
    public function show(string $slug, Request $request): JsonResponse
    {
        $cabang = Cabang::where('slug', $slug)->withCount('blogs')->firstOrFail();

        $blogs = Blog::with(['user:id,name,username,avatar', 'tags:id,name,slug'])
            ->where('cabang_id', $cabang->id)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->paginate(12);

        return response()->json([
            'cabang' => $cabang,
            'blogs'  => $blogs,
        ]);
    }
}
