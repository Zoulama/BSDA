<?php namespace Provisioning\Http\Controllers;

use URL;
use Carbon\Carbon;
use Calendar;
use Redirect;
use Session;
use DB;
use Input;
use Provisioning\BsodClient;
use Provisioning\Http\Requests;
use Provisioning\Appointment;
use Provisioning\EligibilityAddress;
use MaddHatter\LaravelFullcalendar\Event;
use Provisioning\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Provisioning\Helpers\AppointmentHelper;
use Provisioning\Helpers\AbandonHelper;
use Provisioning\Helpers\BsodHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AppointmentController extends BaseController {

	public function index($id,$client_id,$typeRDV)
	{
		BsodHelper::controlRdvType($typeRDV);
		if ($typeRDV === 'customer') {
			$client_bsod = BsodClient::find($id);
			try {
				$client_bsod = BsodClient::findOrFail($id);
			} catch (ModelNotFoundException $er) {
				Session::flash('error_message', trans('appointments.customer_not_exist'));
				return redirect()->route('ClientBsod.index');
			}

			if (is_null($client_bsod->customerId)) {
				Session::flash('error_message', trans('appointments.error_notyet_customer'));
				return redirect()->route('ClientBsod.index');
			}

			$bsod_client_id = $client_bsod->id;
			$eligibilityAddress_id = $client_bsod->eligibility_address_id;
		}else{
			$eligibilityAddress_id = $id;
		}

		$bsod_client_id = isset($bsod_client_id) ? $bsod_client_id : '';
		$eligibilityAddress = EligibilityAddress::find($eligibilityAddress_id);//dd($eligibilityAddress);
		$id_prise = $eligibilityAddress->accomodationId;
		$typeRv = BsodHelper::$typeRV[$typeRDV];

		return view('bsod.appointments.index',compact('typeRv','id_prise','typeRDV','eligibilityAddress_id','bsod_client_id','client_id'));
	}

	public function getDatatable(Request $request){
		return AppointmentHelper::getData($request);
	}

	public function showAppointement(){
		return view('bsod.appointments.appointement_list');
	}

	public function parseDate($value){
		$hour = explode('-', $value->ShiftDesc);
		$startDate = (strlen($hour[0]) == 1) ? $value->Date.'T0'.$hour[0].'00': $value->Date.'T'.$hour[0].'00';
		$endDate = (strlen($hour[1]) == 1) ? $value->Date.'T0'.$hour[1].'00': $value->Date.'T'.$hour[1].'00';
	}

	public function getCalendar(Request $request)
	{
		$result = AppointmentHelper::getCalendar($request);

		if ($result->GetCalendarResult->code != 0) {
			$error_message = AppointmentHelper::errorCodeMessage($result->GetCalendarResult->code);
			Session::flash('error_message', $error_message);
			$typeRDV = $request->typeRDV;
			BsodHelper::controlRdvType($typeRDV);
			return Redirect::route('Appointment.index', array('id' => $request->eligibilityAddress_id,'clientId' => $request->clientId,'typeRDV' => $typeRDV));
		}

		$GetCalendarResult = $result->GetCalendarResult;
		$firstDay = explode('-', $request->startDate)[2];
		$calendars = [];
		if (isset($GetCalendarResult->calendar)) {
			foreach ($GetCalendarResult->calendar as $value) {
				$hour      = explode('-', $value->ShiftDesc);
				$startDate = (strlen($hour[0]) ==1) ? $value->Date.'T0'.$hour[0].'00': $value->Date.'T'.$hour[0].'00';
				$endDate   = (strlen($hour[1]) ==1) ? $value->Date.'T0'.$hour[1].'00' :$value->Date.'T'.$hour[1].'00';
				$varId     = explode(' ',$value->ScheduleID);
				$parse     = str_replace('/', '-', $varId[5]);
				$key      = $varId[0].'_'.$parse;

				$DataAppointement = [
					'ScheduleID'		=> $value->ScheduleID,
					'CalendarTypeDesc'	=> $value->CalendarTypeDesc,
					'Date'				=> $value->Date,
					'ShiftDesc'			=> $value->ShiftDesc,
					'startDate'			=> $startDate,
					'endDate'			=> $endDate,
					'appointmentType'	=> $request->appointmentType,
					'clientId'			=> $request->clientId,
				];

				if ($request->has('bsod_client_id') && $request->bsod_client_id != '') {
					$DataAppointement['bsod_client_id'] = $request->bsod_client_id;
				}

				$url = AppointmentHelper::getUrl($request,$key)['url'];
				$param = AppointmentHelper::getUrl($request,$key)['param'];

				session()->put($key,$DataAppointement);
				$calendars[] = Calendar::event(
					$value->CalendarTypeDesc,
					false,
					$startDate,
					$endDate,
					1,
					[
						'url' => URL::route($url,$param),
						'color'	=> '#327CCB',
						'backgroundColor' => '#327CCB',
					]
				);
			}
		}else {

		}

		$calendar = Calendar::addEvents($calendars)
			->setOptions([
				'firstDay' => $firstDay,
				'timeFormat' => 'H(:mm)',
				'height' => 600,
				'locale' => 'fr'
			])->setCallbacks([
			'viewRender' => 'function() {alert("Callbacks!");}'
		]);
		$idcalendar = substr($calendar->calendar(),9,17);
		return view('bsod.appointments.calendar',compact('calendar','idcalendar'));
	}

	public function bookProspectAppointment($eligibilityAddress_id,$id)
	{
			$DataProspect = session()->get($id);
			$Sid = AppointmentHelper::parseScheduleID($id);
			if ($DataProspect['ScheduleID'] == $Sid) {
				$Sid = $DataProspect['ScheduleID'];
			}

			try {
				$eligibilityAddress = EligibilityAddress::findOrFail($eligibilityAddress_id);
			} catch (ModelNotFoundException $er) {
				Session::flash('error_message', trans('eligibilityadress.adress_not_exist'));
				return redirect()->route('bsod.index');
			}

			$externalSubscriberId = substr(uniqid(rand(),true),10,10);
			$externalSubscriberId = 'CDP'.strtoupper($externalSubscriberId);

			$data = [
				'externalSubscriberId' => $externalSubscriberId,
				'ScheduleID' => $DataProspect['ScheduleID'],
			];

			$result = AppointmentHelper::bookProspectAppointment($data);

			if ($result->BookProspectAppointmentResult->code != 0) {
				$dataError = ['code' => $result->BookProspectAppointmentResult->code ,'url'=> 'Appointment.show'];
				return AppointmentHelper::getError($dataError);
			}

			$message_success = $result->BookProspectAppointmentResult->message;
			//$clientApi = LibClient::getClientById($DataProspect['clientId']);
			//$clientNom = explode(' ', $clientApi['clientNom']);
			$first_name = 'Gerard';//$clientNom[1];
			$last_name  = 'Dupond';//$clientNom[0];

			//$client = BsodClient::whereClientId($DataProspect['clientId'])->first();
			//if (!is_null($client)) {dd('sdfsdf');
			//	return false;
			//}

			$client = BsodClient::create([
					'first_name'				=> trim($first_name),
					'last_name'					=> trim($last_name),
//					'email'						=> trim($request->email),
//					'telephone'					=> trim($request->numContact),
//					'gender'					=> trim($request->civilite),
//					'client_type'				=> trim($request->typeClient),
//					'externalSubscriberId'		=> trim($request->codeClient),
					'accomodationId'			=> $eligibilityAddress->accomodationId,
					'clientID'					=> $DataProspect['clientId'],
					'eligibility_address_id'	=> $eligibilityAddress_id,
				]);

			Appointment::create([
					'externalSubscriberId' => $externalSubscriberId,
					'accomodationId'       => $eligibilityAddress->accomodationId,
					'ScheduleID'           => $DataProspect['ScheduleID'],
					'CalendarTypeDesc'     => $DataProspect['CalendarTypeDesc'],
					'appointment_date'     => $DataProspect['Date'],
					'ShiftDesc'            => $DataProspect['ShiftDesc'],
					'startDate'            => $DataProspect['startDate'],
					'endDate'              => $DataProspect['endDate'],
					'appointmentType'      => $DataProspect['appointmentType'],
					'type'                 => 'prospect',
					'bsod_client_id'       => $client->id,
			]);

			session()->forget($id);
			Session::flash('success_message', $message_success);
			return redirect()->route('Appointment.show');
	}

	public function editAppointment($id,$typeRDV){
		BsodHelper::controlRdvType($typeRDV);

		try {
			$appointment = Appointment::findOrFail($id);
		} catch (ModelNotFoundException $er) {
			Session::flash('error_message', trans('appointments.id_not_exist'));
			return redirect()->route('Appointment.show');
		}

		$id_prise = $appointment->accomodationId;
		$appointmentID = $id;
		$varId   = explode(' ',$appointment->ScheduleID);
		$parse     = str_replace('/', '-', $varId[5]);
		$ScheduleID      = $varId[0].'_'.$parse;
		$externalSubscriberId = $appointment->externalSubscriberId;
		$typeRv = BsodHelper::$typeRV[$typeRDV];

		if($typeRDV === 'customer') {
			if (is_null($appointment->bsodClient)) {
				$client_bsod = BsodClient::find($appointment->bsod_client_id);
			}else{
				$client_bsod = $appointment->bsodClient;
			}

			if (is_null($client_bsod->customerId)) {
				Session::flash('error_message', trans('appointments.error_notyet_customer'));
				return redirect()->route('ClientBsod.index');
			}
		}

		return view('bsod.appointments.index',compact('appointmentID','typeRv','id_prise','typeRDV','ScheduleID','externalSubscriberId'));
	}

	public function changeAppointment($id,$new){
			$DataProspect = session()->get($new);
			$SidNew = AppointmentHelper::parseScheduleID($new);

			try {
				$appointement = Appointment::findOrFail($id);
			} catch (ModelNotFoundException $er) {
				Session::flash('error_message', trans('appointments.id_not_exist'));
				return redirect()->route('Appointment.show');
			}

			if (is_null($appointment->bsodClient)) {
				$clientBsod = BsodClient::find($appointment->bsod_client_id);
			}else{
				$clientBsod = $appointment->bsodClient;
			}

			if (!is_null($clientBsod)) {
				if (!is_null($clientBsod->customerId) && !is_null($clientBsod->identifiantAS != '0')) {
					$dataCustom = [
						'noAbo'                => $clientBsod->customerId,
						'workOrderId'          => $clientBsod->identifiantAS,
						'ScheduleID'           => $SidNew,
						'appointmentType'      => $DataProspect['appointmentType'],
						];
					$result = AppointmentHelper::changeCustomerAppointment($dataCustom);
					$code = $result->ChangeCustomerAppointmentResult->code;
					$message_success = $result->ChangeCustomerAppointmentResult->message;
				}
			}else {
				$data = [
					'externalSubscriberId' => $appointement->externalSubscriberId,
					'ScheduleID' => $SidNew,
				];

				$result = AppointmentHelper::changeProspectAppointment($data);
				$code = $result->ChangeProspectAppointmentResult->code;
				$message_success = $result->ChangeProspectAppointmentResult->message;
			}

			if ($code != 0) {
				$dataError =['code' => $code ,'url'=> $appointement->id];
				return AppointmentHelper::getError($dataError);
			}else {
				$appointement->appointment_date = $DataProspect['Date'];
				$appointement->ShiftDesc = $DataProspect['ShiftDesc'];
				$appointement->startDate = $DataProspect['startDate'];
				$appointement->endDate = $DataProspect['endDate'];
				$appointement->ScheduleID = $DataProspect['ScheduleID'];
				$appointement->save();
			}

			session()->forget($new);
			Session::flash('success_message', $message_success);
			return redirect()->route('Appointment.show');
	}

	public function changeProspectAppointment($id,$new){
		$DataProspect = session()->get($new);
		$SidNew = AppointmentHelper::parseScheduleID($new);

		try {
			$appointment = Appointment::findOrFail($id);
		} catch (ModelNotFoundException $er) {
			Session::flash('error_message', trans('appointments.id_not_exist'));
			return redirect()->route('Appointment.show');
		}

		$data = [
			'externalSubscriberId' => $appointement->externalSubscriberId,
			'ScheduleID' => $SidNew,
		];

		$result = AppointmentHelper::changeProspectAppointment($data);
		$code = $result->ChangeProspectAppointmentResult->code;
		$message_success = $result->ChangeProspectAppointmentResult->message;

		if ($code != 0) {
			$dataError =['code' => $code ,'accomodationId'=> 'Appointment.show'];
			return AppointmentHelper::getError($dataError);
		}else {
			$appointement->appointment_date       = $DataProspect['Date'];
			$appointement->ShiftDesc  = $DataProspect['ShiftDesc'];
			$appointement->startDate  = $DataProspect['startDate'];
			$appointement->endDate    = $DataProspect['endDate'];
			$appointement->ScheduleID = $DataProspect['ScheduleID'];
			$appointement->save();
		}

		session()->forget($new);
		Session::flash('success_message', $message_success);
		return redirect()->route('Appointment.show');
	}

	public function CustomerAppointement($id_comm){

		try {
			$getOrder = Order::findOrFail($id_comm);
		} catch (ModelNotFoundException $er) {
			Session::flash('error_message', trans('commande.order_unknown'));
			return redirect()->route('ClientBsod.index');
		}

		try {
			$client = Client::findOrFail($getOrder->client_id);
		} catch (ModelNotFoundException $er) {
			Session::flash('error_message', trans('commande.clint_order_unknown',['order_id' => $id_comm]));
			return redirect()->route('ClientBsod.index');
		}

		return view('bsod.appointments.customer_appointement',compact('getOrder','client'));
	}

	public function bookCustomerAppointment($id,$key){

		$DataCustomerAppointement = session()->get($key);
		$sid = AppointmentHelper::parseScheduleID($key);
		$client_bsod = BsodClient::find($id);

		if (!is_null($client_bsod)) {
			$data = [
				'noAbo'				=> $client_bsod->customerId,
				'scheduleId' 		=> $sid,
				'appointmentType' 	=> $DataCustomerAppointement['appointmentType'],
			];

			$result = AppointmentHelper::bookCustomerAppointment($data);

			if ($result->BookCustomerAppointmentResult->code != 0) {
				$dataError = ['code' => $result->BookCustomerAppointmentResult->code ,'url'=> 'Appointment.show'];
				return AppointmentHelper::getError($dataError);
			}

			$message_success = $result->BookCustomerAppointmentResult->message;

			Appointment::create([
					'externalSubscriberId' => $client_bsod->externalSubscriberId,
					'accomodationId'       => $client_bsod->accomodationId,
					'ScheduleID'           => $DataProspect['ScheduleID'],
					'CalendarTypeDesc'     => $DataProspect['CalendarTypeDesc'],
					'appointment_date'     => $DataProspect['Date'],
					'ShiftDesc'            => $DataProspect['ShiftDesc'],
					'startDate'            => $DataProspect['startDate'],
					'endDate'              => $DataProspect['endDate'],
					'appointmentType'      => $DataProspect['appointmentType'],
					'type'                 => 'customer',
			]);
		}

		Session::flash('success_message', $message_success);
		return redirect()->route('Appointment.show');
	}

	public function deleteAppointement($id,$typeRDV){
		BsodHelper::controlRdvType($typeRDV);

		try {
			$appointment = Appointment::findOrFail($id);
		} catch (ModelNotFoundException $er) {
				Session::flash('error_message', trans('appointments.id_not_exist'));
				return redirect()->route('Appointment.show');
		}

		if ($typeRDV === 'prospect') {
			$data = [
				'externalSubscriberId' 	=> $appointment->externalSubscriberId,
			];

			$result = AppointmentHelper::removeProspectAppointment($data);
			$code = $result->RemoveProspectAppointmentResult->code;
			$message_success = $result->RemoveProspectAppointmentResult->message;
		}else {
			$client_bsod_data = $appointment->bsodClient;
			$data = [
				'noAbo'				=> $client_bsod_data->customerId,
				'workOrderId'		=> $client_bsod_data->identifiantAS,
				'appointmentType'	=> $appointment->appointmentType,
			];
			$result = AppointmentHelper::removeCustomerAppointment($data);
			$code = $result->RemoveCustomerAppointmentResult->code;
			$message_success = $result->RemoveCustomerAppointmentResult->message;
		}

		if ($code != 0) {
			$dataError =['code' => $code ,'url' => 'Appointment.show'];
			return AppointmentHelper::getError($dataError);
		}

		$appointment->delete();
		Session::flash('success_message', $message_success);
		return redirect()->route('Appointment.show');
	}

	public function detailProspect($id,$typeRDV){
		BsodHelper::controlRdvType($typeRDV);
		try {
			$dataprospect = Appointment::findOrFail($id);
		} catch (ModelNotFoundException $er) {
			Session::flash('error_message', trans('appointments.id_not_exist'));
			return redirect()->route('Appointment.show');
		}
		$calendars[] = Calendar::event(
			$dataprospect->CalendarTypeDesc,
			true,
			$dataprospect->startDate,
			$dataprospect->endDate,
			0,
			[
				'color'				=> '#327CCB',
				'backgroundColor'	=> '#327CCB',
			]
		);
		$var = explode('T', $dataprospect->startDate);
		$firstDay = explode('-',$var[0])[2];
		$calendar = Calendar::addEvents($calendars)
			->setOptions([
				'firstDay'  => $firstDay,
				'timeFormat' => 'H(:mm)',
				'height'   => 300,
			])->setCallbacks([
			'viewRender' => 'function() {alert("Callbacks!");}'
		]);

		$params['idcalendar'] = substr($calendar->calendar(),9,17);
		$hours = explode('-', $dataprospect->ShiftDesc);
		$params['date_horaire'] = $dataprospect->Date.' De '.$hours[0].'H - '.$hours[1].'H';
		$params['adresse'] = EligibilityAddress::where('accomodationId','=',$dataprospect->accomodationId)->first()->street_number_complement;
		$params['CalendarTypeDesc'] = $dataprospect->CalendarTypeDesc;
		$params['id_prise'] = $dataprospect->accomodationId;
		$params['id'] = $dataprospect->id;
		$params['bsodClient'] = $dataprospect->bsodClient;

		return view('bsod.appointments.detail_prospect',compact('calendar','params'));
	}


	public function suspendCustomerAppointment($id){
		$data = AppointmentHelper::customerUpdate($id);
		if (!empty($data)) {
			$result = AppointmentHelper::suspendCustomerAppointment($data);
		}
		return $result;
	}

	public function removeCustomerAppointment($id){
		$data = AppointmentHelper::customerUpdate($id);
		if (!empty($data)) {
			$result = AppointmentHelper::removeCustomerAppointment($data);
		}
		return $result;
	}
}