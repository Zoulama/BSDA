<?php

namespace Provisioning\Centile;

use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;

class Service
{
    const ISTRA_SERVICE_CALL_QUEUING_LABEL = 'CallQueuingService';
    const ISTRA_SERVICE_CALL_QUEUING_NAME = 'CallQueuingService';

    public $label;
    public $serviceName;

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

    public function getType()
    {
        if ($this->serviceName == Service::ISTRA_SERVICE_CALL_QUEUING_NAME)
            return CentilePrestationTypes::CENTREX_CALLQUEUING;
    }

    public function findPrestation(Prestation $parentPrestation)
    {
        return Prestation::where('type', $this->getType())
            ->where('linkedWith', $parentPrestation->getId())
            ->whereNull('validTill')
            ->first();
    }

    public function terminatePrestation(Prestation $parentPrestation)
    {
        if ($prestation = $this->findPrestation($parentPrestation))
            $prestation->terminate();
    }

}
