<?php

namespace Provisioning\Providers;

use Illuminate\Support\ServiceProvider;
use Provisioning\Helpers\Saml\LibSimpleSamlphp;

class SamlServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('LibSimpleSamlphp', function($app)
        {
            return new LibSimpleSamlphp();
        });
    }
}
