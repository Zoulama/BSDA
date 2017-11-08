<?php

namespace Provisioning\Centile;

use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;
use CentileENT;

class User
{
    public $login;
    public $password;
    public $extension;
    public $firstName;
    public $lastName;
    public $emails;
    public $title;
    public $homeNumber;
    public $mobileNumber;
    public $postalAddress;
    public $manualCustomCallerID;
    public $department;
    public $isACDSupervisor;
    public $vmNotification;
    public $externalUserID;
    public $manualPresenceState;
    public $presenceStateChoice;
    public $defaultCalendarPresenceState;
    public $faxExtensionNumber;
    public $nickName;
    public $officeReferenceID;
    public $costCenter;
    public $contactInformation;
    public $duties;
    public $location;
    public $additionalExplanations;
    public $corporateUserId;
    public $additionalInfo;
    public $accessCtrlSystemPersonId;
    public $accessCtrlSystemStatus;
    public $manualCustomPresenceState;
    public $privateBillOnRestrictedCallFromMobilePhone;
    public $customCallerIDPolicy;
    public $extraCustomCallerIDList;
    public $allowPresenceStateUpdateFromUI;
    public $professionalPostalAddress;
    public $forceTwoStepAuthentication;
    public $customCallerIDMapping;
    public $softphone;

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

    public static function find($login)
    {
        if (!$login)
            return null;

        return CentileENT::getUser($login);
    }

    public function findPrestation(Prestation $parentPrestation)
    {
        return Prestation::where('type', CentilePrestationTypes::CENTREX_USER)
            ->where('linkedWith', $parentPrestation->getId())
            ->where('valeur', $this->login)
            ->whereNull('validTill')
            ->first();
    }

    public function terminatePrestation(Prestation $parentPrestation)
    {
        if ($prestation = $this->findPrestation($parentPrestation))
            $prestation->terminate();
    }

    public function withoutPassword()
    {
        $user = $this;
        unset($user->password);
        return $user;
    }

    public function withPSTNs($context)
    {
        $this->pstns = [];
        if ($this->extension) {
            foreach (CentileENT::getPstnNumbersByUserExtension($context, $this->extension) as $pstn)
                $this->pstns[] = $pstn->number;
        }
        return $this;
    }

    public function withDevices($context, $withTerminals = true)
    {
        if (!$this->extension) {
            $this->devices = [];
            return $this;
        }

        $devices = CentileENT::listDevicesUsedByExtension($context, $this->extension);
        $ret = [];

        if ($withTerminals) {
            $terminalsGrouped = [];
            $terminals = CentileENT::getTerminals($context, null, $this->extension);
            foreach ($terminals as $terminal) {
                $terminalsGrouped[$terminal->devicePhysicalID][] = $terminal;
            }

            foreach ($terminalsGrouped as $mac => $group) {
                $device = $this->findDeviceWithMac($devices, $mac);
                $device = $device->withDeviceModel();
                $device->terminals = $terminalsGrouped[$mac];
                $ret[] = $device;
            }
        } else {
            foreach ($devices as $device)
                $ret[] = $device->withDeviceModel();
        }

        $this->devices = $ret;

        return $this;
    }

    public function withCallBarrings($context)
    {
        $this->callBarrings = CentileENT::listAllCallBarringUsedByExtension($context, $this->extension);
        return $this;
    }

    public function withLogicalTerminals($context)
    {
        $this->logicalTerminals = CentileENT::getLogicalTerminalsByExtension($context, $this->extension);
        return $this;
    }

    protected function findDeviceWithMac(array $devices, $mac)
    {
        foreach ($devices as $device)
            if ($device->physicalID == $mac)
                return $device;

        return null;
    }

    public function withForwardings($context)
    {
        $this->forwardings = CentileENT::getForwardingsByAssignedTo($context, $this->extension);
        return $this;
    }

    public function withSoftPhone($context)
    {
        if (!$this->extension) return null;
        $userExtension = CentileENT::getUserExtension($context, $this->extension);
        $this->softphone = ($userExtension->maxSoftphone != 0) ? true : false;
        return $this;
    }
}
