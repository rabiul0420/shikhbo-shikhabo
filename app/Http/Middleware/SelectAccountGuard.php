<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SelectAccountGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminRoute = $request->is('admin', 'admin/*')
            || in_array('admin', $request->route()?->gatherMiddleware() ?? [], true);
        Auth::shouldUse($adminRoute ? 'admin' : 'web');
        return $next($request);
    }
}
