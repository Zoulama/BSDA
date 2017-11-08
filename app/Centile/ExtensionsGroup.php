<?php

namespace Provisioning\Centile;

use CentileENT;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;

class ExtensionsGroup
{
    public $label;
    public $extension;
    public $pstnNumbers;
    public $groupPassword;
    public $hiddenDirectory;
    public $maxActiveConnections;
    public $queueSize;
    public $syndicalPause;
    public $maxCycles;
    public $busyPolicy;
    public $callsRecorded;
    public $isWebCallBack;
    public $isWebVoiceCard;
    public $urlPhoto;
    public $webIdentity;
    public $ussdDirectoryLookupPolicy;
    public $groupsDisplayedNumberPolicy;
    public $groupsDisplayedLabelsTerminalsPolicy;
    public $groupsDisplayedLabelsUssdPolicy;

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
        if ($this->$property == '@NULL') {
            return null;
        }

        return $this->$property;
    }

    public function withExtensions($context)
    {
        $this->extensions = CentileENT::listExtensionsInGroupAddress($context, $this->extension);
        return $this;
    }

    public static function exists($context, $extension)
    {
        $groups = CentileENT::getExtensionGroups($context);
        foreach ($groups as $group) {
            if ($group->extension == $extension)
                return true;
        }

        return false;
    }

    public static function find($context, $extension)
    {
        if (!$extension)
            return null;

        return CentileENT::getExtensionGroup($context, $extension);
    }

    public function findPrestation(Prestation $parentPrestation)
    {
        return Prestation::where('type', CentilePrestationTypes::CENTREX_EXT_GROUP)
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
