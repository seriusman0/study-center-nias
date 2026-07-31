<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Blog::with(['user:id,name,username,avatar', 'cabang:id,nama,slug', 'tags:id,name,slug'])
            ->whereNotNull('published_at');

        if ($request->cabang) {
            $query->whereHas('cabang', fn($q) => $q->where('slug', $request->cabang));
        }
        if ($request->tag) {
            $query->whereHas('tags', fn($q) => $q->where('slug', $request->tag));
        }
        if ($request->author) {
            $query->whereHas('user', fn($q) => $q->where('username', $request->author));
        }
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort', 'latest');
        if ($sortBy === 'popular') {
            $query->withCount('comments')->orderByDesc('comments_count');
        } else {
            $query->orderByDesc('published_at');
        }

        return response()->json($query->paginate(12));
    }

    public function show(string $slug): JsonResponse
    {
        $blog = Blog::with([
            'user:id,name,username,avatar,cabang_id',
            'user.roles:id,name',
            'cabang:id,nama,slug',
            'tags:id,name,slug',
        ])->whereNotNull('published_at')->where('slug', $slug)->firstOrFail();

        return response()->json($blog);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $i = 1;
        while (Blog::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blogs', 'public');
        }

        $blog = Blog::create([
            'user_id' => $request->user()->id,
            'cabang_id' => $validated['cabang_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $this->purifyHtml($validated['content']),
            'image' => $imagePath,
            'published_at' => now(),
        ]);

        if (!empty($validated['tags'])) {
            $tagIds = collect($validated['tags'])->map(function ($name) {
                $slug = Str::slug($name);
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name, 'slug' => $slug])->id;
            });
            $blog->tags()->sync($tagIds);
        }

        return response()->json($blog->load('user', 'cabang', 'tags'), 201);
    }

    public function update(Request $request, Blog $blog): JsonResponse
    {
        $user = $request->user();
        if ($blog->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'cabang_id' => ['sometimes', 'exists:cabangs,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if (isset($validated['content'])) {
            $validated['content'] = $this->purifyHtml($validated['content']);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($validated);

        if (array_key_exists('tags', $validated)) {
            $tagIds = collect($validated['tags'] ?? [])->map(function ($name) {
                $slug = Str::slug($name);
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name, 'slug' => $slug])->id;
            });
            $blog->tags()->sync($tagIds);
        }

        return response()->json($blog->load('user', 'cabang', 'tags'));
    }

    public function destroy(Request $request, Blog $blog): JsonResponse
    {
        $user = $request->user();
        if ($blog->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $blog->delete();
        return response()->json(['message' => 'Blog dihapus.']);
    }
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $path = $request->file('image')->store('blogs/inline', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }

    private function purifyHtml(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed',
            'p,br,strong,em,u,s,ul,ol,li,h2,h3,h4,blockquote,pre,code,a[href|target|rel],img[src|alt|width|height],span[style],div,table,thead,tbody,tr,th,td'
        );
        $config->set('HTML.AllowedAttributes', 'a.href,a.target,a.rel,img.src,img.alt,img.width,img.height,span.style');
        $config->set('CSS.AllowedProperties', 'color,background-color,font-weight,font-style,text-decoration,text-align');
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('Attr.AllowedRel', ['noopener', 'noreferrer', 'nofollow']);
        $config->set('URI.SafeIframeRegexp', null);
        $config->set('AutoFormat.RemoveEmpty', true);
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
