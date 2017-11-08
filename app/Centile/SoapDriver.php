<?php

namespace Provisioning\Centile;

use DB;
use Provisioning\Centile\Contracts\CentileDriver;
use Provisioning\Centile\AdministrativeDomain;
use Provisioning\Centile\CallBarring;
use Provisioning\Centile\Device;
use Provisioning\Centile\DeviceFactory;
use Provisioning\Centile\LogicalTerminal;
use Provisioning\Centile\DialPrefix;
use Provisioning\Centile\Enterprise;
use Provisioning\Centile\ExtensionsGroup;
use Provisioning\Centile\Forwarding;
use Provisioning\Centile\IVRService;
use Provisioning\Centile\Service;
use Provisioning\Centile\Site;
use Provisioning\Centile\Line;
use Provisioning\Centile\User;
use Provisioning\Centile\SpeedDial;
use Provisioning\Centile\UserExtension;
use Provisioning\Exceptions\AdministrativeDomainSwitchException;
use Provisioning\Exceptions\DeviceExistsException;
use Provisioning\Exceptions\PSTNNumberNotFoundException;
use Provisioning\Exceptions\RoutingCommunitySwitchException;
use Provisioning\Exceptions\TrunkExistsException;
use Provisioning\Exceptions\TrunkNotFoundException;
use Provisioning\Exceptions\UnableToLoginException;
use Provisioning\Exceptions\UserExistsException;
use Provisioning\Exceptions\UserExtensionExistsException;

class SoapDriver implements CentileDriver
{
    const ROUTING_COMMUNITY_TYPE = 1;
    const ENTERPRISE_TYPE = 2;
    protected $soapClient;
    protected $username;
    protected $password;
    protected $context;

    public function __construct($username, $password, $wsdl_type = self::ROUTING_COMMUNITY_TYPE)
    {
        $this->username = $username;
        $this->password = $password;

        if ($wsdl_type == self::ROUTING_COMMUNITY_TYPE) {
            $wsdl_uri = config('centile.wsdl_routing_community');
        } else {
            $wsdl_uri = config('centile.wsdl_enterprise');
        }

        if (preg_match('/^http[s]?:\/\//', $wsdl_uri))
            $wsdl = $wsdl_uri;
        else
            $wsdl = base_path($wsdl_uri);

        $this->soapClient = new WSASoapClient(
            $wsdl,
            [
                'soap_version' => SOAP_1_2,
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'keep_alive' => false,
            ]
        );
    }

    private function init($context = null, $type = self::ENTERPRISE_TYPE)
    {
        if (!$this->login())
            throw new UnableToLoginException();

        if ($type == self::ROUTING_COMMUNITY_TYPE) {
            // should be this function but Centile API is broken
            // $this->setCommunityContext(['communityName' => $context]);
            $this->setContext(['admtiveDomainName' => $context]);
        } else
            $this->setContext(['admtiveDomainName' => $context]);
    }

    public function login()
    {
        try {
            $isConnected = $this->soapClient->isConnected()->return;
        } catch (\SoapFault $e) {
            $this->soapClient->unsetHeaders();
            $isConnected = false;
        }

        if (!$isConnected) {
            $this->context = null;
            $response = $this->soapClient->login([
                'login' => $this->username,
                'password' => $this->password,
            ]);

            if (!$response->return)
                return false;

            $this->soapClient->setHeaders(
                'http://ws.apache.org/namespaces/axis2',
                'ServiceGroupId',
                $this->getSoapSession($this->soapClient->__getLastResponse())
            );
        }

        return true;
    }

    protected function getSoapSession($response)
    {
        $xmlReader = new \XMLReader();
        $xmlReader->XML($response);
        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == \XMLReader::ELEMENT && $xmlReader->name == 'axis2:ServiceGroupId') {
                $xmlReader->read();

                return $xmlReader->value;
            }
        }

        return null;
    }

    public function getAdministrator($parameters)
    {
        $this->init();

        $response = $this->soapClient->getAdministrator($parameters);

        if (!$response->return || $response->return->number == 0)
            return false;

        return new Administrator($response->return->elements);
    }

    public function getAdministrativeDomains()
    {
        $this->init();

        $response = $this->soapClient->getAdministrativeDomain();

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            $admtiveDomains[] = new AdministrativeDomain($response->return->elements);
        } else {
            foreach ($response->return->elements as $admtiveDomain) {
                $admtiveDomains[] = new AdministrativeDomain($admtiveDomain);
            }
        }

        return $admtiveDomains;
    }

    public function getAdministrativeDomain($params)
    {
        $this->init();

        $response = $this->soapClient->getAdministrativeDomain($params);

        if (!$response->return || $response->return->number == 0)
            return false;

        return new AdministrativeDomain($response->return->elements);
    }

    public function createAdministrativeDomain(AdministrativeDomain $administrativeDomain)
    {
        $this->init();

        $params = [
            'name' => $administrativeDomain->name,
            'type' => $administrativeDomain->type,
        ];

        $response = $this->soapClient->createAdministrativeDomain($params);

        if (!$response->return)
            return false;

        return new AdministrativeDomain($response->return);
    }

    public function createRoutingCommunity(RoutingCommunity $routingCommunity)
    {
        $this->init(null, self::ROUTING_COMMUNITY_TYPE);

        $params = [
            'name' => $routingCommunity->name,
            'gateway' => $routingCommunity->gateway,
        ];

        $response = $this->soapClient->createRoutingCommunity($params);

        if (!$response->return)
            return false;

        return new RoutingCommunity($response->return);
    }

    public function getRoutingCommunity($params)
    {
        $this->init(null, self::ROUTING_COMMUNITY_TYPE);

        $response = $this->soapClient->getRoutingCommunity($params);

        if (!$response->return || $response->return->number == 0)
            return false;

        return new RoutingCommunity($response->return->elements);
    }

    public function getRoutingCommunities()
    {
        $this->init(null, self::ROUTING_COMMUNITY_TYPE);

        $response = $this->soapClient->getRoutingCommunity();

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            $routingCommunities[] = new RoutingCommunity((array) $response->return->elements);
        } else {
            foreach ($response->return->elements as $community) {
                $routingCommunities[] = new RoutingCommunity((array) $community);
            }
        }

        return $routingCommunities;
    }

    public function getTrunk($context, $parameters)
    {
        $this->init($context, self::ROUTING_COMMUNITY_TYPE);

        $response = $this->soapClient->getPBXTrunking($parameters);

        if (!$response->return || $response->return->number == 0)
            return false;

        return new PBXTrunking($response->return->elements);
    }

    public function getTrunks($context)
    {
        $this->init($context, self::ROUTING_COMMUNITY_TYPE);

        $response = $this->soapClient->getPBXTrunking();

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return array(new PBXTrunking($response->return->elements));
        } else {
            foreach ($response->return->elements as $trunk) {
                $trunks[] = new PBXTrunking($trunk);
            }

            return $trunks;
        }
    }

    public function createTrunk($context, PBXTrunking $trunk)
    {
        if (!$this->getRoutingCommunity(['name' => $context])) {
            $community = new RoutingCommunity([
                'name' => $context,
                'gateway' => config('centile.default_gateway'),
            ]);
            $this->createRoutingCommunity($community);
        }

        $this->init($context, self::ROUTING_COMMUNITY_TYPE);

        $params = [
            'name' => $trunk->name,
            'label' => $trunk->label,
            'countryCode' => $trunk->countryCode,
            'sipHostPorts' => $trunk->sipHostPorts,
            'maxCallsP' => $trunk->maxCalls,
            'checkPolicyOnAssertedCallerID' => $trunk->checkPolicyOnAssertedCallerID,
            'allowedCodecs' => $trunk->allowedCodecs,
            'authUsername' => $trunk->authUsername,
            'authPassword' => $trunk->authPassword,
            'requestAuth' => $trunk->requestAuth,
            'location' => $trunk->location,
            'contactHostPorts' => $trunk->contactHostPorts,
            'callPolicy' => $trunk->callPolicy,
            'networkIDSIPGw' => $trunk->networkIDSIPGw,
            'checkAAA' => $trunk->checkAAA ? 'true' : 'false',
            'rtpRedirection' => $trunk->rtpRedirection,
            'operatorPrefix' => $trunk->operatorPrefix,
            'defaultPstn' => $trunk->defaultPstn,
            'registration' => $trunk->registration,
        ];

        if ($this->getTrunk($context, ['name' => $params['name']]))
            throw new TrunkExistsException('Trunk name: '.$params['name']);

        if ($this->getTrunk($context, ['authUsername' => $params['authUsername']]))
            throw new TrunkExistsException('Trunk with Auth Username: '.$params['authUsername']);

        $response = $this->soapClient->createPBXTrunking($params);

        if (!$response->return)
            return false;

        // temporary hack because unable to set language through API
        if (!$this->setTrunkLanguage($trunk->name, $trunk->language)) {
            $this->deleteTrunk($context, $trunk->name);

            return false;
        }

        // temporary hack because API broken for registration
        $this->setTrunkRegistration($trunk->name, $trunk->registration == 'true' ? '1' : '0');

        return new PBXTrunking($response->return);
    }

    protected function setTrunkRegistration($trunk, $status = 1)
    {
        if (!$trunk)
            return;

        DB::connection('istra')->table('PBXTRUNKING')->where('NAME', $trunk)->update(['REGISTRATION' => $status]);
    }

    public function updateTrunk($context, $trunkName, $params)
    {
        $this->init($context, self::ROUTING_COMMUNITY_TYPE);

        $params['selectedName'] = $trunkName;

        if (!$response = $this->soapClient->updatePBXTrunking($params))
            return false;

        return new PBXTrunking($response->return);
    }

    public function deleteTrunk($context, $name)
    {
        $this->init($context, self::ROUTING_COMMUNITY_TYPE);

        $response = $this->soapClient->deletePBXTrunking(['name' => $name]);
        if ($response->return)
            return true;

        return false;
    }

    protected function setTrunkLanguage($name, $language)
    {
        try {
            DB::connection('istra')->table('PBXTRUNKING')->where('NAME', $name)->update(['LANGUAGE' => 'fr']);
        } catch (\PDOException $e) {
            return false;
        }

        return true;
    }

    public function createUser($context, $params)
    {
        $this->init($context);

        $response = $this->soapClient->createUser($params);
        return new User($response->return);
    }

    public function assignCallBarringToExtension($context, $name, $extension)
    {
        $this->init($context);

        $params = [
            'selectedName' => $name,
            'extension' => $extension,
        ];

        return $this->soapClient->assignCallBarringToExtension($params);
    }

    public function unassignCallBarringToExtension($context, $name, $extension)
    {
        $this->init($context);

        $params = [
            'selectedName' => $name,
            'extension' => $extension,
        ];

        return $this->soapClient->unassignCallBarringToExtension($params);
    }

    public function assignCallBarringToTrunk($context, $name, $trunk)
    {
        $this->init($context);

        $params = [
            'selectedName' => $name,
            'pbxtrunking' => $trunk,
        ];

        return $this->soapClient->assignCallBarringToTrunk($params);
    }

    public function unassignAllCallBarringToExtension($context, $extension)
    {
        $this->init($context);

        $params = [
            'selectedExtension' => $extension,
        ];

        return $this->soapClient->unassignAllCallBarringToExtension($params);
    }

    public function listAllCallBarringUsedByExtension($context, $extension)
    {
        $this->init($context);

        $params = [
            'selectedExtension' => $extension,
        ];

        $response = $this->soapClient->listAllCallBarringUsedByExtension($params);

        if (!property_exists($response, 'return'))
            return [];
        if (!is_array($response->return))
            return $this->getCallBarring($context, ['name' => $response->return]);
        foreach ($response->return as $callBarring)
            $callBarrings[] = $this->getCallBarring($context, ['name' => $callBarring])[0];
        return $callBarrings;
    }

    public function getLine($context, $params)
    {
        $this->init($context);

        $response = $this->soapClient->getLine($params);

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return [new Line($response->return->elements)];
        } else {
            foreach ($response->return->elements as $element) {
                $lines[] = new Line($element);
            }

            return $lines;
        }
    }

    public function createLine($context, $params)
    {
        $this->init($context);

        $response = $this->soapClient->createLine($params);
        return new Line($response->return);
    }

    public function updateLine($context, $params)
    {
        $this->init($context);

        $response = $this->soapClient->updateLine($params);
        return new Line($response->return);
    }

    public function deleteLine($context, $params)
    {
        $this->init($context);

        return $this->soapClient->deleteLine($params)->return;
    }

    public function createForwarding($context, Forwarding $forwarding)
    {
        $this->init($context);

        $params = [
            'label' => $forwarding->label,
            'assignedTo' => $forwarding->assignedTo,
            'type' => $forwarding->type,
            'internalDestination' => $forwarding->internalDestination,
            'labelledDestination' => $forwarding->labelledDestination,
            'externalDestination' => $forwarding->externalDestination,
        ];

        $response = $this->soapClient->createForwarding($params);
        return new Forwarding($response->return);
    }

    public function getForwarding($context, $params = [])
    {
        $this->init($context);

        $response = $this->soapClient->getForwarding($params);

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return [new Forwarding($response->return->elements)];
        } else {
            foreach ($response->return->elements as $element) {
                $forwardings[] = new Forwarding($element);
            }

            return $forwardings;
        }
    }

    public function updateForwarding($context, $params)
    {
        $this->init($context);

        $response = $this->soapClient->updateForwarding($params);

        return new Forwarding($response->return);
    }

    public function deleteForwarding($context, $params)
    {
        $this->init($context);

        return $this->soapClient->deleteForwarding($params);
    }

    public function getCallBarring($context, $params = [])
    {
        $this->init($context);

        $response = $this->soapClient->getCallBarring($context, $params);

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return new CallBarring($response->return->elements);
        } else {
            foreach ($response->return->elements as $element) {
                if (empty($params) || $element->name == $params["name"])
                  $callBarrings[] = new CallBarring($element);
            }

            return $callBarrings;
        }
    }

    public function createDevice($context, Device $device)
    {
        $this->init($context);

        $params = [
          'model' => $device->model,
          'site' => $device->site,
          'physicalID' => $device->physicalID,
          'label' => $device->label,
          'preferedCodec' => $device->preferedCodec,
        ];

        if ($device->extension !== null)
            $params['extension'] = $device->extension;

        $response = $this->soapClient->createDevice($params);

        return DeviceFactory::make($response->return);
    }

    public function updateLogicalTerminal($context, $params)
    {
        $this->init($context);

        if (!$response = $this->soapClient->updateLogicalTerminal($params))
            return false;

        return new LogicalTerminal($response->return);
    }

    public function deleteLogicalTerminal($context, $params)
    {
        $this->init($context);

        return $this->soapClient->deleteLogicalTerminal($params)->return;
    }

    public function createLogicalTerminal($context, LogicalTerminal $terminal)
    {
        $this->init($context);

        $params = [
            'label' => $terminal->label,
            'gateway' => $terminal->gateway,
            'logicalIDs' => $terminal->logicalIDs,
            'extension' => $terminal->extension,
        ];

        return new LogicalTerminal($this->soapClient->createLogicalTerminal($params)->return);
    }

    public function getLogicalTerminal($context, $params = [])
    {
        $this->init($context);

        $response = $this->soapClient->getLogicalTerminal($params);

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return array(new LogicalTerminal($response->return->elements));
        } else {
            foreach ($response->return->elements as $element) {
                $terminals[] = new LogicalTerminal($element);
            }

            return $terminals;
        }
    }

    public function updateIVRService($context, $params)
    {
        $this->init($context);

        if (!$response = $this->soapClient->updateIVRService($params))
            return false;

       return new IVRService($response->return);
    }

    public function getDevice($context, $mac)
    {
        $this->init($context);

        $params = [
            'physicalID' => $mac,
        ];

        $response = $this->soapClient->getDevice($params);

        if (!$response->return || $response->return->number == 0)
            return null;

        return DeviceFactory::make($response->return->elements);
    }

    public function getDevices($context)
    {
        $this->init($context);

        $response = $this->soapClient->getDevice();

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return array(DeviceFactory::make($response->return->elements));
        } else {
            foreach ($response->return->elements as $element) {
                $devices[] = DeviceFactory::make($element);
            }

            return $devices;
        }
    }

    public function updateDevice($context, $params)
    {
        $this->init($context);

        if (!$response = $this->soapClient->updateDevice($params))
            return false;

        return DeviceFactory::make($response->return);
    }

    public function deleteDevice($context, $params)
    {
        $this->init($context);

        return $this->soapClient->deleteDevice($params);
    }

    public function getDeviceModels($params = [])
    {
        $this->init();

        $response = $this->soapClient->getDeviceModel($params);

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return array(new DeviceModel($response->return->elements));
        } else {
            foreach ($response->return->elements as $device) {
                $devices[] = new DeviceModel($device);
            }

            return $devices;
        }
    }

    public function getDeviceModel($params)
    {
        if (!$devices = $this->getDeviceModels($params))
            return null;

        return head($devices);
    }

    public function listDevicesUsedByExtension($context, $params)
    {
        $this->init($context);

        $response = $this->soapClient->listDevicesUsedByExtension($params);

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return array(DeviceFactory::make($response->return->elements));
        } else {
            foreach ($response->return->elements as $device) {
                $devices[] = DeviceFactory::make($device);
            }

            return $devices;
        }
    }

    public function updateTerminal($context, $params)
    {
        $this->init($context);

        if (!$response = $this->soapClient->updateTerminal($params))
            return false;

        return new Terminal($response->return);
    }

    public function getTerminal($context, $params = [])
    {
        $this->init($context);

        $response = $this->soapClient->getTerminal($params);

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return [new Terminal($response->return->elements)];
        } else {
            foreach ($response->return->elements as $element) {
                $terminals[] = new Terminal($element);
            }

            return $terminals;
        }
    }

    public function updateUser($params)
    {
        $this->init();

        $response = $this->soapClient->updateUser($params);

        if (!$response->return)
            return false;

        return new User($response->return);
    }

    public function deleteUser($context, $login)
    {
        $this->init();

        return $this->soapClient->deleteUser(['login' => $login]);
    }

    public function getUser($params, $context = null)
    {
        $this->init($context);

        $response = $this->soapClient->getUser($params);

        if (!$response->return || $response->return->number == 0)
            return null;

        return new User($response->return->elements);
    }

    public function getUsers($context)
    {
        $this->init($context);

        $response = $this->soapClient->getUser();

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return [new User($response->return->elements)];
        } else {
            foreach ($response->return->elements as $element)
                $users[] = new User($element);
            return $users;
        }
    }

    public function createUserExtension($context, UserExtension $userExtension)
    {
        $this->init($context);

        $params = [
            'label' => $userExtension->label,
            'number' => $userExtension->number,
            'isMyIstra' => $userExtension->isMyIstra,
            'isXpadApp' => $userExtension->isXpadApp,
            'pickupAndBLFCallerIDPolicy' => $userExtension->pickupAndBLFCallerIDPolicy,
        ];

        $response = $this->soapClient->createUserExtension($params);

        if (!$response->return || $response->return->number == 0)
            return false;

        return new UserExtension($response->return);
    }

    public function updateUserExtension($context, $params)
    {
        $this->init($context);

        $response = $this->soapClient->updateUserExtension($params);

        if (!$response->return)
            return false;

        return new UserExtension($response->return);
    }

    public function getUserExtension($context, $param)
    {
        $this->init($context);

        $response = $this->soapClient->getUserExtension($param);

        if (!$response->return || $response->return->number == 0)
            return false;

        return new UserExtension($response->return->elements);
    }

    public function getUserExtensions($context)
    {
        $this->init($context);

        $response = $this->soapClient->getUserExtension();

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return array(new UserExtension($response->return->elements));
        } else {
            foreach ($response->return->elements as $element) {
                $userExtensions[] = new UserExtension($element);
            }

            return $userExtensions;
        }
    }

    public function deleteUserExtension($context, $parameters)
    {
        $this->init($context);

        return $this->soapClient->deleteUserExtension($parameters);
    }

    public function createPstnRange(PSTNRange $pstnRange)
    {
        $this->init();

        $response = $this->soapClient->createPstnRange([
            'label' => $pstnRange->label,
            'rangeStart' => $pstnRange->rangeStart,
            'rangeEnd' => $pstnRange->rangeEnd,
            'countryCode' => $pstnRange->countryCode,
            'isRegisteredInEnum' => $pstnRange->isRegisteredInEnum,
        ]);

        if (!$response->return)
            return false;

        return new PSTNRange($response->return);
    }

    public function getPstnRange($context, $parameters)
    {
        if (!$pstnRanges = $this->getPstnRanges($context, $parameters))
            return false;

        return head($pstnRanges);
    }

    //temporary hack to filter PSTNRanges by Administrative Domain
    // public function getPstnRanges($context, $parameters = null, $withSubContexts = false)
    // {
    //     if ($withSubContexts == false) {
    //         $query = DB::table('PSTNRANGE')
    //             ->select('LABEL', 'RANGESTART', 'RANGEEND', 'COUNTRYCODE', 'ISREGISTEREDINENUM')
    //             ->join('ADMTIVEDOMAIN', 'PSTNRANGE.OWNERADMTIVEDOMAINID', '=', 'ADMTIVEDOMAIN.ADMTIVEDOMAINID')
    //             ->where('ADMTIVEDOMAIN.DOMAINNAME', $context);
    //     } else {
    //         $query = DB::table('PSTNRANGE AS P')
    //             ->select('LABEL', 'RANGESTART', 'RANGEEND', 'COUNTRYCODE', 'ISREGISTEREDINENUM')
    //             ->join('ADMTIVEDOMAIN AS A1', 'P.OWNERADMTIVEDOMAINID', '=', 'A1.ADMTIVEDOMAINID')
    //             ->join('ADMTIVEDOMAIN AS A2', 'A1.ADMTIVEDOMAINID', 'LIKE', DB::raw('CONCAT(A2.ADMTIVEDOMAINID, "%")'))
    //             ->where('A2.DOMAINNAME', $context);
    //     }

    //     $pstnRangesFromDB = $query->get();

    //     $pstnRanges = [];
    //     foreach ($pstnRangesFromDB as $pstnRange) {
    //         $pstnRanges[] = new PSTNRange([
    //             'rangeStart' => $pstnRange->RANGESTART,
    //             'rangeEnd' => $pstnRange->RANGEEND,
    //             'label' => $pstnRange->LABEL,
    //             'isRegisteredInEnum' => $pstnRange->ISREGISTEREDINENUM,
    //             'countryCode' => $pstnRange->COUNTRYCODE,
    //         ]);
    //     }

    //     return $pstnRanges;
    // }

    public function getPstnRanges($context, $parameters = null)
    {
        $this->init($context);

        $response = $this->soapClient->getPstnRange($parameters);

        if (!$response->return || $response->return->number == 0)
            return [];
        elseif ($response->return->number == 1)
            return array(new PSTNRange($response->return->elements));
        else {
            foreach ($response->return->elements as $element)
                $pstns[] = new PSTNRange($element);

            return $pstns;
        }
    }

    public function deletePstnRange($start)
    {
        $this->init();

        $response = $this->soapClient->deletePSTNRange(['rangeStart' => $start]);

        if ($response->return)
            return true;

        return false;
    }

    public function getPstnNumber($parameters)
    {
        if (!$pstnNumbers = $this->getPstnNumbers(null, $parameters))
            return false;

        return head($pstnNumbers);
    }

    public function getPstnNumbers($context = null, $parameters = [])
    {
        $this->init($context);

        $response = $this->soapClient->getPlmnPstn($parameters);

        if (!$response->return || $response->return->number == 0)
            return [];
        elseif ($response->return->number == 1)
            return array(new PSTNNumber($response->return->elements));
        else {
            foreach ($response->return->elements as $element)
                $pstns[] = new PSTNNumber($element);

            return $pstns;
        }
    }

    public function updatePstnNumber($context, $params)
    {
        $this->init($context);

        return new PSTNNumber($this->soapClient->updatePlmnPstn($params)->return);
    }

    private function setContext($params)
    {
        if ($params['admtiveDomainName'] == 'Top-Level')
            $params['admtiveDomainName'] = null;

        try {
            if ($this->context !== $params['admtiveDomainName']) {
                $this->soapClient->setContext($params);
                $this->context = $params['admtiveDomainName'];
            }
            return true;
        } catch (\SoapFault $e) {
            throw new AdministrativeDomainSwitchException($e);
        }
    }

    public function setCommunityContext($params)
    {
        if (!$this->login())
            throw new UnableToLoginException();

        $response = $this->soapClient->setCommunityContext($params);

        if ($response->return)
            return true;

        return false;
    }

    public function assignPstnToAdmtiveDomain($params)
    {
        if (!$this->getPstnNumber(['number' => $params['selectedNumber']]))
            throw new PSTNNumberNotFoundException('PSTN Number: '.$params['selectedNumber']);

        $this->init();

        $response = $this->soapClient->assignPlmnPstnToAdmtiveDomain($params);

        if (!$response->return)
            return false;

        return true;
    }

    public function assignPstnToTrunk($context, $params)
    {
        $this->init();

        return $this->soapClient->assignPstnToPbxTrunking($params);
    }

    public function unassignPstn($params)
    {
        $this->init();

        return $this->soapClient->unAssignPstnToPbxTrunking($params);
    }

    public function getEnterprise($parameters)
    {
        if (!$enterprises = $this->getEnterprises($parameters))
            return false;

        return head($enterprises);
    }

    public function getEnterprises($parameters = [])
    {
        $this->init();

        $response = $this->soapClient->getEnterprise($parameters);

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return array(new Enterprise($response->return->elements));
        } else {
            foreach ($response->return->elements as $element) {
                $enterprises[] = new Enterprise($element);
            }

            return $enterprises;
        }
    }

    public function createEnterprise($context, Enterprise $enterprise)
    {
        if (!$this->getAdministrativeDomain(['name' => $context])) {
            $adm = new AdministrativeDomain([
                'name' => $context,
                'type' => 'Service Provider',
            ]);
            $this->createAdministrativeDomain($adm);
        }

        $this->init($context);

        $params = [
            'name' => $enterprise->name,
            'fullName' => $enterprise->fullName,
            'billingAccountCustom1' => $enterprise->billingAccountCustom1,
            'billingAccountCustom2' => $enterprise->billingAccountCustom2,
            'maxExternalConnections' => $enterprise->maxExternalConnections,
            'maxIVRConnections' => $enterprise->maxIVRConnections,
            'internalDialplan' => $enterprise->internalDialplan,
            'ipbx' => $enterprise->ipbx,
            'pilotNumber' => $enterprise->pilotNumber,
            'defaultLanguage' => $enterprise->defaultLanguage,
            'operatorPrefix' => $enterprise->operatorPrefix,
            'countryCode' => $enterprise->countryCode,
            'ussdDirectoryLookupPolicy' => $enterprise->ussdDirectoryLookupPolicy,
        ];

        if (!$response = $this->soapClient->createEnterprise($params))
            return false;

        return new Enterprise($response->return);
    }

    public function updateEnterprise($params)
    {
        $this->init();

        return new Enterprise($this->soapClient->updateEnterprise($params)->return);
    }

    public function deleteEnterprise($params)
    {
        $this->init();

        if (!$this->soapClient->deleteEnterprise($params))
            return false;

        return true;
    }

    public function getDialPrefix($context, $parameters)
    {
        $this->init($context);

        $response = $this->soapClient->getDialPrefix($parameters);

        if (!$response->return || $response->return->number == 0)
            return false;

        return new DialPrefix($response->return->elements);
    }

    public function createDialPrefix($context, DialPrefix $dialPrefix)
    {
        $this->init($context);

        $params = [
            'name' => $dialPrefix->name,
            'prefix' => $dialPrefix->prefix,
            'gateway' => $dialPrefix->gateway,
        ];

        $response = $this->soapClient->createDialPrefix($params);

        if (!$response->return)
            return false;

        return new DialPrefix($response->return);
    }

    public function createSite($context, Site $site)
    {
        $this->init($context);

        $params = [
            'name' => $site->name,
            'location' => $site->location,
            'isDefaultSite' => $site->isDefaultSite,
        ];

        $response = $this->soapClient->createSite($params);

        if (!$response->return)
            return false;

        return new Site($response->return);
    }

    public function updateSite($context, $params)
    {
        $this->init($context);

        return new Site($this->soapClient->updateSite($params)->return);
    }

    public function createSpeedDial($context, $dialPrefix, SpeedDial $speedDial)
    {
        $this->init($context);

        if (!isE164format($speedDial->externalDestination))
            $speedDial->externalDestination = $dialPrefix . $speedDial->externalDestination;

        $params = [
            'extension' => $speedDial->extension,
            'externalDestination' => $speedDial->externalDestination,
            'label' => $speedDial->label,
        ];

        $response = $this->soapClient->createSpeedDial($params);

        if (!$response->return)
            return false;

        return new SpeedDial($response->return);
    }

    public function getSpeedDials($context)
    {
        $this->init($context);

        $response = $this->soapClient->getSpeedDial();

        if (!$response->return || $response->return->number == 0) {
            return [];
        } elseif ($response->return->number == 1) {
            return array(new SpeedDial($response->return->elements));
        } else {
            foreach ($response->return->elements as $element) {
                $speedDials[] = new SpeedDial($element);
            }

            return $speedDials;
        }
    }

    public function getSpeedDial($context, $parameters)
    {
        $this->init($context);

        $response = $this->soapClient->getSpeedDial($parameters);

        if (!$response->return || $response->return->number == 0)
            return null;
        else
            return new SpeedDial($response->return->elements);
    }

    public function updateSpeedDial($context, $parameters)
    {
        $this->init($context);

        $response = $this->soapClient->updateSpeedDial($parameters);

        if (!$response->return)
            return false;

        return new SpeedDial($response->return);
    }

    public function deleteSpeedDial($context, $parameters)
    {
        $this->init($context);

        return $this->soapClient->deleteSpeedDial($parameters);
    }

    public function getIVRServices($context, $name)
    {
        $this->init($context);

        $params = [
            'ivrName' => $name
        ];

        $response = $this->soapClient->getIVRService($params);

        if (!$response->return || $response->return->number == 0) {
          return [];
        } elseif ($response->return->number == 1) {
          return array(new IVRService($response->return->elements));
        } else {
            foreach ($response->return->elements as $element) {
                $services[] = new IVRService($element);
        }

          return $services;
        }
    }

    public function createIVRService($context, IVRService $ivr)
    {
        $this->init($context);

        $params = [
            'extension' => $ivr->extension,
            'label' => $ivr->label,
            'ivrName' => $ivr->ivrName,
        ];

        $response = $this->soapClient->createIVRService($params);

        if (!$response->return)
            return false;

        return new IVRService($response->return);
    }

    public function deleteIVRService($context, $params)
    {
        $this->init($context);

        return $this->soapClient->deleteIVRService($params);
    }

    public function getServices($context, $name)
    {
        $this->init($context);

        $params = [
            'serviceName' => $name
        ];

        $response = $this->soapClient->getService($params);

        if (!$response->return || $response->return->number == 0) {
          return [];
        } elseif ($response->return->number == 1) {
          return array(new Service($response->return->elements));
        } else {
            foreach ($response->return->elements as $element) {
                $services[] = new Service($element);
        }

          return $services;
        }
    }

    public function createService($context, Service $service)
    {
        $this->init($context);

        $params = [
            'label' => $service->label,
            'serviceName' => $service->serviceName,
        ];

        $response = $this->soapClient->createService($params);

        if (!$response->return)
            return false;

        return new Service($response->return);
    }

    public function deleteService($context, $params)
    {
        $this->init($context);

        return $this->soapClient->deleteService($params);
    }

    public function setExtensionsInExtensionGroup($context, $selectedExtension, $extensions)
    {
        $this->init($context);

        $params = [
            'selectedExtension' => $selectedExtension,
            'extensions' => $extensions,
        ];

        if (!$this->soapClient->setExtensionsInExtensionGroup($params))
            return false;

        return true;
    }

    public function getExtensionGroup($context, $parameters)
    {
        $this->init($context);

        $response = $this->soapClient->getExtensionGroup($parameters);

        if (!$response->return || $response->return->number == 0)
            return null;
        else
            return new ExtensionsGroup($response->return->elements);
    }

    public function getExtensionGroups($context)
    {
        $this->init($context);

        $response = $this->soapClient->getExtensionGroup();

        if (!$response->return || $response->return->number == 0) {
          return [];
        } elseif ($response->return->number == 1) {
          return array(new ExtensionsGroup($response->return->elements));
        } else {
          foreach ($response->return->elements as $element) {
              $extensionsGroup[] = new ExtensionsGroup($element);
          }

          return $extensionsGroup;
        }
    }

    public function listExtensionsInGroupAddress($context, $parameters)
    {
        $this->init($context);

        $response = $this->soapClient->listExtensionsInGroupAddress($parameters);

        if (!$response->return)
          return [];

        return explode(',', $response->return);
    }

    public function createExtensionGroup($context, ExtensionsGroup $extensionsGroup)
    {
        $this->init($context);

        $params = [
            'extension' => $extensionsGroup->extension,
            'label' => $extensionsGroup->label,
            'syndicalPause' => $extensionsGroup->syndicalPause,
        ];

        return new ExtensionsGroup($this->soapClient->createExtensionGroup($params)->return);
    }

    public function updateExtensionGroup($context, $params)
    {
        $this->init($context);

        $response = $this->soapClient->updateExtensionGroup($params);

        if (!$response->return)
            return false;

        return new ExtensionsGroup($response->return);
    }

    public function deleteExtensionGroup($context, $params)
    {
        $this->init($context);

        return $this->soapClient->deleteExtensionGroup($params);
    }

    public function getSite($context, $parameters)
    {
        $this->init($context);

        $response = $this->soapClient->getSite($parameters);

        if (!$response->return || $response->return->number == 0)
            return false;

        return new Site($response->return->elements);
    }
}
