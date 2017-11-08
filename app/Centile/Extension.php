<?php

namespace Provisioning\Centile;

use DB;
use Provisioning\Centile\DialPlan;
use CentileENT;
use Provisioning\Centile\IVRService;

class Extension
{
    public static function all($context)
    {
        $enterprise = CentileENT::getEnterprise($context);
        $dialPlan = new DialPlan($enterprise->getDialPlan());
        return $dialPlan->filterAvailableExtensions();
    }

    public static function getAssignedExtensions($context)
    {
        return DB::connection('istra')
            ->table('INTERNALADDRESS')
            ->join('ADMTIVEDOMAIN', 'ADMTIVEDOMAIN.ADMTIVEDOMAINID', '=', 'INTERNALADDRESS.OWNERADMTIVEDOMAINID')
            ->where('ADMTIVEDOMAIN.DOMAINNAME', $context)
            ->where('ADDRESSNUMBER', 'not like', '~%')
            ->get()
            ->pluck('ADDRESSNUMBER')
            ->toArray();
    }

    public static function exists($context, $number)
    {
        return DB::connection('istra')
            ->table('INTERNALADDRESS')
            ->join('ADMTIVEDOMAIN', 'ADMTIVEDOMAIN.ADMTIVEDOMAINID', '=', 'INTERNALADDRESS.OWNERADMTIVEDOMAINID')
            ->where('ADDRESSNUMBER', $number)
            ->where('ADMTIVEDOMAIN.DOMAINNAME', $context)
            ->count() ? true : false;
    }

    public static function isAssigned($context, $number)
    {
        return DB::connection('istra')
            ->table('INTERNALADDRESS')
            ->join('ADMTIVEDOMAIN', 'ADMTIVEDOMAIN.ADMTIVEDOMAINID', '=', 'INTERNALADDRESS.OWNERADMTIVEDOMAINID')
            ->where('ADDRESSNUMBER', $number)
            ->where('ADMTIVEDOMAIN.DOMAINNAME', $context)
            ->whereNotNull('IUSERID')
            ->count() ? true : false;
    }

    public static function mapLinkedObjectsToExtensions($context, $extensions, $linkUsers = true, $linkServices = false)
    {
        $return = [];

        if ($linkUsers)
            $users = CentileENT::getUsers($context);

        if ($linkServices)
            $servicesExtensions = self::getServicesExtensions($context);

        foreach ($extensions as $extension) {
            $obj['extension'] = $extension;
            $obj['linked'] = null;
            if ($linkUsers) {
                foreach ($users as $user) {
                    if ($extension == $user->extension) {
                        $obj['linked']['type'] = 'USER';
                        $obj['linked']['label'] = implode(' ', [$user->firstName, $user->lastName]);
                    }
                }
            }
            if ($linkServices && !$obj['linked'])
                $obj['linked'] = self::mapServicesToExtension($context, $extension, $servicesExtensions);
            $return[] = $obj;
        }

        return $return;
    }

    protected static function getServicesExtensions($context)
    {
        $servicesExtensions = [];
        if ($conferenceBridge = CentileENT::getConferenceBridge($context)) {
            if ($conferenceBridge->extension) {
                $servicesExtensions[$conferenceBridge->extension] = ['type' => 'CONFERENCE'];
            }
        }

        $extensionsGroups = CentileENT::getExtensionGroups($context);
        foreach ($extensionsGroups as $extensionsGroup) {
            if ($extensionsGroup->extension)
                $servicesExtensions[$extensionsGroup->extension] = ['type' => 'EXTENSIONS_GROUP', 'label' => $extensionsGroup->label];
        }

        $speedDials = CentileENT::getSpeedDials($context);
        foreach ($speedDials as $speedDial) {
            if ($speedDial->extension)
                $servicesExtensions[$speedDial->extension] = ['type' => 'SPEED_DIAL', 'label' => $speedDial->label];
        }

        if ($voicemail = CentileENT::getVoicemail($context)) {
            if ($voicemail->extension) {
                $servicesExtensions[$voicemail->extension] = ['type' => 'VOICEMAIL'];
            }
        }

        return $servicesExtensions;
    }

    protected static function mapServicesToExtension($context, $extension, $servicesExtensions)
    {
        if (array_key_exists($extension, $servicesExtensions))
            return $servicesExtensions[$extension];

        return null;
    }

    public static function checkFormat($extension)
    {
        if (!preg_match('/^\d+$/', $extension))
            return false;

        return true;
    }
}
