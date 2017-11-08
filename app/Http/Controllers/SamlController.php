<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;
use Provisioning\Helpers\Saml\Saml;
use Provisioning\Http\Requests;
use Provisioning\Http\Controllers\Controller;

class SamlController extends Controller
{
    /**
     * Logout Saml
     * @return void
     */
    public function logout()
    {
        Saml::samlLogout();
    }

}
