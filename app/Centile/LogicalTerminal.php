<?php

namespace Provisioning\Centile;

use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;
use CentileENT;

class LogicalTerminal
{
    public $maxConnectionOnOutgoingCall;
    public $gateway;
    public $callBackControl;
    public $physicalID;
    public $logicalIDs;
    public $label;
    public $maxTerminalConnection;
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

    public function terminatePrestation(Prestation $parentPrestation)
    {
        $prestation = Prestation::where('type', CentilePrestationTypes::CENTREX_LOGICAL_TERMINAL)
            ->where('valeur', $this->physicalID)
            ->where('validTill', null)
            ->first();

        if ($prestation) {
            $prestation->validTill = date('Y-m-d');
            $prestation->save();
        }
    }

    public function newPrestation(Prestation $parentPrestation)
    {
        return Prestation::centileFactory(
            CentilePrestationTypes::CENTREX_LOGICAL_TERMINAL,
            date('Y-m-d'),
            $parentPrestation->getClientId(),
            $parentPrestation->getGroupId(),
            $parentPrestation->getCentileResellerContext(),
            $parentPrestation->getCentileContext(),
            $this->physicalID,
            'Remote Terminal for ext: ' . $this->extension . ')',
            Prestation::STATUS_COMPLETION,
            $parentPrestation->getId()
        );
    }

    public static function find($context, $physicalId)
    {
        if (!$physicalId)
            return null;

        return CentileENT::getLogicalTerminal($context, $physicalId);
    }
}
