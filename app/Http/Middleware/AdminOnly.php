<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Réserve une route aux ROLE_ADMIN / ROLE_SUPER_ADMIN. */
class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAdmin()) {
            abort(403, 'هذه الصفحة مخصصة للمديرين.');
        }

        return $next($request);
    }
}
