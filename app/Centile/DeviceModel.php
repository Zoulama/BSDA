<?php

namespace Provisioning\Centile;

use CentileENT;

class DeviceModel
{
    public $maxTerminals;
    public $isDevicePasswordUsed;
    public $nsupportLineDefault;
    public $supportSpeedDial;
    public $isMacAddressNeeded;
    public $msupportSRST;
    public $msupportBLF;
    public $supportSRST;
    public $supportLineDefault;
    public $supportNetworkPv;
    public $codecsSupported;
    public $supportFax;
    public $msupportLine;
    public $isIPAddressNeeded;
    public $linesSupported;
    public $manufacturer;
    public $name;
    public $supportLine;
    public $extraConfFileTagsEditable;
    public $label;
    public $devicePasswordLength;
    public $isPublicDeviceModel;
    public $defaultConfFileTagsEditable;
    public $supportBLF;
    public $isRegisterIDNeeded;


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

    public static function getLabelFromName($name)
    {
        if ($deviceModel = CentileENT::getDeviceModel($name))
            return $deviceModel->label;

        return null;
    }

    public function getMaxLinesSupported()
    {
        if (preg_match('/^(\d+)(;\d+)*$/', $this->linesSupported, $matches))
            return $matches[1];

        return 0;
    }
}
