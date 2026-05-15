<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles:id,name', 'cabang:id,nama'])
            ->when($request->role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $request->role)))
            ->when($request->cabang_id, fn($q) => $q->where('cabang_id', $request->cabang_id))
            ->when($request->q, function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(fn($w) => $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('username', 'like', $term));
            });

        return response()->json($query->paginate(20));
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('roles', 'cabang', 'studentProfile', 'mentorProfile', 'adminProfile'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password'     => 'nullable|string|min:5',
            'cabang_id'    => 'nullable|exists:cabangs,id',
            'role_names'   => 'nullable|array',
            'role_names.*' => 'exists:roles,name',
        ]);

        $userData = [
            'name'      => $data['name'],
            'username'  => $this->generateUsername($data['name']),
            'email'     => $data['email'] ?? null,
            'password'  => Hash::make($data['password'] ?? '12345'),
            'cabang_id' => $data['cabang_id'] ?? null,
            'is_active' => true,
        ];

        $user = User::create($userData);

        $roleNames = $data['role_names'] ?? ['student'];
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->all();
        $user->roles()->sync($roleIds);

        return response()->json($user->load('roles', 'cabang'), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'username'     => ['sometimes', 'string', 'max:50', 'regex:/^[a-z0-9]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email'        => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'     => 'nullable|string|min:5',
            'cabang_id'    => 'nullable|exists:cabangs,id',
            'is_active'    => 'nullable|boolean',
            'role_names'   => 'nullable|array',
            'role_names.*' => 'exists:roles,name',
        ]);

        $userData = collect($data)->only(['name', 'username', 'email', 'cabang_id'])->toArray();
        if (array_key_exists('is_active', $data)) $userData['is_active'] = (bool) $data['is_active'];
        if (!empty($data['password'])) $userData['password'] = Hash::make($data['password']);

        $user->update($userData);

        if (isset($data['role_names'])) {
            $ids = Role::whereIn('name', $data['role_names'])->pluck('id')->all();
            $user->roles()->sync($ids);
        }

        return response()->json($user->load('roles', 'cabang'));
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'role'    => ['nullable', 'exists:roles,name'],
        ]);

        $names = $validated['roles'] ?? (isset($validated['role']) ? [$validated['role']] : []);
        $ids = Role::whereIn('name', $names)->pluck('id')->all();
        $user->roles()->sync($ids);

        return response()->json($user->load('roles'));
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
        }
        $user->delete();
        return response()->json(['message' => 'User dihapus.']);
    }

    private function generateUsername(string $name): string
    {
        $base = preg_replace('/[^a-z0-9]/', '', strtolower($name));
        if ($base === '') $base = 'user';
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }
        return $username;
    }
}
