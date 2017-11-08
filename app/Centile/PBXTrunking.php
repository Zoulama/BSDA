<?php

namespace Provisioning\Centile;

use Provisioning\Centile\Site;
use Provisioning\Firewall;
use Illuminate\Support\Facades\Log;

use CentileTRK;
use DB;

class PBXTrunking
{
    public $forceCodecOrderOutgoingCalls;
    public $sipHostPorts;
    public $networkIDSIPGw;
    public $name;
    public $forceCodecOrderIncomingCalls;
    public $location;
    public $maxCalls;
    public $label;
    public $rtpRedirection;
    public $callPolicy;
    public $countryCode;
    public $allowedCodecs;
    public $authUsername;
    public $checkPolicyOnAssertedCallerID;
    public $authPassword;
    public $requestAuth;
    public $checkOriginatingSipHost;
    public $checkAAA;
    public $callTimeout;
    public $defaultPstn;
    public $operatorPrefix;
    public $contactHostPorts;
    public $registration;

    public function __construct($params)
    {
        foreach ($params as $param => $value) {
            // hack to convert checkAAAP property to checkAAA
            // because the API returns checkAAAP while the documentation asks for checkAAA
            if ($param == 'checkAAAP') {
                $this->checkAAA = $value;
                continue;
            }

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
        return $this->$property;
    }

    public static function getDefaultResellerContext($clientId)
    {
        return 'TRUNK-' . $clientId;
    }

    public static function generateTrunkName($clientId, $prestationId)
    {
        return 'TRUNK-' . $clientId . '-' . $prestationId;
    }

    public static function generatePassword()
    {
        $specialChars = '_-@';
        $alphaDigit = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $passwordLength = config('centile.trunk.password_length');
        $password = '';
        $password .= self::randomCharOfString($specialChars);
        for ($i = 0; strlen($password) < $passwordLength; ++$i) {
            $password .= self::randomCharOfString($alphaDigit);
        }

        return str_shuffle($password);
    }

    protected static function randomCharOfString($string)
    {
        return substr($string, random_int(0, strlen($string) - 1), 1);
    }

    public static function generateUsername($clientId, $prestationId)
    {
        return $clientId .'-'. $prestationId;
    }

    public function withPrestationId($prestationId)
    {
        $this->prestationId = $prestationId;
        return $this;
    }

    public function withForwardings($context)
    {
        $this->forwardings = CentileTRK::getForwardingsByAssignedTo($context, $this->name);
        return $this;
    }

    public function changeLocationAttribute()
    {
        $this->location = substr($this->location, strlen(Site::AREA_CODE_PREFIX), strlen($this->location));
        return $this;
    }

    public function withPstns($context)
    {
        $pstnsFromDb = DB::connection('istra')
            ->table('PSTNPOOL')
            ->select('PSTNPOOL.PSTNNUMBER')
            ->join('PBXTRUNKING', 'PBXTRUNKING.ASSIGNABLEOBJECTID', '=', 'PSTNPOOL.ASSIGNEDTOID')
            ->where('PBXTRUNKING.NAME', $this->name)
            ->pluck('PSTNPOOL.PSTNNUMBER');

        foreach ($pstnsFromDb as $pstnFromDb)
            $pstns[] = CentileTRK::getPstnNumber($pstnFromDb);

        $this->pstns = $pstns;
        return $this;
    }

    public function withFirewall($context)
    {
        $this->firewalls = Firewall::getIpAddressesFromContext($context);
        return $this;
    }
}
