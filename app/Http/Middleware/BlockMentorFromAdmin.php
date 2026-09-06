<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockMentorFromAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('mentor') && str_starts_with($request->getPathInfo(), '/admin')) {
            return redirect()->route('beranda');
        }

        return $next($request);
    }
}
