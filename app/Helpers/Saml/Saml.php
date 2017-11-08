<?php

namespace Provisioning\Helpers\Saml;

use Illuminate\Support\Facades\Facade;

class Saml extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'LibSimpleSamlphp';
    }

}