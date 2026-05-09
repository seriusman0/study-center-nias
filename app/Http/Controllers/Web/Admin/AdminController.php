<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminProfile;
use App\Models\Blog;
use App\Models\Cabang;
use App\Models\Comment;
use App\Models\MentorProfile;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
        $query = User::with(['roles', 'cabang']);
        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('username', 'like', $term));
        }
        $users = $query->latest()->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();
        return view('admin.users', compact('users', 'roles'));
    }

    public function createUser()
    {
        $roles = Role::orderBy('name')->get();
        $cabangs = Cabang::orderBy('nama')->get();
        return view('admin.users_create', compact('roles', 'cabangs'));
    }

    public function storeUser(Request $request)
    {
        $data = $this->validateUser($request);
        $profileData = $this->validateProfileData($request, $data['role_names'] ?? []);

        DB::transaction(function () use ($data, $profileData, $request) {
            $userData = [
                'name'      => $data['name'],
                'username'  => $this->generateUsername($data['name']),
                'email'     => $data['email'] ?? null,
                'password'  => Hash::make($request->filled('password') ? $request->password : '12345'),
                'cabang_id' => $data['cabang_id'] ?? null,
                'is_active' => true,
            ];

            if ($request->hasFile('avatar')) {
                $userData['avatar'] = '/storage/' . $request->file('avatar')->store('avatars', 'public');
            }

            $user = User::create($userData);

            $roleNames = $data['role_names'] ?? ['student'];
            $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->all();
            $user->roles()->sync($roleIds);

            $this->syncProfiles($user, $roleNames, $profileData);

            session()->flash('new_user_username', $user->username);
        });

        return redirect()->route('admin.users')
            ->with('success', 'Pengguna ditambahkan. Username: ' . session('new_user_username'));
    }

    public function editUser(User $user)
    {
        $user->load(['roles', 'studentProfile', 'mentorProfile', 'adminProfile']);
        $roles = Role::orderBy('name')->get();
        $cabangs = Cabang::orderBy('nama')->get();
        return view('admin.users_edit', compact('user', 'roles', 'cabangs'));
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);
        $profileData = $this->validateProfileData($request, $data['role_names'] ?? []);

        DB::transaction(function () use ($data, $profileData, $request, $user) {
            $userData = [
                'name'      => $data['name'],
                'username'  => $data['username'] ?? $user->username,
                'email'     => $data['email'] ?? null,
                'cabang_id' => $data['cabang_id'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
                }
                $userData['avatar'] = '/storage/' . $request->file('avatar')->store('avatars', 'public');
            }

            $user->update($userData);

            $roleNames = $data['role_names'] ?? [];
            $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->all();
            $user->roles()->sync($roleIds);

            $this->syncProfiles($user, $roleNames, $profileData);
        });

        return redirect()->route('admin.users')->with('success', 'Pengguna diperbarui.');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);
        $names = $request->input('roles', []);
        $ids = Role::whereIn('name', $names)->pluck('id')->all();
        $user->roles()->sync($ids);
        return back()->with('success', 'Role diperbarui.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', 'Status pengguna diperbarui.');
    }

    public function deleteUser(User $user)
    {
        if ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
        }
        $user->delete();
        return back()->with('success', 'Pengguna dihapus.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password'  => 'nullable|string|min:5',
            'avatar'    => 'nullable|image|max:2048',
            'cabang_id' => 'nullable|exists:cabangs,id',
            'role_names'   => 'nullable|array',
            'role_names.*' => 'exists:roles,name',
        ];
        if ($user) {
            $rules['username'] = ['required', 'string', 'max:50', 'regex:/^[a-z0-9]+$/', Rule::unique('users', 'username')->ignore($user->id)];
        }
        return $request->validate($rules);
    }

    private function validateProfileData(Request $request, array $roleNames): array
    {
        $rules = [];
        if (in_array('student', $roleNames)) {
            $rules += [
                'student.student_number' => 'nullable|string|max:50',
                'student.birth_date'     => 'nullable|date',
                'student.birth_place'    => 'nullable|string|max:100',
                'student.gender'         => 'nullable|in:L,P,Lainnya',
                'student.address'        => 'nullable|string',
                'student.guardian_name'  => 'nullable|string|max:100',
                'student.guardian_phone' => 'nullable|string|max:50',
                'student.school_name'    => 'nullable|string|max:150',
                'student.entry_year'     => 'nullable|integer|min:2000|max:2100',
            ];
        }
        if (in_array('mentor', $roleNames)) {
            $rules += [
                'mentor.expertise'        => 'nullable|string|max:150',
                'mentor.bio'              => 'nullable|string',
                'mentor.education'        => 'nullable|string|max:150',
                'mentor.experience_years' => 'nullable|integer|min:0|max:80',
                'mentor.hourly_rate'      => 'nullable|numeric|min:0',
                'mentor.is_available'     => 'nullable|boolean',
            ];
        }
        if (in_array('admin', $roleNames)) {
            $rules += [
                'admin.employee_number' => 'nullable|string|max:50',
                'admin.department'      => 'nullable|string|max:100',
                'admin.position'        => 'nullable|string|max:100',
            ];
        }
        return $request->validate($rules);
    }

    private function syncProfiles(User $user, array $roleNames, array $data): void
    {
        if (in_array('student', $roleNames) && isset($data['student'])) {
            StudentProfile::updateOrCreate(['user_id' => $user->id], $data['student']);
        } elseif (! in_array('student', $roleNames)) {
            $user->studentProfile()?->delete();
        }

        if (in_array('mentor', $roleNames) && isset($data['mentor'])) {
            $payload = $data['mentor'];
            $payload['is_available'] = (bool) ($payload['is_available'] ?? true);
            MentorProfile::updateOrCreate(['user_id' => $user->id], $payload);
        } elseif (! in_array('mentor', $roleNames)) {
            $user->mentorProfile()?->delete();
        }

        if (in_array('admin', $roleNames) && isset($data['admin'])) {
            AdminProfile::updateOrCreate(['user_id' => $user->id], $data['admin']);
        } elseif (! in_array('admin', $roleNames)) {
            $user->adminProfile()?->delete();
        }
    }

    private function generateUsername(string $name): string
    {
        $base = preg_replace('/[^a-z0-9]/', '', strtolower($name));
        if ($base === '') {
            $base = 'user';
        }
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }
        return $username;
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
