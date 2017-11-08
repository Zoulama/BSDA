<?php  namespace Provisioning\Helpers;

use Provisioning\BsodOrder;
use Provisioning\Helpers\BsodHelper;
use Provisioning\BsodService;
use Provisioning\BsodClient;
use Illuminate\Http\Request;
use Datatables;
use Parser;
use File;
use Provisioning\Helpers\ManangerFtpHelper;
class ClientBsodHelper {

	public static function getData(Request $request) {
		$clientBsod = BsodClient::select(
			array(
				'bsod_clients.id as id',
				'bsod_clients.externalSubscriberId as externalSubscriberId',
				'bsod_clients.first_name as first_name',
				'bsod_clients.last_name as last_name',
				'bsod_clients.updated_at as updated_at',
				'bsod_clients.clientID as clientID',
				'eligibility_address.id as addr_id'
			));

		$clientBsod->join('eligibility_address','eligibility_address.id','=','bsod_clients.eligibility_address_id');


		if ($request->has('last_name')) {
			$clientBsod->where('bsod_clients.last_name', 'like', '%'.$request->last_name.'%');
		}

		if ($request->has('externalSubscriberId')) {
			$clientBsod->where('bsod_clients.externalSubscriberId', 'like', '%'.$request->externalSubscriberId.'%');
		}

		$btn_action = '<div class="text-right">';

		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route( \'ClientBsod.edit\', array($id)) }}" class="btn btn-default btn-mini" target="_blank">  <i class="icon-edit"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Detail" href="{{ URL::route( \'ClientBsod.detail\', array($id)) }}" class="btn btn-info btn-mini" target="_blank">  <i class="icon-eye-open"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="ReplanificationRDV" href="{{ URL::route( \'Appointment.index\', array($id,$clientID,\'customer\')) }}" target="_blank" class="btn btn-success btn-mini">  <i class="icon-calendar"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="liste commande" href="{{ URL::route( \'ClientBsod.listOrder\', array($id)) }}" target="_blank" class="btn btn-primary btn-mini">  <i class="icon-inbox"></i></a></div>';
		$btn_action .= '</div>';

		$datatable = Datatables::of($clientBsod)->edit_column('externalSubscriberId', '<strong><span class=\'label label-success\'>{{$externalSubscriberId}}</span></strong>')
											->edit_column('first_name', '<strong>{{$first_name}}</strong>')
											->edit_column('last_name', '<strong>{{$last_name}}</strong>')
											->edit_column('updated_at','<abbr class="timeago" title="{{ $updated_at }}"></abbr>')
											->add_column('action', '')->addColumn('action', $btn_action)->removeColumn('id')->removeColumn('addr_id')->removeColumn('clientID');

		return $datatable->make();
	}

	public static function getDataListOrder($id){
		$clientListOrders = BsodOrder::select(
			array(
				'bsod_orders.id as id',
				'bsod_orders.numCommande as numCommande',
				'bsod_orders.dateCommande as dateCommande',
				'bsod_orders.typeCommande as typeCommande',
				'bsod_orders.updated_at as updated_at',
				'bsod_orders.bsod_client_id as bsod_client_id'
			));

		$clientListOrders->join('bsod_clients','bsod_clients.id','=','bsod_orders.bsod_client_id');
		$clientListOrders->where('bsod_clients.id',$id);

		$btn_action = '<div class="text-right">';

		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route( \'Orders.edit\', array($bsod_client_id,$id)) }}" class="btn btn-primary btn-mini" target="_blank">  <i class="icon-edit"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route( \'Orders.detail\', array($bsod_client_id,$id)) }}" class="btn btn-info btn-mini" target="_blank">  <i class="icon-eye-open"></i></a></div>';

		$btn_action .= '</div>';

		$datatable = Datatables::of($clientListOrders)->edit_column('numCommande', '<strong><span class=\'label label-success\'>{{$numCommande}}</span></strong>')
											->edit_column('dateCommande', '<strong>{{$dateCommande}}</strong>')
											->edit_column('typeCommande', '<strong>{{$typeCommande}}</strong> : <span class=\'label label-success\'>{!!BsodHelper::$label_typeC[$typeCommande]!!}</span>')
											->edit_column('updated_at','<abbr class="timeago" title="{{ $updated_at }}"></abbr>')
											->add_column('action', '')->addColumn('action', $btn_action)->removeColumn('id')->removeColumn('bsod_client_id');
		return $datatable->make();
	}

}