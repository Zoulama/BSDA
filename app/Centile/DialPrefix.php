<?php

namespace Provisioning\Centile;

class DialPrefix
{
    public $prefix;
    public $externalPrefix;
    public $trunkAddress;
    public $name;
    public $gateway;
    public $isDefaultDialPrefix;
    public $isEnum;
    public $numberCompleteCheck;

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
