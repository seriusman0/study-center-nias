<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $usersByRole = User::join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.name as role', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->get();

        $blogsByCabang = Blog::join('cabangs', 'blogs.cabang_id', '=', 'cabangs.id')
            ->whereNotNull('blogs.published_at')
            ->select('cabangs.nama as cabang', DB::raw('count(*) as total'))
            ->groupBy('cabangs.nama')
            ->get();

        $commentsPerMonth = Comment::select(
            DB::raw("strftime('%Y-%m', created_at) as month"),
            DB::raw('count(*) as total')
        )
            ->whereNull('deleted_at')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return response()->json([
            'users_by_role' => $usersByRole,
            'blogs_by_cabang' => $blogsByCabang,
            'comments_per_month' => $commentsPerMonth,
            'total_users' => User::count(),
            'total_blogs' => Blog::whereNotNull('published_at')->count(),
            'total_comments' => Comment::whereNull('deleted_at')->count(),
        ]);
    }
}
