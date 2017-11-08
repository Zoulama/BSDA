<?php

namespace Provisioning\Centile;

use Provisioning\Centile\Site;
use Provisioning\Firewall;
use Illuminate\Support\Facades\Log;

use CentileENT;
use DB;

class Enterprise
{
    const LIMITATION_NONE = 0;
    const LIMITATION_PARTIAL = 1;
    const LIMITATION_TOTAL = 2;

    public $allowPrivateBillOnRestrictedCallFromMobilePhone;
    public $pilotNumber;
    public $name; // max length: 20
    public $allowedScenariosID;
    public $internalDialplan;
    public $defaultDialPrefix;
    public $ownerAdmtiveDomain;
    public $ipbx;
    public $maxExternalConnections;
    public $allowLdapOnClientApps;
    public $pnTypeAsDefault;
    public $externalCommunityID; //max length: 40
    public $groupsDisplayedNumberPolicy;
    public $ussDirectoryLookupPolicy;
    public $billingAccountCustom2; //max length: 50
    public $allowPhotoCustomization;
    public $enabledSynchroCalendarPresenceState;
    public $groupsDisplayedLabelsUssdPolicy;
    public $failOnNoAnswerDelay;
    public $receptionist;
    public $defaultSite;
    public $cliOnCallsToMobile;
    public $fsLogoutTime;
    public $billingAccountCustom1; //max length: 50
    public $customCallerIDPolicy;
    public $defaultLanguage;
    public $exportDirectory;
    public $billedPresenceStates; //max length: 1024
    public $groupsDisplayedLabelsTerminalsPolicy;
    public $useEmailSignaturesManager;
    public $activated;
    public $maxIVRConnections;
    public $fsLoginDuration;
    public $maxMembersInExtensionGroup;
    public $fullName; //max length: 128
    public $countryCode; //max length: 4
    public $operatorPrefix; //max length: 10
    public $maxMembersInUnissonGroup;
    public $ussdDirectoryLookupPolicy;
    public $label;

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

    public function getDialPlan()
    {
        return $this->internalDialplan;
    }

    public static function getDefaultResellerContext($clientId)
    {
        return 'CENTREX-' . $clientId;
    }

    public static function getDefaultContext($clientId, $prestationId)
    {
        return 'CENTREX-' . $prestationId;
    }

    public function withRestriction()
    {
        $this->restriction = (string) DB::connection('istra')
            ->table('COMMUNITY')
            ->where('NAME', $this->name)
            ->select('LIMITATION')
            ->value('LIMITATION');

        return $this;
    }

    public function withUsedDialPlanMasks()
    {
        $extensions = Extension::getAssignedExtensions($this->name);
        $dialPlan = new DialPlan($this->internalDialplan);
        $masks = $dialPlan->getMasks();
        $used = [];

        foreach ($extensions as $extension) {
            foreach ($masks as $mask) {
                if ((strlen($extension) == $mask->getLength()) && starts_with($extension, $mask->getPrefix()))
                    $used[] = $mask->getString();
            }
        }

        $this->usedDialPlanMasks = implode(';', array_unique($used));
        if (!empty($this->usedDialPlanMasks))
            $this->usedDialPlanMasks .= ';';
        return $this;
    }

    public function withDefaultSite()
    {
        $areaCode = CentileENT::getSite($this->name, Site::DEFAULT_NAME);
        $this->defaultSite = substr($areaCode->location, strlen(Site::AREA_CODE_PREFIX), strlen($areaCode->location));
        return $this;
    }

    public function withFirewall($context)
    {
        $this->firewalls = Firewall::getIpAddressesFromContext($context);
        return $this;
    }

    public function linkToPrestationId($prestationId)
    {
        $this->prestationId = $prestationId;
        return $this;
    }
}
