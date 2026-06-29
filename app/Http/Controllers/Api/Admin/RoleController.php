<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions:id,name')->withCount('users')->orderBy('name')->get();
        return response()->json(['data' => $roles]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:roles,name'],
            'description' => 'nullable|string|max:255',
        ]);
        $role = Role::create($data);
        return response()->json(['data' => $role], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => 'nullable|string|max:255',
        ]);
        $role->update($data);
        return response()->json(['data' => $role]);
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);
        return response()->json(['data' => $role->load('permissions:id,name')]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if (in_array($role->name, ['admin', 'student', 'mentor', 'guest', 'fulltimer'])) {
            return response()->json(['message' => 'Role bawaan tidak dapat dihapus.'], 422);
        }
        $role->delete();
        return response()->json(['message' => 'Role dihapus.']);
    }
}
