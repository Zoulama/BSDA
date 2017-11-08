<?php

namespace Provisioning\Http\Middleware;

use Closure;
use AuthUser;

class CanAccessPrestation
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

        if (in_array($request->route('prestation')->getGroupId(), AuthUser::getUserGroups()))
            return $next($request);

        abort(403);
    }
}
