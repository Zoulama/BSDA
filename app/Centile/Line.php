<?php

namespace Provisioning\Centile;
use CentileENT;

class Line
{
    const TYPE_LINE = 0;
    const TYPE_MONITORING = 1;
    const TYPE_SPEED_DIAL = 2;
    const TYPE_EXTERNAL_REGISTRATION = 3;

    public $label;
    public $devicePhysicalID;
    public $type;
    public $extension;
    public $lineNumber;
    public $externalsd;
    public $passwordSRST;
    public $userSRS;

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

    public static function find($context, $mac, $lineNumber)
    {
        return CentileENT::getLine($context, $mac, $lineNumber);
    }
}
