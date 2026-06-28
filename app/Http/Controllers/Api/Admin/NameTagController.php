<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NameTagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $studentRoleId = Role::where('name', 'student')->value('id');

        $query = User::with(['studentProfile', 'cabang:id,nama', 'roles:id,name'])
            ->where('is_active', true);

        if ($studentRoleId) {
            $query->whereHas('roles', fn($q) => $q->where('roles.id', $studentRoleId));
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn($w) => $w->where('name', 'like', $term)
                ->orWhere('username', 'like', $term));
        }
        if ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        return response()->json(['data' => $query->orderBy('name')->paginate(50)]);
    }

    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_ids'    => 'required|array|min:1',
            'user_ids.*'  => 'integer|exists:users,id',
            'width_cm'    => 'nullable|numeric|min:5|max:15',
            'height_cm'   => 'nullable|numeric|min:3|max:15',
        ]);

        $width  = (float) ($data['width_cm']  ?? 8.5);
        $height = (float) ($data['height_cm'] ?? 5.5);

        $students = User::with('studentProfile', 'cabang:id,nama')
            ->whereIn('id', $data['user_ids'])
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'avatar', 'cabang_id']);

        return response()->json([
            'width_cm'  => $width,
            'height_cm' => $height,
            'students'  => $students,
        ]);
    }
}
