<?php

namespace Provisioning\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Provisioning\Centile\User;
use Provisioning\Centile\Device;
use Provisioning\Centile\Line;
use Provisioning\Centile\Terminal;
use Provisioning\Centile\ExtensionsGroup;
use Provisioning\Centile\SpeedDial;
use Provisioning\Centile\PSTNNumber;
use Provisioning\Centile\Forwarding;
use Provisioning\Centile\LogicalTerminal;
use Provisioning\Client;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\CentilePrestationTypes;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Provisioning\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();

        Route::bind('user', function ($login) {
            if (!$user = User::find($login))
                abort(404);

            return $user;
        });

        Route::bind('client', function ($clientId) {
            if (!$client = Client::find($clientId))
                abort(404);

            return $client;
        });

        Route::bind('device', function ($mac) {
            $prestation = Route::input('prestation');

            if (!$device = Device::find($prestation->getCentileContext(), $mac))
                abort(404);

            return $device;
        });

        Route::bind('prestation', function ($prestationId) {
            if (!$prestation = Prestation::withId($prestationId)
                ->first())
                abort(404);

            return $prestation;
        });

        Route::bind('line', function ($lineId) {
            $prestation = Route::input('prestation');
            $device = Route::input('device');

            if (!$line = Line::find($prestation->getCentileContext(), $device->physicalID, $lineId))
                abort(404);

            return $line;
        });

        Route::bind('terminal', function ($terminalId) {
            $prestation = Route::input('prestation');
            $device = Route::input('device');

            if(!$terminal = Terminal::find($prestation->getCentileContext(), $device->physicalID, $terminalId))
                abort(404);

            return $terminal;
        });

        Route::bind('extensionsGroup', function ($extension) {
            $prestation = Route::input('prestation');
            if (!$extGroup = ExtensionsGroup::find($prestation->getCentileContext(), $extension))
                abort(404);

            return $extGroup;
        });

        Route::bind('speedDial', function ($extension) {
            $prestation = Route::input('prestation');
            if (!$speedDial = SpeedDial::find($prestation->getCentileContext(), $extension))
                abort(404);

            return $speedDial;
        });

        Route::bind('pstn', function ($number) {
            if (!$pstn = PSTNNumber::find($number))
                abort(404);

            return $pstn;
        });

        Route::bind('logicalTerminal', function ($physicalId) {
            $prestation = Route::input('prestation');
            if (!$logicalTerminal = LogicalTerminal::find($prestation->getCentileContext(), $physicalId))
                abort(404);

            return $logicalTerminal;
        });

        Route::bind('forwarding', function ($forwardingId) {
            $prestation = Route::input('prestation');
            if (!$forwarding = Forwarding::find($prestation->getCentileContext(), $forwardingId))
                abort(404);

            return $forwarding;
        });
    }

    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => ['api'],
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    protected function mapWebRoutes()
    {
        Route::group([
            'namespace' => $this->namespace, 'middleware' => ['web'],
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }
}
