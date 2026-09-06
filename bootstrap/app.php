<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: ['*']);

        $middleware->web(prepend: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\BlockMentorFromAdmin::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->redirectGuestsTo(fn () => url('/'));
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if ($user && $user->isAdmin()) {
                return route('admin.dashboard');
            }
            if ($user && $user->hasRole(['student', 'college', 'scholarship_teenager', 'mentor'])) {
                return route('beranda');
            }
            return url('/');
        });

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            if (in_array($status, [403, 404, 405], true)
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Auth\Access\AuthorizationException
            ) {
                if (auth()->check()) {
                    return redirect()->route('beranda');
                }
                return redirect()->to('/');
            }

            return null;
        });
    })->create();
