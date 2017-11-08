<?php

namespace Provisioning\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Lang;

class ApiAdmin
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
        if (!in_array($request->input('apitoken'), Config("Provisioning.authorized_keys.apiadmin"))) {
            return response()->json(array('error' => Lang::get('api/message.token_not_authorized') ),403);
        }
        return $next($request);
    }
}
