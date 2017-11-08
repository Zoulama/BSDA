<?php

namespace Provisioning\Centile;

class AdministrativeDomain
{
    public $type;
    public $parentAdmtiveDomain;
    public $name;

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
        return $this->$property;
    }
}
