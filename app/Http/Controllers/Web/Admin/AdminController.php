<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Cabang;
use App\Models\Comment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users'    => User::count(),
            'total_blogs'    => Blog::count(),
            'total_comments' => Comment::count(),
            'total_cabangs'  => Cabang::count(),
        ];

        $usersByRole = Role::withCount('users')->get();
        $blogsByCabang = Cabang::withCount('blogs')->get();

        return view('admin.dashboard', compact('stats', 'usersByRole', 'blogsByCabang'));
    }

    public function users(Request $request)
    {
        $query = User::with(['role', 'cabang']);
        if ($request->filled('role')) {
            $query->whereHas('role', fn($q) => $q->where('name', $request->role));
        }
        $users = $query->latest()->paginate(20)->withQueryString();
        $roles = Role::all();
        return view('admin.users', compact('users', 'roles'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|exists:roles,name']);
        $role = Role::where('name', $request->role)->firstOrFail();
        $user->update(['role_id' => $role->id]);
        return back()->with('success', 'Role diperbarui.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', 'Status pengguna diperbarui.');
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'Pengguna dihapus.');
    }

    public function cabangs()
    {
        $cabangs = Cabang::withCount('blogs')->get();
        return view('admin.cabangs', compact('cabangs'));
    }

    public function storeCabang(Request $request)
    {
        $request->validate([
            'nama'    => 'required|string|max:255',
            'alamat'  => 'nullable|string',
            'kontak'  => 'nullable|string',
        ]);

        Cabang::create([
            'nama'   => $request->nama,
            'slug'   => \Illuminate\Support\Str::slug($request->nama),
            'alamat' => $request->alamat,
            'kontak' => $request->kontak,
        ]);

        return back()->with('success', 'Cabang ditambahkan.');
    }

    public function updateCabang(Request $request, Cabang $cabang)
    {
        $request->validate([
            'nama'   => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'kontak' => 'nullable|string',
        ]);

        $cabang->update([
            'nama'   => $request->nama,
            'slug'   => \Illuminate\Support\Str::slug($request->nama),
            'alamat' => $request->alamat,
            'kontak' => $request->kontak,
        ]);

        return back()->with('success', 'Cabang diperbarui.');
    }

    public function deleteCabang(Cabang $cabang)
    {
        $cabang->delete();
        return back()->with('success', 'Cabang dihapus.');
    }

    public function blogs(Request $request)
    {
        $query = Blog::with(['user', 'cabang']);
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        $blogs = $query->latest()->paginate(20)->withQueryString();
        return view('admin.blogs', compact('blogs'));
    }

    public function deleteBlog(Blog $blog)
    {
        $blog->delete();
        return back()->with('success', 'Blog dihapus.');
    }
}
