<?php

namespace Provisioning\Listeners;

use Provisioning\Events\PrestationTerminated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Provisioning\CentilePrestationTypes;
use PBXTrunking;
use CentileTRK;

class DeleteFromCentile
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PrestationTerminated  $event
     * @return void
     */
    public function handle(PrestationTerminated $event)
    {
        $prestation = $event->prestation;

        switch ($prestation->type) {
            case CentilePrestationTypes::TRUNK_PSTN:
                CentileTRK::releasePstn($prestation->getValue());
                break;
        }
    }
}
