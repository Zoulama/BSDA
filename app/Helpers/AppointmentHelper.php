<?php namespace Provisioning\Helpers;

use SoapClient;
use Cache;
use Session;
use Redirect;
use Datatables;
use Illuminate\Http\Request;
use Provisioning\Appointment;
use Provisioning\Helpers\BsodHelper;
use Illuminate\Support\Facades\Lang;

class AppointmentHelper
{
	public static function getUrl($request,$key){
		if($request->has('ScheduleID') && $request->has('appointmentID')){
			$url = 'Appointment.change';
			$param = [$request->appointmentID,$key];
		}elseif($request->has('typeRDV')){
			switch ($request->typeRDV) {
				case 'prospect':
					$url = 'Appointment.prospect';
					$param = [$request->eligibilityAddress_id,$key];
					break;
				case 'customer':
					$url = 'Appointment.customer';
					$param = [$request->bsod_client_id,$key];
					break;
			}
		}

		return ['url' => $url, 'param' => $param];
	}

	public static function parseScheduleID($scheduleId){
		$scheduleId = str_replace('-', "/",$scheduleId);
		$scheduleId = str_replace('_','     ', $scheduleId);
		$scheduleId = trim($scheduleId);

		return $scheduleId;
	}

	public static function  getFunctionData($data){
		$res = BsodHelper::checkDtataClient("prisederendezvous");

		if (isset($data['noAbo'])) {
			$res['data']->noAbo = $data['noAbo'];
		}

		if (isset($data['scheduleId'])) {
			$res['data']->scheduleId = $data['scheduleId'];
		}

		if (isset($data['commentaire'])) {
			$res['data']->commentaire = $data['commentaire'];
		}

		if (isset($data['appointmentType'])) {
			$res['data']->appointmentType = $data['appointmentType'];
		}

		if (isset($data['Acts'])) {
			$res['data']->acts = $data['Acts'];
		}

		if (isset($data['Symptom'])) {
			$res['data']->symptom = $data['Symptom'];
		}

		if (isset($data['externalSubscriberId'])) {
			$res['data']->externalSubscriberId = $data['externalSubscriberId'];
		}

		if (isset($data['ScheduleID'])) {
			$res['data']->scheduleId = $data['ScheduleID'];
		}

		if (isset($data['workOrderId'])) {
			$res['data']->workOrderId = $data['workOrderId'];
		}

		return $res;
	}

	public static function getCalendar($request){
		$res = BsodHelper::checkDtataClient("prisederendezvous");
		$res['data']->accomodationId = trim($request->accomodationId);
		$res['data']->startDate = trim($request->startDate);
		$res['data']->endDate = trim($request->endDate);
		$res['data']->appointmentType = trim($request->appointmentType);
		$result = $res['client']->GetCalendar($res['data']);
		if (is_soap_fault($result)){
			Session::flash('error_message', trans('appointments.soap_error'));
			return redirect()->route('Appointment.show');
		}

		return $result;
	}

	public static function bookProspectAppointment($data){
		$res = self::getFunctionData($data);
		$result = $res['client']->BookProspectAppointment($res['data']);
		if (is_soap_fault($result)){
			Session::flash('error_message', trans('appointments.soap_error'));
			return redirect()->route('Appointment.show');
		}

		return $result;
	}

	public static function bookCustomerAppointment($data){
		$res = self::getFunctionData($data);
		$result = $res['client']->BookCustomerAppointment($res['data']);
		if (is_soap_fault($result)){
			//
		}

		return $result;
	}

	public static function changeProspectAppointment($data){
		$res = self::getFunctionData($data);
		$result = $res['client']->ChangeProspectAppointment($res['data']);
		if (is_soap_fault($result)){
			Session::flash('error_message', trans('appointments.soap_error'));
			return redirect()->route('Appointment.show');
		}

		return $result;
	}

	public static function changeCustomerAppointment($data){
		$res = self::getFunctionData($data);
		$result = $res['client']->ChangeCustomerAppointment($res['data']);
		if (is_soap_fault($result)){
			Session::flash('error_message', trans('appointments.soap_error'));
			return redirect()->route('Appointment.show');
		}

		return  $result;
	} 

	public static function suspendCustomerAppointment($data){
		$res = self::getFunctionData($data);
		$result = $res['client']->SuspendCustomerAppointment($res['data']);
		if (is_soap_fault($result)){
			//
		}

		return  $result;
	}

	public static function removeProspectAppointment($data){
		$res = BsodHelper::checkDtataClient("prisederendezvous");
		$res['data']->externalSubscriberId = $data['externalSubscriberId'];
		$result = $res['client']->RemoveProspectAppointment($res['data']);
		if (is_soap_fault($result)){
			Session::flash('error_message', trans('appointments.soap_error'));
			return redirect()->route('Appointment.show');
		}

		return $result;
	}

	public static function removeCustomerAppointment($data){
		$res = self::getFunctionData($data);
		$res['data']->noAbo = $data['noAbo'];
		$res['data']->workOrderId = $data['workOrderId'];
		$res['data']->appointmentType = $data['appointmentType'];
		$result = $res['client']->RemoveCustomerAppointment($res['data']);
		if (is_soap_fault($result)){
			Session::flash('error_message', trans('appointments.soap_error'));
			return redirect()->route('Appointment.show');
		}

		return  $result;
	}

	public static function customerUpdate($id){
		$res = CustomerAppointements::find($id);
		$data = [];
		if (!is_null($res)) {
			$data['noAbo']				= $res->noAbo;
			$data['workOrderId']		= $res->workOrderId;
			$data['appointmentType']	= $res->appointmentType;
		}

		return $data;
	}

	public static function errorCodeMessage($code){
		switch ($code) {
			case '30500':
				return trans('appointments.GetCalendar.ConsultationPeriodTooLong');
				break;
			case '30600':
				return trans('appointments.GetCalendar.StartDatePassed');
				break;
			case '30700':
				return trans('appointments.GetCalendar.StartDateTooClose');
				break;
			case '30800':
				return trans('appointments.GetCalendar.StartDateTooFar');
				break;
			case '30900':
				return trans('appointments.GetCalendar.StartDateLaterThanEndDate');
				break;
			case '31100':
				return trans('appointments.GetCalendar.EndDateTooFar');
				break;
			case '20700':
				return trans('appointments.GetCalendar.AddressIdInvalid');
				break;
			case '31300':
				return trans('appointments.BookProspectAppointment.AppointmentAlreadyExists');
				break;
			case '31000':
				return trans('appointments.General.AppointmentDateNoChange');
				break;
			case '31600':
				return trans('appointments.General.ActionInvalidvsAppointmentStatus');
				break;
			case '31700':
				return trans('appointments.General.ActionInvalidvsAppointmentDelayCode');
				break;
			case '10100':
				return trans('appointments.General.RetailerIdInvalid');
				break;
			case '10200':
				return trans('appointments.General.VendorCodeInvalid');
				break;
			case '10300':
				return trans('appointments.General.RetailerIdAndVendorCodeInvalid');
				break;
			case '80000':
				return trans('appointments.General.IncompatibleStatut');
				break;
			case '20100':
				return trans('appointments.General.CustomerNotExist');
				break;
			case '20300':
				return trans('appointments.General.SubscriberNumberInvalid');
				break;
			case '30100':
				return trans('appointments.General.NoAppointment');
				break;
			case '30200':
				return trans('appointments.General.AppointmentInvalid');
				break;
			case '30300':
				return trans('appointments.General.ScheduleIdInvalid');
				break;
			case '30400':
				return trans('appointments.General.ScheduleIdUnavailable');
				break;
			case '20900':
				return trans('appointments.General.WorkOrderIdInvalid');
				break;
			case '20210':
				return trans('appointments.General.ExternalIdNotExiste');
				break;
			case '20200':
				return trans('appointments.General.ExternalIdInvalid');
				break;
			case '31620':
				return trans('appointments.General.symptomIvalid');
				break;
			case '31630':
				return trans('appointments.General.prestationInvalid');
				break;
			case '30':
				return trans('appointments.General.FormatParameterInvalid');
				break;
			default:
				return trans('appointments.General.UnhandledError');
		}
	}

	public static function getError($dataError){
			$error_message = self::errorCodeMessage($dataError['code']);
			Session::flash('error_message', $error_message);
			return redirect()->route($dataError['url']);
	}

	public static function getData(Request $request) {
		$appointments = Appointment::select(array('id','ScheduleID' ,'appointment_date', 'ShiftDesc', 'appointmentType', 'type', 'updated_at'));
		$columns = $request->input('columns');
		$filter =  $request->input('filter');
		if (!empty($columns) && sizeOf($columns)) {
			foreach($columns as $id => $column) {
				if ($column['data'] == 3) {
					switch ($filter) {
					case "active":
						$appointments->where('status','active');
						$columns[$id]['search']['value'] = '';
						$request->merge(array('columns' => $columns));
					break;
					case "archived":
						$appointments->where('status','archived');
						$columns[$id]['search']['value'] = '';
						$request->merge(array('columns' => $columns));
					break;
					}
				}
			}
		}

		$btn_action = '<div class="text-right">';
		
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route( \'Appointment.delete\', array($id,$type)) }}" class="btn btn-danger btn-mini">  <i class="icon-remove"></i></a></div>';

		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route( \'Appointment.edit\', array($id,$type)) }}" class="btn btn-primary btn-mini" target="_blank">  <i class="icon-edit"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route( \'Appointment.detail\', array($id,$type)) }}" class="btn btn-info btn-mini" target="_blank">  <i class="icon-eye-open"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Passer commande" href="{{ URL::route( \'Orders.create\', array($id)) }}" target="_blank" class="btn btn-success btn-mini">  <i class="icon-inbox"></i></a></div>';
		$btn_action .= '</div>';

		$datatable = Datatables::of($appointments)->edit_column('id', '<a href="{{ route( \'Appointment.detail\', array($id,\'prospect\' )) }}" target="_blank">{{$id}}</a>')
											->edit_column('ScheduleID', '{{$ScheduleID}}')
											->edit_column('appointment_date', '{{$appointment_date}}')
											->edit_column('ShiftDesc', '{{$ShiftDesc}}')
											->edit_column('appointmentType', '{{$appointmentType}}')
											->add_column('action', '')
											->edit_column('type', '{{$type}}')->addColumn('action', $btn_action)->removeColumn('updated_at')->removeColumn('id');

		return $datatable->make();
	}

	public static function uctrans($text)
	{
		return ucwords(trans($text));
	}

}