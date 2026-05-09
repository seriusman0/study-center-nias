<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleAdminController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:roles,name'],
            'description' => 'nullable|string|max:255',
        ]);

        Role::create($data);
        return back()->with('success', 'Role ditambahkan.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => 'nullable|string|max:255',
        ]);

        $role->update($data);
        return back()->with('success', 'Role diperbarui.');
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->input('permissions', []));
        return back()->with('success', 'Permission untuk role ' . $role->name . ' diperbarui.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['admin', 'student', 'mentor', 'guest', 'fulltimer'])) {
            return back()->with('success', 'Role bawaan tidak dapat dihapus.');
        }
        $role->delete();
        return back()->with('success', 'Role dihapus.');
    }
}
