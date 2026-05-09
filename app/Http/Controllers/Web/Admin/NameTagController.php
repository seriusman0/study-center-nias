<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class NameTagController extends Controller
{
    public function index(Request $request)
    {
        $studentRoleId = Role::where('name', 'student')->value('id');

        $query = User::with(['studentProfile', 'cabang', 'roles'])
            ->where('is_active', true);

        if ($studentRoleId) {
            $query->whereHas('roles', fn($q) => $q->where('roles.id', $studentRoleId));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn($w) => $w->where('name', 'like', $term)
                ->orWhere('username', 'like', $term)
                ->orWhereHas('studentProfile', fn($s) => $s->where('school_name', 'like', $term)
                    ->orWhere('grade_class', 'like', $term)));
        }

        if ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        $students = $query->orderBy('name')->paginate(50)->withQueryString();
        $cabangs = Cabang::orderBy('nama')->get();

        return view('admin.nametags.index', compact('students', 'cabangs'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'user_ids'    => 'required|array|min:1',
            'user_ids.*'  => 'integer|exists:users,id',
            'width_cm'    => 'nullable|numeric|min:5|max:15',
            'height_cm'   => 'nullable|numeric|min:3|max:15',
            'auto_print'  => 'nullable|boolean',
        ]);

        $width  = (float) ($data['width_cm']  ?? 8.5);
        $height = (float) ($data['height_cm'] ?? 5.5);
        $autoPrint = (bool) ($request->boolean('auto_print'));

        $students = User::with('studentProfile')
            ->whereIn('id', $data['user_ids'])
            ->orderBy('name')
            ->get();

        return view('admin.nametags.generate', compact('students', 'width', 'height', 'autoPrint'));
    }
}
