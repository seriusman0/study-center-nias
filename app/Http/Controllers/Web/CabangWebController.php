<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Blog;
use Illuminate\Http\Request;

class CabangWebController extends Controller
{
    public function index()
    {
        $cabangs = Cabang::withCount('blogs')->get();
        return view('cabang.index', compact('cabangs'));
    }

    public function show(string $slug, Request $request)
    {
        $cabang = Cabang::where('slug', $slug)->firstOrFail();
        $blogs = Blog::with(['user', 'tags'])
            ->where('cabang_id', $cabang->id)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('cabang.show', compact('cabang', 'blogs'));
    }
}
