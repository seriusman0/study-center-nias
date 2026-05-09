<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles:id,name', 'cabang:id,nama'])
            ->when($request->role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $request->role)))
            ->when($request->cabang_id, fn($q) => $q->where('cabang_id', $request->cabang_id));

        return response()->json($query->paginate(20));
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
        $user->delete();
        return response()->json(['message' => 'User dihapus.']);
    }
}
