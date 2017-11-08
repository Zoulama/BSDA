<?php namespace Provisioning\Helpers;

use SoapClient;
use Cache;
use Session;
use Redirect;
use Provisioning\Helpers\BsodHelper;

class AbandonHelper{

	public static function  abandonCommande($data){
		$res = BsodHelper::checkDtataClient('abandon');

		if (isset($data['noAbo'])) {
			$res['data']->noAbo = trim($data['noAbo']);
		}

		$result = $res['client']->AbandonCommande($res['data']);;

		return $result;
	}
}