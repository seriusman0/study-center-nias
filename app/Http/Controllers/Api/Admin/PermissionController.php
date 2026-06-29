<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::withCount('roles')->orderBy('name')->get();
        return response()->json(['data' => $permissions]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_\.]+$/', 'unique:permissions,name'],
            'description' => 'nullable|string|max:255',
        ]);
        $permission = Permission::create($data);
        return response()->json(['data' => $permission], 201);
    }

    public function update(Request $request, Permission $permission): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_\.]+$/', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'description' => 'nullable|string|max:255',
        ]);
        $permission->update($data);
        return response()->json(['data' => $permission]);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();
        return response()->json(['message' => 'Permission dihapus.']);
    }
}
