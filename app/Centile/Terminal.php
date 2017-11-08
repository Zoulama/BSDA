<?php

namespace Provisioning\Centile;
use CentileENT;

class Terminal
{
    public $label;
    public $physicalState;
    public $extension;
    public $devicePhysicalID;
    public $maxTerminalConnectionOnOutgoingCall;
    public $preferredCodecs;
    public $fax;
    public $devicePort;
    public $scenario;
    public $maxTerminalConnection;

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

    public function withLines($context)
    {
        $ret = [];
        $lines = CentileENT::getLines($context, $this->devicePhysicalID, $this->devicePort);
        foreach ($lines as $line)
            $ret[$line->lineNumber] = $line;
        $this->lines = $ret;
        return $this;
    }

    public static function find($context, $mac, $port)
    {
        return CentileENT::getTerminal($context, $mac, $port);
    }
}
