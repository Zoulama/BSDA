<?php

namespace Provisioning\Centile;

class Site
{
    const DEFAULT_NAME = 'default';
    const AREA_CODE_PREFIX = '33';

    public $defaultNetworkDomain;
    public $name;
    public $externalCallLimit;
    public $localGateway;
    public $isDefaultSite;
    public $location;

    public function __construct($params)
    {
        foreach ($params as $param => $value) {
            if ($value === "@NULL") {
                $this->$param = null;
            }
            elseif ($value === "false")
                $this->$param = false;
            elseif ($value === "true")
                $this->$param = true;
            else
                $this->$param = $value;
        }
    }

    public function __get($property)
    {
        if ($this->$property == "@NULL")
            return null;

        return $this->$property;
    }
}
