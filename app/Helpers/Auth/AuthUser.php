<?php

namespace Provisioning\Helpers\Auth;

use Illuminate\Support\Facades\Facade;

class AuthUser extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'AuthUserClass';
    }

}