<?php

namespace Provisioning\Centile;

class Administrator
{
    public $hasSuFunctionality;
    public $appLanguage;
    public $hasAcdStatsAccess;
    public $cdrDigitMask;
    public $appMaxInactiveInterval;
    public $callMixesDigitMask;
    public $appFirstWeekDay;
    public $hasCallMixesAccess;
    public $login;
    public $hasWebAdminAccess;
    public $hasCdrAccess;
    public $lastName;
    public $ownerAdmtiveDomain;
    public $appTimeFormat;
    public $firstName;
    public $accessRightsType;
    public $appDateFormat;

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
