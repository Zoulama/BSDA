<?php

namespace Provisioning\Centile;

class CallBarring
{
    public $applyToSMS;
    public $applyToVoiceCall;
    public $type;
    public $label;
    public $description;
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
        if ($this->$property == "@NULL")
            return null;

        return $this->$property;
    }
}
