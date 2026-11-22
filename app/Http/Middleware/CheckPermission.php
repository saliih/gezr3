<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Usage : ->middleware('perm:clients,edit') */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'view'): Response
    {
        $user = $request->user();

        if (!$user || !$user->canDo($module, $action)) {
            abort(403, 'ليست لديك صلاحية للقيام بهذه العملية.');
        }

        return $next($request);
    }
}
