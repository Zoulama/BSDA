<?php

namespace Provisioning\Centile;

use Provisioning\Centile\Device;
use Provisioning\Centile\Contracts\DeviceManufacturerAPI;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;

class GigasetDevice extends Device implements DeviceManufacturerAPI
{
    public function registerOnManufacturerAPI($secret = null)
    {
        $client = new HttpClient;
        try {
            $response = $client->post(config('app.devices.manufacturer_api_url.gigaset'), [
                'form_params' => [
                    'mac' => macAddress($this->physicalID),
                    'macId' => $secret,
                ],
            ]);
        } catch (\Exception $e) {

        }
    }

    public function unregisterFromManufacturerAPI()
    {
        $client = new HttpClient;
        try {
            $response = $client->delete(config('app.devices.manufacturer_api_url.gigaset'), [
                'query' => [
                    'mac' => macAddress($this->physicalID),
                ],
            ]);
        } catch (\Exception $e) {

        }
    }

    public function getStatusFromManufacturerAPI()
    {
      $client = new HttpClient;
      try {
          $response = $client->get(config('app.devices.manufacturer_api_url.gigaset'), [
              'query' => [
                  'mac' => macAddress($this->physicalID),
              ],
          ]);
          return true;
      } catch (\Exception $e) {
          return false;
      }
    }
}
