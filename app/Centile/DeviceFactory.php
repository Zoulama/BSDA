<?php

namespace Provisioning\Centile;

use CentileENT;

class DeviceFactory
{
    public static function make($params)
    {
        $deviceModel = CentileENT::getDeviceModel($params->model);
        $className = '\Provisioning\Centile\\' . ucfirst($deviceModel->manufacturer) . 'Device';
        return new $className($params);
    }
}