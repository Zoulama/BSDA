<?php

namespace Provisioning;

use Storage;
use GuzzleHttp\Client as HTTPClient;
use Illuminate\Support\Facades\Log;

class Firewall
{
    public static function registerCentrexIpAddress($context, $ipAddress)
    {
        if (env('IP_ADDRESS_CENTREX_REGISTER_URL')) {
            try {
                if (env('IP_ADDRESS_CENTREX_REGISTER_LOG_FILE'))
                    Storage::append(env('IP_ADDRESS_CENTREX_REGISTER_LOG_FILE'), (new \Datetime)->format('Y-m-d H:i:s') . ': CONTEXT: ' . $context . ' IP: ' . $ipAddress);
                $client = new HTTPClient;
                $response = $client->request('POST', env('IP_ADDRESS_CENTREX_REGISTER_URL'), [
                    'form_params' => [
                        'ip' => $ipAddress,
                        'context' => $context,
                    ]
                ]);
            } catch (\Exception $e) {
                if (env('IP_ADDRESS_CENTREX_REGISTER_LOG_FILE'))
                    Storage::append(env('IP_ADDRESS_CENTREX_REGISTER_LOG_FILE'), (new \Datetime)->format('Y-m-d H:i:s') . ': Error sending ' . $ipAddress);
            }
        }
    }

    public static function registerTrunkIpAddress($context, $ipAddress)
    {
        if (env('IP_ADDRESS_TRUNK_REGISTER_URL')) {
            try {
                if (env('IP_ADDRESS_TRUNK_REGISTER_LOG_FILE'))
                    Storage::append(env('IP_ADDRESS_TRUNK_REGISTER_LOG_FILE'), (new \Datetime)->format('Y-m-d H:i:s') . ': CONTEXT: ' . $context . ' IP: ' . $ipAddress);
                $client = new HTTPClient;
                $response = $client->request('POST', env('IP_ADDRESS_TRUNK_REGISTER_URL'), [
                    'form_params' => [
                        'ip' => $ipAddress,
                        'context' => $context,
                    ]
                ]);
            } catch (\Exception $e) {
                if (env('IP_ADDRESS_TRUNK_REGISTER_LOG_FILE'))
                    Storage::append(env('IP_ADDRESS_TRUNK_REGISTER_LOG_FILE'), (new \Datetime)->format('Y-m-d H:i:s') . ': Error sending ' . $ipAddress);
            }
        }
    }

    public static function getIpAddressesFromContext($context)
    {
        if (env('IP_ADDRESS_ACL_API_URL')) {
            try {
                $client = new HTTPClient;
                $response = $client->get(env('IP_ADDRESS_ACL_API_URL') . '?context=' . $context);
                $data = json_decode($response->getBody());
                return $data;
            } catch (\Exception $e) {
                return false;
            }
        }
    }
}
