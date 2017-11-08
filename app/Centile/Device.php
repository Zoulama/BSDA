<?php

namespace Provisioning\Centile;

use Provisioning\CentilePrestationTypes;
use Provisioning\ComptaPrestation as Prestation;
use CentileENT;
use DB;
use Illuminate\Support\Facades\Log;

class Device
{
    public $dynamicNattedIpAddress;
    public $staticNetworkIp;
    public $model;
    public $specificNetworkDomain;
    public $password;
    public $label;
    public $encryptionKey;
    public $staticNetworkGw;
    public $physicalID;
    public $pvNetworkMode;
    public $dynamicInternalIpAddress;
    public $site;
    public $staticNetworkMask;
    public $preferedCodec;
    public $extension;

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

    public static function find($context, $mac)
    {
        if (!$mac)
            return null;

        return CentileENT::getDevice($context, $mac);
    }

    public function getFormattedPhysicalId($separator = ':')
    {
        return implode($separator, str_split($this->physicalID, 2));
    }

    public function findPrestation(Prestation $parentPrestation)
    {
        return Prestation::where('type', CentilePrestationTypes::CENTREX_DEVICE)
            ->where('linkedWith', $parentPrestation->getId())
            ->where('valeur', $this->getFormattedPhysicalId())
            ->whereNull('validTill')
            ->first();
    }

    public function terminatePrestation(Prestation $parentPrestation)
    {
        if ($prestation = $this->findPrestation($parentPrestation))
            $prestation->terminate();
    }

    public function withDeviceModel()
    {
        $model = CentileENT::getDeviceModel($this->model);
        $this->deviceModel = $model;
        return $this;
    }

    public function withTerminals($context)
    {
        $this->terminals = CentileENT::getTerminals($context, $this->physicalID);
        return $this;
    }

    public function withDeviceManufacturerStatus($context)
    {
        $this->manufacturerStatus = CentileENT::getDeviceManufacturerStatus($context, $this->physicalID);
        return $this;
    }

    public function withLines($context, $terminal = 1)
    {
        $ret = [];
        $lines = CentileENT::getLines($context, $this->physicalID, $terminal);
        foreach ($lines as $line)
            $ret[$line->lineNumber] = $line;
        $this->lines = array_values($ret);
        return $this;
    }

    public static function existingMacAddresses()
    {
        return DB::connection('istra')
            ->table('DEVICE')
            ->select('PHYSICALID')
            ->pluck('PHYSICALID')
            ->toArray();
    }
}
