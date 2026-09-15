<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [\App\Http\Middleware\SelectAccountGuard::class]);
        $middleware->alias([
            'auth' => Authenticate::class,
            'admin' => EnsureUserIsAdmin::class,
            'super_admin' => EnsureUserIsSuperAdmin::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin*')
            ? route('admin.login')
            : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $redirectTo = $request->input('redirect_to');
            $query = $redirectTo && str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//')
                ? ['redirect_to' => $redirectTo]
                : [];

            return redirect()
                ->route($request->is('admin*') ? 'admin.login' : 'login', $query)
                ->with('status', 'Your session expired. Please login again.');
        });
    })->create();
