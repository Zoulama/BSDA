<?php

namespace Provisioning\Http\Middleware;

use Closure;
use AuthUser;

class CanAccessClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (env('APP_ENV') == 'local')
            return $next($request);

        if (in_array($request->route('client')->getGroupId(), AuthUser::getUserGroups()))
            return $next($request);

        abort(403);
    }
}
