<?php

namespace Provisioning\Centile;
use DB;
use CentileENT;

class UserExtension
{
    public $number;
    public $label;
    public $maxSoftphone;
    public $dnd;
    public $hiddenDirectory;
    public $hideCallerID;
    public $isACDAgent;
    public $dropCallWaiting;
    public $softphoneG729;
    public $isVideoSoftphone;
    public $isWebSoftphone;
    public $isClickToCallFEAllowed;
    public $isSwitchBoardAllowed;
    public $isTapiDriverAllowed;
    public $callsRecorded;
    public $isWebCallBack;
    public $isWebVoiceCard;
    public $urlPhoto;
    public $hasAcdCallPad;
    public $maxTerminalConnectionsOnSoftphone;
    public $webIdentity;
    public $isCTIMonitoringOfPersonalCallsAllowed;
    public $pickupAndBLFCallerIDPolicy;
    public $isMyIstra;
    public $isMultiStageDialing;
    public $allowConferenceMaster;
    public $isUssd;
    public $isWebXpad;
    public $isXpadApp;
    public $hasOnTheFlyRecordingRight;
    public $isCallBackFeatureAllowed;
    public $isExternalConferenceAllowed;
    public $protectedFields;

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

    public static function getAssignedExtensions($context)
    {
        return DB::connection('istra')
            ->table('INTERNALADDRESS')
            ->join('ADMTIVEDOMAIN', 'ADMTIVEDOMAIN.ADMTIVEDOMAINID', '=', 'INTERNALADDRESS.OWNERADMTIVEDOMAINID')
            ->where('ADMTIVEDOMAIN.DOMAINNAME', $context)
            ->where('ADDRESSNUMBER', 'not like', '~%')
            ->whereNotNull('IUSERID')
            ->get()
            ->pluck('ADDRESSNUMBER')
            ->toArray();
    }

    public static function exists($context, $extension)
    {
        if (!CentileENT::getUserExtension($context, $extension))
            return false;

        return true;
    }
}
