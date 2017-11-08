<?php  namespace Provisioning\Helpers;

use Illuminate\Support\Facades\Facade;

class LibAuthUser extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'LibAuthUserClass'; }

}