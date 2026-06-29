<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionAdminController extends Controller
{
    public function index()
    {
        $permissions = Permission::withCount('roles')->orderBy('name')->get();
        return view('admin.permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_\.]+$/', 'unique:permissions,name'],
            'description' => 'nullable|string|max:255',
        ]);

        Permission::create($data);
        return back()->with('success', 'Permission ditambahkan.');
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_\.]+$/', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'description' => 'nullable|string|max:255',
        ]);

        $permission->update($data);
        return back()->with('success', 'Permission diperbarui.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return back()->with('success', 'Permission dihapus.');
    }
}
