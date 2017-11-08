<?php namespace Provisioning\Helpers;

use Session;
use Redirect;
use Provisioning\Helpers\BsodHelper;


class InfoClientBsodHelper{

	public static function customerInfos($data){
		$res = BsodHelper::checkDtataClient('infosclients');
		$res['data']->CustomerId = trim($data['CustomerId']);
		$res['data']->ExternalId = trim($data['ExternalId']);
		$resData = ['request' =>  $res['data']];

		$result = $res['client']->GetCustomerInfos($resData);
		
		if (is_soap_fault($result)){
			Session::flash('error_message', trans('appointments.soap_error'));
			return redirect()->route('Appointment.show');
		}

		return $result;
	}

	public static function getCustomerInfosBis($data){
		$res = BsodHelper::checkDtataClient('infosclients');
		$res['data']->CustomerId = trim($data['CustomerId']);
		$res['data']->ExternalId = trim($data['ExternalId']);
		$resData = ['request' =>  $res['data']];

		$result = $res['client']->GetCustomerInfosBis($resData);
		if (is_soap_fault($result)){
			Session::flash('error_message', trans('appointments.soap_error'));
			return redirect()->route('Appointment.show');
		}
		return $result;
	}

}