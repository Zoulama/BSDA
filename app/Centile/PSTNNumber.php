<?php

namespace Provisioning\Centile;

use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;
use Provisioning\Centile\Enterprise;
use Provisioning\Centile\Extension;
use CentileENT;

class PSTNNumber
{
    public $isDefault;
    public $rangeLabel;
    public $activationStatus;
    public $location;
    public $label;
    public $admtiveDomain;
    public $number;
    public $type;
    public $pbxTrunking;
    public $numberExtension;

    public function __construct($params)
    {
        foreach ($params as $param => $value) {
            if ($value === '@NULL') {
                $this->$param = null;
            } elseif ($value === 'false') {
                $this->$param = false;
            } elseif ($value === 'true') {
                $this->$param = true;
            } else {
                $this->$param = $value;
            }
        }
    }

    public function __get($property)
    {
        if ($this->$property == '@NULL') {
            return null;
        }

        return $this->$property;
    }

    public function findPrestation(Prestation $parentPrestation)
    {
        return Prestation::where(function ($query) {
            $query->where('type', CentilePrestationTypes::CENTREX_PSTN)
                ->orWhere('type', CentilePrestationTypes::TRUNK_PSTN);
            })
            ->where('linkedWith', $parentPrestation->getId())
            ->where('valeur', $this->number)
            ->whereNull('validTill')
            ->first();
    }

    public function terminatePrestation(Prestation $parentPrestation)
    {
        if ($prestation = $this->findPrestation($parentPrestation))
            $prestation->terminate();
    }

    public function newPrestation(Prestation $parentPrestation, $type = CentilePrestationTypes::CENTREX_PSTN)
    {
        return Prestation::centileFactory(
            $type,
            date('Y-m-d'),
            $parentPrestation->getClientId(),
            $parentPrestation->getGroupId(),
            $parentPrestation->getCentileResellerContext(),
            $parentPrestation->getCentileContext(),
            $this->number,
            null,
            Prestation::STATUS_COMPLETION,
            $parentPrestation->getId()
        );
    }

    public function withPilotNumber(Enterprise $enterprise)
    {
        if ($enterprise->pilotNumber == $this->number)
            $this->isPilotNumber = true;
        else
            $this->isPilotNumber = false;
        return $this;
    }

    public function withExtensionDetails($context)
    {
        $this->extensionDetails = Extension::mapLinkedObjectsToExtensions($context, [$this->numberExtension], true, true);
        return $this;
    }

    public static function find($number)
    {
        if (!$number)
            return null;

        return CentileENT::getPstnNumber($number);
    }
}
