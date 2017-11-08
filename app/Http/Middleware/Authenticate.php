<?php

namespace Provisioning\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Provisioning\Helpers\Auth\AuthUser;
use Provisioning\Helpers\Saml\Saml;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (env('APP_ENV') == 'local')
            return $next($request);

        if (!Saml::samlLogged()) {
            Saml::samlLogin();
        } else {
            if (!AuthUser::hasApplication(env('APPTOKEN'))) {
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}
