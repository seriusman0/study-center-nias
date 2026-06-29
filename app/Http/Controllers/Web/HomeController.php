<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Cabang;

class HomeController extends Controller
{
    public function index()
    {
        $blogs = Blog::with(['user', 'cabang', 'tags'])
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->take(6)
            ->get();

        $cabangs = Cabang::all();

        return view('home', compact('blogs', 'cabangs'));
    }
}
