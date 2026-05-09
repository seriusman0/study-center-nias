<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Cabang;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::with(['user', 'cabang', 'tags'])
            ->whereNotNull('published_at');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('cabang')) {
            $query->whereHas('cabang', fn($q) => $q->where('slug', $request->cabang));
        }
        if ($request->get('sort') === 'popular') {
            $query->withCount('comments')->orderByDesc('comments_count');
        } else {
            $query->latest('published_at');
        }

        $blogs = $query->paginate(12)->withQueryString();
        $cabangs = Cabang::all();

        return view('blog.index', compact('blogs', 'cabangs'));
    }

    public function show(string $slug)
    {
        $blog = Blog::with(['user.role', 'cabang', 'tags'])->where('slug', $slug)->firstOrFail();
        $comments = $blog->rootComments()->with(['user', 'replies.user'])->latest()->get();

        return view('blog.show', compact('blog', 'comments'));
    }

    public function create()
    {
        $cabangs = Cabang::all();
        return view('blog.form', compact('cabangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'cabang_id' => 'required|exists:cabangs,id',
            'image'     => 'nullable|image|max:2048',
        ]);

        $data = [
            'user_id'      => auth()->id(),
            'title'        => $request->title,
            'slug'         => Str::slug($request->title) . '-' . Str::random(5),
            'content'      => $request->content,
            'cabang_id'    => $request->cabang_id,
            'published_at' => now(),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog = Blog::create($data);

        if ($request->filled('tags')) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds = collect($tagNames)->map(fn($name) => Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            )->id);
            $blog->tags()->sync($tagIds);
        }

        return redirect()->route('blog.show', $blog->slug)->with('success', 'Blog diterbitkan!');
    }

    public function edit(string $slug)
    {
        $blog = Blog::with('tags')->where('slug', $slug)->firstOrFail();

        if (auth()->id() !== $blog->user_id && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $cabangs = Cabang::all();
        return view('blog.form', compact('blog', 'cabangs'));
    }

    public function update(Request $request, Blog $blog)
    {
        if (auth()->id() !== $blog->user_id && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'cabang_id' => 'required|exists:cabangs,id',
            'image'     => 'nullable|image|max:2048',
        ]);

        $data = [
            'title'     => $request->title,
            'content'   => $request->content,
            'cabang_id' => $request->cabang_id,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($data);

        if ($request->filled('tags')) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds = collect($tagNames)->map(fn($name) => Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            )->id);
            $blog->tags()->sync($tagIds);
        } else {
            $blog->tags()->detach();
        }

        return redirect()->route('blog.show', $blog->slug)->with('success', 'Blog diperbarui!');
    }

    public function destroy(Blog $blog)
    {
        if (auth()->id() !== $blog->user_id && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $blog->delete();
        return redirect()->route('blog.index')->with('success', 'Blog dihapus.');
    }
}
