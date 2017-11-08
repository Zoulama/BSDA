<?php

namespace Provisioning\Centile;

use CentileENT;

class Forwarding
{
    public $activated;
    public $assignedTo;
    public $externalDestination;
    public $filter;
    public $forwardingID;
    public $internalDestination;
    public $label;
    public $labelledDestination;
    public $noAnswerDelay;
    public $parent;
    public $specificCaller;
    public $timeFilter;
    public $type;

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

    public static function find($context, $forwardingId)
    {
        if (!$forwardingId)
            return null;

        return CentileENT::getForwarding($context, $forwardingId);
    }
}
