<?php

namespace Provisioning\Centile\Contracts;

interface DeviceManufacturerAPI
{
    public function registerOnManufacturerAPI($secret = null);
    public function unregisterFromManufacturerAPI();
    public function getStatusFromManufacturerAPI();
}
