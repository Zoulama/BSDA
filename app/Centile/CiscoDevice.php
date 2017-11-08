<?php

namespace Provisioning\Centile;

use Provisioning\Centile\Device;
use Provisioning\Centile\Contracts\DeviceManufacturerAPI;

class CiscoDevice extends Device implements DeviceManufacturerAPI
{
    public function registerOnManufacturerAPI($secret = null)
    {

    }

    public function unregisterFromManufacturerAPI()
    {

    }

    public function getStatusFromManufacturerAPI()
    {
      return false;
    }

}
