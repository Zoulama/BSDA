<?php

namespace Provisioning\Centile;

use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;

class IVRService
{
    const ISTRA_IVR_VOICEMAIL_LABEL = 'EnterpriseVM';
    const ISTRA_IVR_CONFERENCE_LABEL = 'Conference';
    const DEFAULT_VOICEMAIL_LABEL = 'Messagerie Vocale';
    const DEFAULT_CONFERENCE_LABEL = 'Conférence';

    public $extension;
    public $isWebCallback;
    public $webIdentity;
    public $sipServer;
    public $serviceType;
    public $hiddenDirectory;
    public $isDefaultByServiceType;
    public $label;
    public $isWebVoiceCard;
    public $ivrName;
    public $urlPhoto;

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
        if ($this->ivrName == 'EnterpriseVM')
            return CentilePrestationTypes::CENTREX_VOICEMAIL;
        elseif ($this->ivrName == 'Conference')
            return CentilePrestationTypes::CENTREX_CONFERENCE;
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

    public static function getDefaultVoicemailLabel()
    {
        return self::DEFAULT_VOICEMAIL_LABEL;
    }

    public static function getDefaultConferenceLabel()
    {
        return self::DEFAULT_CONFERENCE_LABEL;
    }
}
