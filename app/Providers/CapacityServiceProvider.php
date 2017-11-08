<?php

namespace Provisioning\Providers;

use Illuminate\Support\ServiceProvider;
use Provisioning\Helpers\Auth\AuthUser;
use Provisioning\Helpers\LibClient;

class CapacityServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app['view']->composer(['layouts.app'], function ($view) {
			$cap_search = [];
			$cap_client = [];
			$client_name = '';
			$cap_prestation = [];
			$prestation_label='';
			$info_app_current = AuthUser::getApplication(env('APPTOKEN'));
			$view->app_name = $info_app_current['name'];
			$view->app_logo = $info_app_current['logo'];
			$view->user_name = AuthUser::getUserName();
			$view->list_applications = AuthUser::getApplications();
			$view->url_profile = AuthUser::getUrlProfile();
			$capacities = AuthUser::getCapacities();
			$data = $view->getData();
			if (isset($data['client'])) {
				$client_name = $data['client']['clientNom'];
			}
			if (isset($data['prestation'])) {
				$prestation = $data['prestation'];
				$prestation_label= '<img src="' . $prestation->icon() . '">  ' . htmlspecialchars($prestation->caption());
			}
			foreach ($capacities as $value) {
				switch ($value['capacity']) {
					case 'search':
						$cap_search[] = $value;
						break;
					case 'client':
						if (!isset($data['client'])) break;
						$value['url'] = str_replace(['__CLIENTID__'], [$data['client']['clientID']], $value['url']);
						$cap_client[] = $value;
						break;
					case 'prestation':
						if (!isset($data['prestation'])) break;
						$value['url'] = str_replace(['__PRESTAID__'], [$data['prestation']['prestationID']], $value['url']);
						$cap_prestation[] = $value;
						break;
				}
			}
			$view->cap_search = $cap_search;
			$view->cap_client = $cap_client;
			$view->client_name = $client_name;
			$view->cap_prestation = $cap_prestation;
			$view->prestation_label= $prestation_label;
		});
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
}
