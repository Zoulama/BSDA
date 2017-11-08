<?php

namespace Provisioning\Centile;

use CentileENT;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;

class SpeedDial
{
    public $extension;
    public $label;
    public $hiddenDirectory;
    public $externalDestination;
    public $isWebCallBack;
    public $isWebVoiceCard;
    public $urlPhoto;
    public $webIdentity;
    public $billingCallType;
    public $plmnNumbers;
    public $pstnNumbers;

    public function __construct($params)
    {
        foreach ($params as $param => $value) {
            if ($value === "@NULL")
                $this->$param = null;
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
        if ($this->$property == '@NULL') {
            return null;
        }

        return $this->$property;
    }

    public static function find($context, $extension)
    {
        if (!$extension)
            return null;

        return CentileENT::getSpeedDial($context, $extension);
    }

    public static function exists($context, $extension)
    {
        $speeddial = CentileENT::getSpeedDial($context, $extension);
        if ($speeddial)
            return true;

        return false;
    }

    public function findPrestation(Prestation $parentPrestation)
    {
        return Prestation::where('type', CentilePrestationTypes::CENTREX_SPEED_DIAL)
            ->where('linkedWith', $parentPrestation->getId())
            ->where('valeur', $this->extension)
            ->whereNull('validTill')
            ->first();
    }

    public function terminatePrestation(Prestation $parentPrestation)
    {
        if ($prestation = $this->findPrestation($parentPrestation))
            $prestation->terminate();
    }
}
