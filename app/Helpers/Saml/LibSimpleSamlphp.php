<?php

namespace Provisioning\Helpers\Saml;

class LibSimpleSamlphp
{

    protected $path;
    protected $sp;
    protected $saml;

    public function __construct()
    {
        $this->saml = $this->setupSimpleSaml();

    }

    protected function setupSimpleSaml()
    {
        $this->path = env('SP_PATH');
        $this->sp = env('DEFAULT_SP');
        require_once($this->path);
        return new \SimpleSAML_Auth_Simple($this->sp);
    }

    public function samlLogged()
    {
        return $this->saml->isAuthenticated();
    }

    public function samlLogin()
    {
        $this->saml->requireAuth();
    }

    public function samlLogout()
    {
        $this->saml->logout(env('LOGOUT_TARGET'));
    }

    public function getSamlAttributes()
    {
        return $this->saml->getAttributes();
    }

}