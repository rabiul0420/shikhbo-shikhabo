<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            abort_if(\Illuminate\Support\Facades\Auth::guard('web')->check(), 403, 'Please sign in with an admin account.');
            return redirect()->route('admin.login');
        }

        if (! $request->user() instanceof \App\Models\Admin || ! $request->user()->is_admin) {
            abort(403, 'Only admin users can access this page.');
        }

        if ($request->routeIs('admin.index') && $request->user()->adminRole() === 'content_editor') {
            return redirect()->route('admin.blogs.index');
        }

        abort_unless($request->user()->canAccessAdminRoute($request->route()->getName() ?? ''), 403);

        $exam = $request->route('exam');
        if ($exam instanceof \App\Models\Exam) {
            abort_unless($exam->accessibleByAdmin($request->user()), 403);
        }

        return $next($request);
    }
}
