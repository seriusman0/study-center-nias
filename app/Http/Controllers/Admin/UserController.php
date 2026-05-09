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
        $query = User::with(['role:id,name', 'cabang:id,nama'])
            ->when($request->role, fn($q) => $q->whereHas('role', fn($r) => $r->where('name', $request->role)))
            ->when($request->cabang_id, fn($q) => $q->where('cabang_id', $request->cabang_id));

        return response()->json($query->paginate(20));
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $role = Role::where('name', $validated['role'])->first();
        $user->update(['role_id' => $role->id]);

        return response()->json($user->load('role'));
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
