<?php

namespace Provisioning\Centile;

use Provisioning\Centile\Contracts\CentileDriver;
use Provisioning\Centile\DeviceModel;
use Provisioning\Centile\LogicalTerminal;
use Provisioning\Centile\DialPrefix;
use Provisioning\Centile\Enterprise;
use Provisioning\Centile\Forwarding;
use Provisioning\Centile\PSTNRange;
use Provisioning\Centile\Site;
use Provisioning\Centile\SpeedDial;
use Provisioning\Centile\User;
use Provisioning\Centile\Extension;
use Provisioning\Centile\ExtensionsGroup;
use Provisioning\Centile\UserExtension;
use Provisioning\Centile\DialPlan;
use Provisioning\Centile\Line;
use Provisioning\Exceptions\InvalidLimitationException;
use Provisioning\Exceptions\CreateUserException;
use Provisioning\Exceptions\DeviceNotFoundException;
use Provisioning\Exceptions\DefaultPSTNNotFoundException;
use Provisioning\Exceptions\ExtensionAlreadyAssignedException;
use Provisioning\Exceptions\ExtensionNotInDialPlanException;
use Provisioning\Exceptions\LoginAlreadyUsedException;
use Provisioning\Exceptions\MissingDialPrefixException;
use Provisioning\Exceptions\PSTNNumberExistsException;
use Provisioning\Exceptions\TrunkUpdateException;
use Provisioning\Exceptions\UserExistsException;
use Provisioning\Exceptions\UserExtensionNotFoundException;
use Provisioning\Exceptions\ExtensionsGroupNotFoundException;
use Provisioning\Exceptions\InvalidExtensionException;
use DB;
use Validator;
use CentileENT;
use CentileTRK;
use Illuminate\Support\Facades\Log;

class Centile
{
    const DEFAULT_COUNTRY_CODE = '33';
    const DEFAULT_DIAL_PREFIX_NAME = 'default';
    const DEFAULT_ENTERPRISE_LANGUAGE = 'fr';
    const DEFAULT_IPBX = 'IPTC1';
    const DEFAULT_OPERATOR_PREFIX = '0';
    const DEFAULT_USSD_DIR_LOOKUP_POLICY = '0';
    const INSEE_PREFIX = '33';

    protected $client;

    public function __construct(CentileDriver $client)
    {
        $this->client = $client;
    }

    public function login()
    {
        return $this->client->login();
    }

    public function setContext($context)
    {
        return $this->client->setContext(['admtiveDomainName' => $context]);
    }

    public function setCommunityContext($context)
    {
        return $this->client->setCommunityContext(['communityName' => $context]);
    }

    public function getAdministrator($username)
    {
        if (!$admin = $this->client->getAdministrator(['login' => $username])) {
            return false;
        }

        return $admin;
    }

    public function createAdministrativeDomain($name, $type)
    {
        $administrativeDomain = new AdministrativeDomain(['name' => $name, 'type' => $type]);

        if (!$this->client->createAdministrativeDomain($administrativeDomain))
            return false;

        return $administrativeDomain;
    }

    public function createRoutingCommunity($name, $type)
    {
        $routingCommunity = new RoutingCommunity(['name' => $name, 'type' => $type]);

        if (!$this->client->createRoutingCommunity($routingCommunity))
            return false;

        return $routingCommunity;
    }

    public function getAdministrativeDomain($name)
    {
        if (!$administrativeDomain = $this->client->getAdministrativeDomain(['name' => $name])) {
            return false;
        }

        return $administrativeDomain;
    }

    public function getRoutingCommunity($name)
    {
        if (!$routingCommunity = $this->client->getRoutingCommunity(['name' => $name]))
            return false;

        return $routingCommunity;
    }

    public function getAdministrativeDomains()
    {
        if (!$admtiveDomains = $this->client->getAdministrativeDomains()) {
            return [];
        }

        return $admtiveDomains;
    }

    public function getRoutingCommunities()
    {
        if (!$routingCommunities = $this->client->getRoutingCommunities()) {
            return [];
        }

        return $routingCommunities;
    }

    public function createUserExtension($context, $number, $label = null) {
        $params = [
          'label' => $label,
          'number' => $number,
          'isMyIstra' => true,
          'isXpadApp' => false,
          'pickupAndBLFCallerIDPolicy' => '2',
        ];

        $ue = new UserExtension($params);

        if (!$ue = $this->client->createUserExtension($context, $ue))
            return false;

        return $ue;
    }

    public function updateUserExtension($context, $selected, $label) {
        $params = [
          'selectedNumber' => $selected,
          'label' => $label,
        ];

        return $this->client->updateUserExtension($context, $params);
    }

    public function updateUserExtensionSoftphone($context, $selected, $softphone) {
        $params = [
          'selectedNumber' => $selected,
          'maxSoftphone' => $softphone,
        ];

        return $this->client->updateUserExtension($context, $params);
    }

    public function assignCallBarringToExtension($context, $name, $extension) {
        return $this->client->assignCallBarringToExtension($context, $name, $extension);
    }

    public function unassignCallBarringToExtension($context, $name, $extension) {
        return $this->client->unassignCallBarringToExtension($context, $name, $extension);
    }

    public function assignCallBarringToTrunk($context, $name, $trunk)
    {
        return $this->client->assignCallBarringToTrunk($context, $name, $trunk);
    }

    public function unassignAllCallBarringToExtension($context, $extension) {
        return $this->client->unassignAllCallBarringToExtension($context, $extension);
    }

    public function listAllCallBarringUsedByExtension($context, $extension) {
        return $this->client->listAllCallBarringUsedByExtension($context, $extension);
    }

    public function createUser(
        $context,
        $login = null,
        $password = null,
        $firstName = null,
        $lastName = null,
        $mobileNumber = null,
        $email = null,
        $extension = null,
        $extensionLabel = null
    ) {
        $params = [
          'login' => $login,
          'password' => $password,
          'firstName' => $firstName,
          'lastName' => $lastName,
          'mobileNumber' => $mobileNumber,
          'emails' => $email,
          'extension' => $extension,
        ];

        //check if login is already used in the cluster
        if ($this->getUser($login))
            throw new LoginAlreadyUsedException('Login ' . $login . ' is already used');

        if ($extension) {
            if (!$this->getUserExtension($context, $extension)) {
                $this->createUserExtension($context, $extension, $extensionLabel);
            } else {
                if (Extension::isAssigned($context, $extension))
                    throw new ExtensionAlreadyAssignedException("Extension " . $extension . " is already assigned");
            }
        }

        try {
            return $this->client->createUser($context, $params);
        } catch (Exception $e) {
            throw new CreateUserException('Login: ' . $login);
        }
    }

    public function updateUser($context, $login, $params)
    {
        $params['selectedlogin'] = $login;

        if (array_key_exists('extension', $params)) {
            $user = $this->getUser($login);

            if ($params['extension'] && ($user->extension != $params['extension'])) {
                if (Extension::isAssigned($context, $params['extension']))
                    throw new ExtensionAlreadyAssignedException("User extension " . $params['extension'] . " already assigned");
                elseif (!$this->getUserExtension($context, $params['extension'])) {
                    if (!$this->isExtensionInDialPlan($context, $params['extension']))
                        throw new ExtensionNotInDialPlanException('Extension ' . $params['extension'] . ' not in dialplan');
                    $this->createUserExtension($context, $params['extension']);
                }
            }

            if ($user->extension && empty($params['extension'])) {
                $this->deleteUserExtension($context, $user->extension);
            } elseif ($user->extension && $user->extension !== $params['extension']) {
                $this->changeUserUserExtension($context, $user->extension, $params['extension']);
                $this->deleteUserExtension($context, $user->extension);
            }
        }

        return $this->client->updateUser($params);
    }

    public function changeUserUserExtension($context, $oldExtension, $newExtension)
    {
        if ($oldExtension)
            return;

        //link devices
        $devices = $this->listDevicesUsedByExtension($context, $oldExtension);

        foreach ($devices as $device)
            $this->updateDevice($context, $device->physicalID, null, $device->label, $newExtension);

        //link pstns
        $pstns = $this->getPstnNumbersByUserExtension($context, $oldExtension);
        foreach ($pstns as $pstn)
            $this->updatePstnNumber($context, $pstn->number, ['numberExtension' => $newExtension]);

        //link extensions groups
        $groups = $this->getExtensionGroups($context);
        foreach ($groups as $group) {
            $this->deleteExtensionFromExtensionsGroup($context, $group->extension, $oldExtension);
            $this->addExtensionToExtensionsGroup($context, $group->extension, $newExtension);
        }

        //call barrings

        //link user
        if ($user = $this->getUserByUserExtension($context, $oldExtension))
            $this->client->updateUser(['selectedlogin' => $user->login, 'extension' => $newExtension]);
    }

    public function deleteUser($context, $login)
    {
        return $this->client->deleteUser($context, $login);
    }

    public function getUsers($context)
    {
        if (!$users = $this->client->getUsers($context))
            return [];

        return $users;
    }

    public function getUser($login)
    {
        if (!$user = $this->client->getUser(['login' => $login]))
            return null;

        return $user;
    }

    public function getUserByUserExtension($context, $extension)
    {
        return $this->client->getUser(['extension' => $extension], $context);
    }

    public function getUserByEmail($email)
    {
        return $this->client->getUser(['emails' => $email]);
    }

    public function getUserExtensions($context)
    {
        if (!$extensions = $this->client->getUserExtensions($context))
            return [];

        return $extensions;
    }

    public function getUserExtension($context, $number)
    {
        if (!$extension = $this->client->getUserExtension($context, ['number' => $number]))
            return null;

        return $extension;
    }

    public function deleteUserExtension($context, $extension)
    {
        return $this->client->deleteUserExtension($context, ['selectedNumber' => $extension]);
    }

    public function isExtensionInDialPlan($context, $extension)
    {
        // check if new selected extension is in the dialplan
        $enterprise = $this->getEnterprise($context);
        $dialplan = new DialPlan($enterprise->getDialPlan());
        if (!$dialplan->includesExtension($extension))
            return false;

        return true;
    }

    public function getDeviceModels()
    {
        $allowedModels = config('centile.device-models');

        $models = $this->client->getDeviceModels();
        foreach ($models as $model) {
            if (in_array($model->label, $allowedModels))
                $ret[] = $model;
        }

        return $ret;
    }

    public function getDeviceModel($name)
    {
        if (!$device = $this->client->getDeviceModel(['name' => $name]))
            return null;

        return $device;
    }

    public function getDeviceManufacturerStatus($context, $mac)
    {
        return $this->client->getDevice($context, $mac)->getStatusFromManufacturerAPI();
    }

    public function getDevice($context, $mac)
    {
        return $this->client->getDevice($context, $mac);
    }

    public function getDevices($context)
    {
        if (!$devices = $this->client->getDevices($context))
            return [];

        return $devices;
    }

    public function deleteDevice($context, $mac)
    {
        $device = $this->getDevice($context, $mac);
        $ret = $this->client->deleteDevice($context, ['selectedPhysicalID' => $mac]);
        $device->unregisterFromManufacturerAPI();
        return $ret;
    }

    public function listDevicesUsedByExtension($context, $extension)
    {
        if (!$devices = $this->client->listDevicesUsedByExtension($context, ['selectedExtension' => $extension]))
            return $devices;

        return collect($devices)->unique()->toArray();

    }

    public function createLogicalTerminal($context, $gateway, $logicalIDs, $extension, $label = null)
    {
        $terminal = new LogicalTerminal([
            'gateway' => $gateway,
            'logicalIDs' => $logicalIDs,
            'extension' => $extension,
            'label' => $label,
        ]);

        return $this->client->createLogicalTerminal($context, $terminal);
    }

    public function updateLogicalTerminal($context, $physicalId, $params)
    {
        $params['selectedPhysicalID'] = $physicalId;

        return $this->client->updateLogicalTerminal($context, $params);
    }

    public function deleteLogicalTerminal($context, $physicalId)
    {
        return $this->client->deleteLogicalTerminal($context, ['selectedPhysicalID' => $physicalId]);
    }

    public function getLogicalTerminal($context, $physicalId)
    {
        return head($this->client->getLogicalTerminal($context, ['selectedPhysicalID' => $physicalId]));
    }

    public function getLogicalTerminalsByExtension($context, $extension)
    {
        return $this->client->getLogicalTerminal($context, ['extension' => $extension]);
    }

    public function getLogicalTerminals($context)
    {
        return $this->client->getLogicalTerminal($context);
    }

    // public function getTerminalFromDB($macAddress)
    // {
    //     $data = DB::connection('istra')
    //         ->table('ABSTRACTTERMINAL')
    //         ->leftjoin('ATERMINALIADDRESS', 'ABSTRACTTERMINAL.ABSTRACTTERMINALID', '=', 'ATERMINALIADDRESS.ABSTRACTTERMINALID')
    //         ->leftjoin('INTERNALADDRESS', 'ATERMINALIADDRESS.INTERNALADDRESSID', '=', 'INTERNALADDRESS.INTERNALADDRESSID')
    //         ->leftjoin('PSTNPOOL', 'INTERNALADDRESS.ASSIGNABLEOBJECTID', '=', 'PSTNPOOL.ASSIGNEDTOID')
    //         ->leftjoin('DEVICE', 'ABSTRACTTERMINAL.PHYSICALID', 'like', DB::raw("CONCAT(DEVICE.MACADDRESS, '%')"))
    //         ->leftjoin('DEVICEMODEL', 'DEVICE.DEVICEMODELID', '=', 'DEVICEMODEL.DEVICEMODELID')
    //         ->leftjoin('SITE', 'DEVICE.SITEID', '=', 'SITE.SITEID')
    //         ->where('ABSTRACTTERMINAL.PHYSICALID', 'like', $macAddress . '%')
    //         ->where('ABSTRACTTERMINAL.DEVICEPORT', 1)
    //         ->select([
    //             'DEVICE.MACADDRESS',
    //             'DEVICE.LABEL',
    //             'INTERNALADDRESS.ADDRESSNUMBER',
    //             'PSTNPOOL.PSTNNUMBER',
    //             'DEVICEMODEL.LABEL as MODEL',
    //             'DEVICEMODEL.NAME as REFERENCE',
    //             'SITE.NAME as SITE',
    //             'DEVICEMODEL.LINESSUPPORTED as linesSupported',
    //             'ABSTRACTTERMINAL.DEVICEPORT as devicePort'
    //         ])
    //         ->first();
    //     return [
    //         'label' => $data->LABEL,
    //         'devicePhysicalID' => $data->MACADDRESS,
    //         'extension' => $data->ADDRESSNUMBER === 'NaN' ? null : $data->ADDRESSNUMBER ,
    //         'pstn' => $data->PSTNNUMBER,
    //         'model' => $data->MODEL,
    //         'reference' => $data->REFERENCE,
    //         'site' => $data->SITE,
    //         'linesSupported' => $data->linesSupported,
    //         'devicePort' => $data->devicePort,
    //     ];
    // }

    // public function getTerminalsFromDB($context)
    // {
    //     $data = DB::connection('istra')
    //         ->table('ABSTRACTTERMINAL')
    //         ->leftjoin('ATERMINALIADDRESS', 'ABSTRACTTERMINAL.ABSTRACTTERMINALID', '=', 'ATERMINALIADDRESS.ABSTRACTTERMINALID')
    //         ->leftjoin('INTERNALADDRESS', 'ATERMINALIADDRESS.INTERNALADDRESSID', '=', 'INTERNALADDRESS.INTERNALADDRESSID')
    //         // ->leftjoin('PSTNPOOL', 'INTERNALADDRESS.ASSIGNABLEOBJECTID', '=', 'PSTNPOOL.ASSIGNEDTOID')
    //         ->leftjoin('DEVICE', 'ABSTRACTTERMINAL.PHYSICALID', 'like', DB::raw("CONCAT(DEVICE.MACADDRESS, '%')"))
    //         ->leftjoin('DEVICEMODEL', 'DEVICE.DEVICEMODELID', '=', 'DEVICEMODEL.DEVICEMODELID')
    //         ->leftjoin('SITE', 'DEVICE.SITEID', '=', 'SITE.SITEID')
    //         ->leftjoin('COMMUNITY', 'DEVICE.ENTID', '=', 'COMMUNITY.ENTID')
    //         ->where('ABSTRACTTERMINAL.DEVICEPORT', 1)
    //         ->whereNotNull('DEVICE.DEVICEID')
    //         ->where('COMMUNITY.NAME', $context)
    //         ->select([
    //             'DEVICE.MACADDRESS',
    //             'DEVICE.LABEL',
    //             'INTERNALADDRESS.ADDRESSNUMBER',
    //             // 'PSTNPOOL.PSTNNUMBER',
    //             'DEVICEMODEL.LABEL as MODEL',
    //             'DEVICEMODEL.NAME as REFERENCE',
    //             'SITE.NAME as SITE'
    //         ])
    //         ->get();

    //     $ret = [];
    //     foreach ($data as $cur) {
    //         $ret[] = [
    //             'label' => $cur->LABEL,
    //             'devicePhysicalID' => $cur->MACADDRESS,
    //             'extension' => $cur->ADDRESSNUMBER,
    //             // 'pstn' => $cur->PSTNNUMBER,
    //             'model' => $cur->MODEL,
    //             'reference' => $cur->REFERENCE,
    //             'site' => $cur->SITE,
    //         ];
    //     }

    //     return $ret;
    // }

    public function getTerminal($context, $mac, $port)
    {
        if (!$terminal = $this->client->getTerminal($context, [
            'devicePhysicalID' => $mac,
            'devicePort' => $port,
        ]))
            return null;

        return head($terminal);
    }

    public function getTerminals($context, $mac = null, $extension = null)
    {
        $params = [];
        if ($mac)
            $params['devicePhysicalID'] = $mac;

        if ($extension)
            $params['extension'] = $extension;

        if (!$terminals = $this->client->getTerminal($context, $params))
            return [];

        return $terminals;
    }

    public function createLine($context, $mac, $lineNumber, $label, $type, $extension = null, $terminal = 1)
    {
        $params = [
            'devicePhysicalID' => $mac . '-' . $terminal,
            'label' => $label,
            'type' => $type,
            'lineNumber' => $lineNumber,
            'extension' => $extension,
        ];

        return $this->client->createLine($context, $params);
    }

    public function updateLineLine($context, $mac, $lineNumber, $label, $terminal = 1)
    {
        $params = [
            'devicePhysicalID' => $mac . '-' . $terminal,
            'lineNumber' => $lineNumber,
            'label' => $label,
            'type' => LINE::TYPE_LINE,
        ];

        // because the label doesn't update on updateLine, we have to delete and then recreate the line
        $this->client->deleteLine($context, $params);
        return $this->client->createLine($context, $params);
    }

    public function updateLineMonitoring($context, $mac, $lineNumber, $label, $numberToMonitor, $terminal = 1)
    {
        $params = [
            'devicePhysicalID' => $mac . '-' . $terminal,
            'lineNumber' => $lineNumber,
            'label' => $label,
            'type' => LINE::TYPE_MONITORING,
            'extension' => $numberToMonitor,
        ];

        // because the label doesn't update on updateLine, we have to delete and then recreate the line
        $this->client->deleteLine($context, $params);
        return $this->client->createLine($context, $params);
    }

    public function updateLineSpeedDial($context, $mac, $lineNumber, $label, $numberToDial, $terminal = 1)
    {
        $params = [
            'devicePhysicalID' => $mac . '-' . $terminal,
            'lineNumber' => $lineNumber,
            'label' => $label,
            'type' => LINE::TYPE_SPEED_DIAL,
        ];

        if (Extension::exists($context, $numberToDial)) {
            $params['extension'] = $numberToDial;
            $params['externalsd'] = null;
        } elseif (isE164format($numberToDial)) {
            $params['extension'] = null;
            $params['externalsd'] = $numberToDial;
        } else
            throw new InvalidExtensionException($numberToDial . ' is neither a valid extension or a phone number in e164 format');

        // because the label doesn't update on updateLine, we have to delete and then recreate the line
        $this->client->deleteLine($context, $params);
        return $this->client->createLine($context, $params);
    }

    public function deleteLine($context, $mac, $lineNumber, $terminal = 1)
    {
        $params = [
            'devicePhysicalID' => $mac . '-' . $terminal,
            'lineNumber' => $lineNumber,
        ];

        return $this->client->deleteLine($context, $params);
    }

    public function createDevice($context, $mac, $model, $label, $extension, $codec, $secret = null)
    {
        $parameters = [
            'model' => $model,
            'site' => Site::DEFAULT_NAME,
            'physicalID' => $mac,
            'label' => $label,
            'extension' => $extension,
            'preferedCodec' => $codec,
        ];

        $device = new Device($parameters);

        if (!$device = $this->client->createDevice($context, $device))
            return false;

        $device->registerOnManufacturerAPI($secret);

        if (preg_match("/^csip-cisco-ata/", $model)) {
            $parameters = [
            'selectedDevicePhysicalID' => $mac,
            'selectedDevicePort' => 1,
            'fax' => true,
            ];

            $this->client->updateTerminal($context, $parameters);
        }

        return $device;
    }

    public function updateDevice($context, $mac, $params)
    {
        $params['selectedPhysicalID'] = $mac;

        if (!$oldDevice = $this->client->getDevice($context, $mac))
            throw new DeviceNotFoundException('Device with MAC address ' . $mac . ' not found');

        if (!$ret = $this->client->updateDevice($context, $params))
            return false;

        if (!$newDevice = $this->client->getDevice($context, $params['physicalID']))
            throw new DeviceNotFoundException('Device with MAC address ' . $params['physicalID'] . ' not found');

        $oldDevice->unregisterFromManufacturerAPI();
        $newDevice->registerOnManufacturerAPI(isset($params['secret']) ? $params['secret'] : null);

        return $ret;
    }

    public function updateTerminal($context, $mac, $terminal, $params)
    {
        if (!$device = $this->client->getDevice($context, $mac))
            throw new DeviceNotFoundException('Device with MAC address ' . $mac . ' not found');

        $parameters = [
            'selectedDevicePhysicalID' => $mac,
            'selectedDevicePort' => $terminal,
        ];

        $deviceModel = CentileENT::getDeviceModel($device->model);
        if ($deviceModel && preg_match("/^csip-cisco-ata/", $deviceModel->name))
            $parameters['fax'] = true;

        if (array_key_exists('extension', $params))
            $parameters['extension'] = $params['extension'];

        if (array_key_exists('label', $params))
            $parameters['label'] = $params['label'];

        return $this->client->updateTerminal($context, $parameters);
    }

    public function deleteTerminal($context, $mac, $terminal)
    {
        $this->updateTerminal($context, $mac, $terminal, [
            'extension' => null,
            'label' => null,
        ]);

        return true;
    }

    public function getLine($context, $mac, $lineNumber, $terminal = 1)
    {
        $lines = $this->getLines($context, $mac, $terminal);

        foreach ($lines as $line) {
            if ($line->lineNumber == $lineNumber)
                return $line;
        }

        return null;
    }

    public function getLines($context, $mac, $terminal = 1)
    {
        return $this->client->getLine($context, ['devicePhysicalID' => $mac . '-' . $terminal]);
    }

    public function createForwarding($context, $assignedTo, $type, $destination, $label = null)
    {
        $params = [
            'assignedTo' => $assignedTo,
            'type' => $type,
            'label' => $label,
        ];

        if (in_array($destination, ['USER_MOBILE', 'USER_HOME', 'ENT_VM', 'ENT_RCPT', 'REJECTION']))
            $params['labelledDestination'] = $destination;
        elseif (isE164format($destination))
            $params['externalDestination'] = $destination;
        elseif (UserExtension::exists($context, $destination))
            $params['internalDestination'] = $destination;
        elseif (ExtensionsGroup::exists($context, $destination))
            $params['internalDestination'] = $destination;
        elseif (SpeedDial::exists($context, $destination))
            $params['internalDestination'] = $destination;
        else
            throw new \Exception('Unable to determine forwarding destination');

        $forwarding = new Forwarding($params);

        return $this->client->createForwarding($context, $forwarding);
    }

    public function getForwardings($context)
    {
        return $this->client->getForwarding($context);
    }

    public function deleteAllForwardings($context, $trunk)
    {
        foreach ($this->client->getForwarding($context, ['assignedTo' => $trunk]) as $forwarding)
            $this->deleteForwarding($context, $forwarding->forwardingID);
    }

    // Doesn't work for now as we can't specify an ID
    // public function getForwarding($context, $forwardingId)
    // {
    //     return $this->client->getForwarding($context, ['forwardingID' => $forwardingId]);
    // }

    public function getForwarding($context, $forwardingId)
    {
        $forwardingData = DB::connection('istra')
            ->table('TRIGGEREDSERVICE')
            ->leftjoin('INTERNALADDRESS', 'TRIGGEREDSERVICE.FORWARDINGASSIGNEDTOID', '=', 'INTERNALADDRESS.ASSIGNABLEOBJECTID')
            ->leftJoin('INTERNALADDRESS as I2', 'TRIGGEREDSERVICE.INTERNALDESTINATIONID', '=', 'I2.INTERNALADDRESSID')
            ->where('TRIGGEREDSERVICE.FORWARDINGID', $forwardingId)
            ->select([
                'INTERNALADDRESS.ADDRESSNUMBER as assignedTo',
                'I2.ADDRESSNUMBER as internalDestination',
                'TRIGGEREDSERVICE.FORWARDINGID as forwardingID',
                'TRIGGEREDSERVICE.LABEL as label',
                'TRIGGEREDSERVICE.NOANSWERFORWARDINGDELAY as noAnswerDelay',
                'TRIGGEREDSERVICE.FORWARDTYPE as type',
                'TRIGGEREDSERVICE.EXTERNALDESTINATION as externalDestination',
                'TRIGGEREDSERVICE.LABELLEDDESTINATION AS labelledDestination',
                'TRIGGEREDSERVICE.ACTIVATED AS activated',
                'TRIGGEREDSERVICE.FILTER AS filter'
            ])
            ->first();

        if (!$forwardingData)
            return null;

        $forwardingData->forwardingID = (string) $forwardingData->forwardingID;
        $forwardingData->noAnswerDelay = (string) $forwardingData->noAnswerDelay;

        return new Forwarding($forwardingData);
    }

    public function getForwardingsByAssignedTo($context, $assignedTo)
    {
        return $this->client->getForwarding($context, ['assignedTo' => $assignedTo]);
    }

    public function updateForwarding($context, $forwardingId, $params)
    {
        $params['id'] = $forwardingId;

        return $this->client->updateForwarding($context, $params);
    }

    public function deleteForwarding($context, $forwardingId)
    {
        $params = [
            'id' => $forwardingId,
        ];

        return $this->client->deleteForwarding($context, $params);
    }

    public function getCallBarring($context, $name)
    {
        if ($name == "")
            return null;

        return $this->client->getCallBarring($context, ['name' => $name]);
    }

    public function getCallBarrings($context)
    {
        if (!$callBarrings = $this->client->getCallBarring($context))
            return [];

        return $callBarrings;
    }

    public function getTrunk($routingCommunity, $name)
    {
        $parameters = ['name' => $name];

        if (!$trunk = $this->client->getTrunk($routingCommunity, $parameters))
            return false;

        return $trunk;
    }

    public function getTrunks($routingCommunity = null)
    {
        $routingCommunitiesNames = [];
        if ($routingCommunity === null) {
            $routingCommunities = $this->client->getRoutingCommunities();
            foreach ($routingCommunities as $routingCommunity) {
                $routingCommunitiesNames[] = $routingCommunity->name;
            }
        } else {
            $routingCommunitiesNames[] = $routingCommunity;
        }

        $trunks = [];
        foreach ($routingCommunitiesNames as $routingCommunityName) {
            $trunks = array_merge($trunks, $this->client->getTrunks($routingCommunityName));
        }

        return $trunks;
    }

    public function createTrunk($routingCommunity, $params)
    {
        $trunk = new PBXTrunking($params);

        if (!$trunk = $this->client->createTrunk($routingCommunity, $trunk))
            return false;

        return $trunk;
    }

    public function deleteTrunk($routingCommunity, $name)
    {
        if (!$this->client->deleteTrunk($routingCommunity, $name))
            return false;

        return true;
    }

    public function updateTrunk($routingCommunity, $name, $params)
    {
        if (!$trunk = $this->client->updateTrunk($routingCommunity, $name, $params)) {
            return false;
        }

        return $trunk;
    }

    public function getPstnRange($parameters, $context = null)
    {
        if (!$pstn = $this->client->getPstnRange($context, $parameters)) {
            return false;
        }

        return $pstn;
    }

    public function getPstnRanges($context = null, $withSubContexts = false)
    {
        if (!$pstns = $this->client->getPstnRanges($context, null, $withSubContexts)) {
            return [];
        }

        return $pstns;
    }

    public function createPstnRange($rangeStart, $rangeEnd, $clientName, $context = null, $trunk = null)
    {
        $i = 1;
        do {
            $label = sprintf(PSTNRange::DEFAULT_PREFIX.'-'.$clientName.'-%04d', $i);
            ++$i;
        } while ($this->client->getPstnRange($context, ['label' => $label]));

        for ($number = $rangeStart; $number <= $rangeEnd; ++$number) {
            if ($this->getPstnNumber($number)) {
                throw new PSTNNumberExistsException('Unable to create PSTN Range ' . $rangeStart . ' to ' . $rangeEnd . '. Number '.$number .' already exists');
            }
        }

        $params = [
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'label' => $label,
            'countryCode' => '+',
        ];

        $pstnRange = new PSTNRange($params);
        if (!$this->client->createPstnRange($pstnRange))
            return false;

        if ($context !== null) {
            foreach ($pstnRange->getAllPSTNs() as $pstn) {
                if ($trunk)
                    $this->assignPstnToTrunk($context, $pstn, $trunk);
                else
                    $this->assignPstnToAdmtiveDomain($pstn, $context);
            }
        }

        return $pstnRange;
    }

    public function deletePstnRange($start)
    {
        if (!$this->client->deletePstnRange($start)) {
            return false;
        }

        return true;
    }

    public function getPstnNumbers($context = null, $params = [])
    {
        if (!$pstnNumbers = $this->client->getPstnNumbers($context, $params))
            return [];

        return $pstnNumbers;
    }

    public function getPstnNumber($pstnNumber)
    {
        $params['number'] = $pstnNumber;

        if (!$pstn = $this->client->getPstnNumber($params))
            return null;

        return $pstn;
    }

    public function getPstnNumbersByUserExtension($context, $userExtension)
    {
        $params['numberExtension'] = $userExtension;
        return $this->client->getPstnNumbers($context, $params);
    }

    /**
     * Get all numbers that have been assigned to a trunk or centrex
     */
    public function getAssignedPSTNNumbers($numbers)
    {
        $assigned = [];
        foreach ($numbers as $number) {
            $ret = $this->getPstnNumber($number);
            if ($ret !== null && $ret->admtiveDomain !== 'Top-Level')
                $assigned[] = $number;
        }

        return $assigned;
    }

    /**
     * Get all registered numbers in the pool
     * (they don't have to be assigned to a centrex/trunk)
     */
    public function getRegisteredPSTNNumbers($numbers)
    {
        $registered = [];
        foreach ($numbers as $number) {
            if ($this->getPstnNumber($number))
                $registered[] = $number;
        }
        return $registered;
    }

    public function assignPstnToAdmtiveDomain($pstnNumber, $admtiveDomain)
    {
        return $this->client->assignPstnToAdmtiveDomain(
            [
                'selectedNumber' => $pstnNumber,
                'admtiveDomain' => $admtiveDomain,
            ]
        );
    }

    public function assignPstnToTrunk($context, $pstnNumber, $trunkName)
    {
        if ($this->client->assignPstnToTrunk($context, [
            'selectedPstnNumber' => $pstnNumber,
            'pbxTrunking' => $trunkName,
        ]))
            return true;

        return false;
    }

    public function unassignPstnFromTrunk($pstnNumber)
    {
        return $this->client->unassignPstn(['selectedPstnNumber' => $pstnNumber, 'pbxTrunking' => null]);
    }

    public function releasePstn($pstnNumber)
    {
        $this->assignPstnToAdmtiveDomain($pstnNumber, 'Top-Level');
    }

    public function updatePstnNumber($context, $number, $params)
    {
        $params['selectedNumber'] = $number;

        return $this->client->updatePstnNumber($context, $params);
    }

    public function setDefaultPstn($context, $trunkName, $pstnNumber)
    {
        if (!empty($pstnNumber)) {
            if (!$this->client->updateTrunk($context, $trunkName, ['checkPolicyOnAssertedCallerID' => '1'])) {
                throw new TrunkUpdateException('Trunk name: '.$trunkName);
            }

            // because updatePlmnPstn is broken on RoutingCommunity API, we have to use the one from Enterprise API
            return CentileENT::updatePstnNumber($context, $pstnNumber, ['isDefault' => 'true']);
        } else {
            $trunk = $this->getTrunk($context, $trunkName);

            if (!$trunk->defaultPstn) {
                throw new DefaultPSTNNotFoundException('Trunk name: '.$trunkName);
            }

            if (!$this->client->updateTrunk($context, $trunkName, ['checkPolicyOnAssertedCallerID' => '2'])) {
                throw new TrunkUpdateException('Trunk name: '.$trunkName);
            }

            // because updatePlmnPstn is broken on RoutingCommunity API, we have to use the one from Enterprise API
            return CentileENT::updatePstnNumber($context, $trunk->defaultPstn, ['isDefault' => 'false']);
        }

        // temporary hack while waiting for Centile to fix their SOAP API
        // try {
        //     //unset any other pstn number to not be default on this trunk
        //     DB::table('PSTNPOOL')
        //         ->leftJoin('PBXTRUNKING', 'PSTNPOOL.ASSIGNEDTOID', '=', 'PBXTRUNKING.ASSIGNABLEOBJECTID')
        //         ->where('PBXTRUNKING.NAME', $trunkName)
        //         ->update(['PSTNPOOL.ISDEFAULT' => 0]);

        //     if (!empty($pstnNumber))
        //         DB::table('PSTNPOOL')
        //             ->where('PSTNNUMBER', $pstnNumber)
        //             ->update(['PSTNPOOL.ISDEFAULT' => 1]);
        // } catch (\PDOException $e) {
        //     return false;
        // }

        // return true;
    }

    public function getEnterprises()
    {
        if (!$enterprises = $this->client->getEnterprises())
            return [];

        return $enterprises;
    }

    public function getEnterprise($name)
    {
        $params = ['name' => $name];

        return $this->client->getEnterprise($params);
    }

    public function deleteEnterprise($name)
    {
        $parameters = [
            'name' => $name,
        ];

        foreach ($this->getPstnNumbers($name) as $pstn)
            $this->assignPstnToAdmtiveDomain($pstn->number, 'Top-Level');

        if (!$this->client->deleteEnterprise($parameters))
            return false;

        return true;
    }

    public function restrictEnterprise($name, $restriction)
    {
        if (!in_array($restriction, [
            Enterprise::LIMITATION_NONE,
            Enterprise::LIMITATION_PARTIAL,
            Enterprise::LIMITATION_TOTAL,
        ]))
            throw new InvalidLimitationException($restriction);

        DB::connection('istra')
            ->table('COMMUNITY')
            ->where('NAME', $name)
            ->update(['LIMITATION' => $restriction]);

        return true;
    }

    public function createEnterprise($context, $name, $fullName, $billingAccount, $clientId, $maxChannels, $internalDialplan)
    {
        $parameters = [
            'name' => $name,
            'fullName' => $fullName,
            'billingAccountCustom1' => $billingAccount,
            'billingAccountCustom2' => $clientId,
            'maxExternalConnections' => $maxChannels,
            'maxIVRConnections' => $maxChannels * 2,
            'internalDialplan' => $internalDialplan,
            'ipbx' => self::DEFAULT_IPBX,
            'defaultLanguage' => self::DEFAULT_ENTERPRISE_LANGUAGE,
            'operatorPrefix' => self::DEFAULT_OPERATOR_PREFIX,
            'countryCode' => self::DEFAULT_COUNTRY_CODE,
            'ussdDirectoryLookupPolicy' => self::DEFAULT_USSD_DIR_LOOKUP_POLICY,
        ];

        $enterprise = new Enterprise($parameters);

        if (!$this->client->createEnterprise($context, $enterprise))
            return false;

        return $enterprise;
    }

    public function updateEnterprise($name, $params)
    {
        $params['selectedName'] = $name;

        return $this->client->updateEnterprise($params);
    }

    public function createSite($context, $name, $location)
    {
        $parameters = [
            'name' => $name,
            'location' => $location,
            'isDefaultSite' => 'true',
        ];

        $site = new Site($parameters);

        if (!$this->client->createSite($context, $site))
            return false;

        return $site;
    }

    public function updateSite($context, $name, $parameters)
    {
        $parameters['selectedName'] = $name;

        return $this->client->updateSite($context, $parameters);
    }

    public function createSpeedDial($context, $extension, $externalDestination, $label, $dialPrefixName = self::DEFAULT_DIAL_PREFIX_NAME)
    {
        $parameters = [
            'extension' => $extension,
            'externalDestination' => $externalDestination,
            'label' => $label,
        ];

        $speedDial = new SpeedDial($parameters);

        if (!$dialPrefix = $this->client->getDialPrefix($context, ['name' => $dialPrefixName]))
            throw new MissingDialPrefixException('Context: ' . $context . ' | DialPrefix: default');

        if (!$this->client->createSpeedDial($context, $dialPrefix->prefix, $speedDial))
            return false;

        return $speedDial;
    }

    public function getSpeedDials($context)
    {
        if (!$speedDials = $this->client->getSpeedDials($context))
            return [];

        return $speedDials;
    }

    public function getSpeedDial($context, $extension)
    {
        if (!$speedDial = $this->client->getSpeedDial($context, ['extension' => $extension]))
            return null;

        return $speedDial;
    }

    public function updateSpeedDial($context, $selectedExtension, $extension, $pstn, $label)
    {
        return $this->client->updateSpeedDial($context, [
            'selectedExtension' => $selectedExtension,
            'extension' => $extension,
            'externalDestination' => $pstn,
            'label' => $label,
        ]);
    }

    public function deleteSpeedDial($context, $extension)
    {
        return $this->client->deleteSpeedDial($context, [
            'extension' => $extension,
        ]);
    }

    public function createDialPrefix($context, $name, $prefix, $gateway)
    {
        $parameters = [
            'name' => $name,
            'prefix' => $prefix,
            'gateway' => $gateway,
        ];

        $dialPrefix = new DialPrefix($parameters);

        if (!$this->client->createDialPrefix($context, $dialPrefix))
            return false;

        return $dialPrefix;
    }

    public function createIVRService($context, $extension, $label, $ivrName)
    {
        $parameters = [
            'extension' => $extension,
            'label' => $label,
            'ivrName' => $ivrName,
        ];

        $ivrService = new IVRService($parameters);

        if (!$this->client->createIVRService($context, $ivrService))
            return false;

        return $ivrService;
    }

    public function deleteIVRService($context, $extension)
    {
        $params = ['extension' => $extension];
        return $this->client->deleteIVRService($context, $params);
    }

    public function updateIVRService($context, $selectedExtension, $extension)
    {
        $parameters = [
            'selectedExtension' => $selectedExtension,
            'extension' => $extension,
        ];

        if (!$ivr = $this->client->updateIVRService($context, $parameters))
            return false;

        return $ivr;
    }

    public function createService($context, $label, $serviceName)
    {
        $parameters = [
            'label' => $label,
            'serviceName' => $serviceName,
        ];

        $service = new Service($parameters);

        if (!$this->client->createService($context, $service))
            return false;

        return $service;
    }

    public function deleteService($context, $serviceName)
    {
        $params = ['serviceName' => $serviceName];
        return $this->client->deleteService($context,$params);
    }

    public function getVoicemail($context)
    {
        if (!$voicemails = $this->client->getIVRServices($context, IVRService::ISTRA_IVR_VOICEMAIL_LABEL))
            return null;

        return head($voicemails);
    }

    public function getConferenceBridge($context)
    {
        if (!$confBridges = $this->client->getIVRServices($context, IVRService::ISTRA_IVR_CONFERENCE_LABEL))
            return null;

        return head($confBridges);
    }

    public function getCallQueuing($context)
    {
        if (!$callQueuing= $this->client->getServices($context, Service::ISTRA_SERVICE_CALL_QUEUING_LABEL))
            return null;

        return head($callQueuing);
    }

    public function createExtensionGroup($context, $extension, $extensions, $label)
    {
        $parameters = [
            'extension' => $extension,
            'label' => $label,
            'syndicalPause' => '1',
        ];

        $group = $this->client->createExtensionGroup($context, new ExtensionsGroup($parameters));

        if ($extensions)
            $this->setExtensionsInExtensionGroup($context, $extension, $extensions);

        $group->extensions = $extensions;

        return $group;
    }

    /**
     * @param  [string] $context
     * @param  [string] $selected
     * @param  [array] $params
     * @return [ExtensionsGroup]
     */
    public function updateExtensionGroup($context, $selected, $params)
    {
        $params['selectedExtension'] = $selected;

        return $this->client->updateExtensionGroup($context, $params);
    }

    public function setExtensionsInExtensionGroup($context, $selectedExtension, $extensions)
    {
        if (!$extensions)
            return;

        $extensionsArray = explode(',', $extensions);
        foreach ($extensionsArray as $extension) {
            if (!$this->getUserExtension($context, $extension))
                throw new UserExtensionNotFoundException('User extension ' . $extension . ' not found');
        }

        return $this->client->setExtensionsInExtensionGroup($context, $selectedExtension, $extensions);
    }

    public function getExtensionGroup($context, $extension)
    {
        return $this->client->getExtensionGroup($context, ['extension' => $extension]);
    }

    public function getExtensionGroups($context)
    {
        if (!$groups = $this->client->getExtensionGroups($context))
            return [];

        return $groups;
    }

    public function listExtensionsInGroupAddress($context, $extension)
    {
        if (!$this->getExtensionGroup($context, $extension))
            throw new \ExtensionsGroupNotFoundException($extension);

        return $this->client->listExtensionsInGroupAddress($context, ['selectedExtension' => $extension]);
    }

    public function deleteExtensionFromExtensionsGroup($context, $extensionGroup, $extension)
    {
        if (!$extensionsInGroup = $this->listExtensionsInGroupAddress($context, $extensionGroup))
            return true;

        if (($key = array_search($extension, $extensionsInGroup)) === false)
            return true;

        unset($extensionsInGroup[$key]);
        if (count($extensionsInGroup))
            $extensionsInGroup = implode(',', $extensionsInGroup);
        else
            $extensionsInGroup = null;

        return $this->setExtensionsInExtensionGroup($context, $extensionGroup, $extensionsInGroup);
    }

    public function addExtensionToExtensionsGroup($context, $extensionGroup, $extension)
    {
        $extensionsInGroup = $this->listExtensionsInGroupAddress($context, $extensionGroup);
        if (($key = array_search($extension, $extensionsInGroup)) === false) {
            $extensionsInGroup[] = $extension;
            return $this->setExtensionsInExtensionGroup($context, $extensionGroup, implode(',', $extensionsInGroup));
        }

        return true;
    }

    public function deleteExtensionGroup($context, $extension)
    {
        return $this->client->deleteExtensionGroup($context, ['extension' => $extension]);
    }

    public function getSite($context, $name)
    {
        $parameters = [
            'name' => $name,
        ];

        if (!$site = $this->client->getSite($context, $parameters)) {
            return false;
        }

        return $site;
    }
}
