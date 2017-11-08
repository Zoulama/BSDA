<?php  namespace Provisioning\Helpers;

use Provisioning\BsodOrder;
use Provisioning\Helpers\BsodHelper;
use Illuminate\Http\Request;
use Provisioning\BsodClient;
use Datatables;
use DateTime;
use Parser;
use File;
use Provisioning\Helpers\ManangerFtpHelper;

class FollowUpBsodOrder {

	public static function getData(Request $request) {

		$orders = BsodOrder::select(
			array(
				'bsod_orders.id as id',
				'bsod_clients.externalSubscriberId as externalSubscriberId',
				'bsod_orders.dateCommande as dateCommande',
				'bsod_orders.typeCommande as typeCommande',
				'bsod_orders.updated_at as updated_at',
				'bsod_orders.bsod_client_id as bsod_client_id',
				'bsod_orders.appointment_id as appointment_id',
			));

		$orders->join('bsod_clients','bsod_clients.id','=','bsod_orders.bsod_client_id');

		if ($request->has('externalSubscriberId')) {
			$orders->where('bsod_clients.externalSubscriberId', 'like', '%'.$request->externalSubscriberId.'%');
		}

		$columns = $request->input('columns');
		$filter = $request->input('filter');
		if (!empty($columns) && sizeOf($columns)) {
			foreach($columns as $id => $column) {
				if ($column['data'] == 3) {
					switch ($filter) {
					case "in_progress":
						$orders->where('status','in_progress');
						$columns[$id]['search']['value'] = '';
						$request->merge(array('columns' => $columns));
					break;
					case "completed":
						$orders->where('status','completed');
						$columns[$id]['search']['value'] = '';
						$request->merge(array('columns' => $columns));
					break;
					}
				}
			}
		}

		$btn_action = '<div class="text-right">';

		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route(\'Orders.abandon\', array($bsod_client_id)) }}" class="btn btn-danger btn-mini">  <i class="icon-remove"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route(\'Orders.edit\', array($bsod_client_id,$id)) }}" class="btn btn-primary btn-mini" target="_blank">  <i class="icon-edit"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Modifier" href="{{ URL::route(\'Orders.detail\', array($bsod_client_id,$id)) }}" class="btn btn-info btn-mini" target="_blank">  <i class="icon-eye-open"></i></a></div>';
		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="ReplanificationRDV" href="{{ URL::route(\'Appointment.edit\', array($appointment_id,\'customer\')) }}" target="_blank" class="btn btn-success btn-mini">  <i class="icon-calendar"></i></a></div>';
		$btn_action .= '</div>';

		$datatable = Datatables::of($orders)->edit_column('externalSubscriberId', '<strong><span class=\'label label-success\'>{{$externalSubscriberId}}</span></strong>')
											->edit_column('dateCommande', '{{$dateCommande}}')
											->edit_column('typeCommande', '{{$typeCommande}} : <span class=\'label label-success\'>{!!BsodHelper::$label_typeC[$typeCommande]!!}</span>')
											->edit_column('updated_at','<abbr class="timeago" title="{{ $updated_at }}"></abbr>')
											->add_column('action', '')->addColumn('action', $btn_action)->removeColumn('id')->removeColumn('appointment_id')->removeColumn('bsod_client_id');

		return $datatable->make();
	}

	public static function services($client_id,$order_id){
		$client = BsodClient::find($client_id);
		$dataOrder = $datas->bsodOrders()->whereBsodClientId($client_id)->first();
		$dataServices = $dataOrder->bsodService;
		$equipements = isset(json_decode($dataServices->services)->DATA[1]->Equipement) ? json_decode($dataServices->services)->DATA[1]->Equipement :null;
		$dataEquip = [
			'codeEAN13' => isset($equipements->codeEAN13) ? $equipements->codeEAN13 : '',
			'numeroSerie' => isset($equipements->numeroSerie) ? $equipements->numeroSerie : '',
		];

		return [
				'dataOrder'	=> $dataOrder,
				'pivot_id'	=> $dataOrder->id,
				'dataEquip'	=> $dataEquip,
		];
	}

	public static function getDetailOreder($client_id,$order_id){
		$client  = BsodClient::find($client_id);
		$dataOrder = $datas->bsodOrders()->whereBsodClientId($client_id)->first();
		$dataServices = $dataOrder->bsodService;
		$dataServices = json_decode($dataOrder->bsodService);
		$DATA = isset($dataServices->DATA) ? $dataServices->DATA : [];
		$FIBRE = isset($dataServices->FIBRE) ? $dataServices->FIBRE : [];
		$label_typeC = BsodHelper::$label_typeC;
		$typeRacc = BsodHelper::$typeRacc;
		$orderInfo = $dataOrder->order()->first();
		$orders = [
				'pivot_id' => $dataOrder->id,
				'numCommande' => $dataOrder->numCommande,
				'dateCommande' => $dataOrder->dateCommande,
				'typeCommande' => $dataOrder->typeCommande,
		];

		return ['client' => $client,'orders' => $orders ,'DATA' => $DATA ,'FIBRE' => $FIBRE ,'label_typeC' => $label_typeC,'typeRacc' => $typeRacc];
	}

	public static function isAvailabe($opt_avail,$val){

		foreach ($opt_avail->options as $value) {
			if ($value->valeur == $val['valeur'] && $value->codeAction != $val['codeAction']){
				$newOptions [] = [
									'codeAction'	=> $val['codeAction'],
									'option'		=> $value->option,
									'valeur'		=> $value->valeur,
								];
			}elseif($value->valeur != $val['valeur']){
				$newOptions [] = [
									'codeAction'	=> "NA",
									'option'		=> $value->option,
									'valeur'		=> $value->valeur,
				];
			}
		}
		return $newOptions;
	}

	public static function  arcomIsArrived($file){
			$res = ManangerFtpHelper::getDirectory(ManangerFtpHelper::getArComPath());
			$arcom_tab = [];
			$commentaire = '';
			$filename = ManangerFtpHelper::getArComPath().BsodHelper::getArcomFolder().$file;
			$arcom_tab ['arcom'] = 'ARCOM';
			$arcom_tab ['recpetion'] = '';
			$arcom_tab ['status'] = '';
			$arcom_tab ['msg'] = '';
			$arcom_tab ['date'] = '';
			$key = array_search($filename, $res);
			if (!is_bool($key)) {
				$arcom = $res[$key];
				$f = ManangerFtpHelper::getFile($arcom,'test.xml');
				$parser = new Parser();
				$xml_content = File::get('test.xml');
				$data_arcom = $parser::xml($xml_content);
				$date_file = explode('_', $data_arcom['idMessage']);
				$date_file = explode('.', $date_file[3])[0];

				if (isset($data_arcom['erreur'])) {
					$commentaire = $data_arcom['erreur']['commentaire'];
				}

				$arcom_tab ['arcom'] = 'ARCOM';
				$arcom_tab ['recpetion'] = 'Reçu';
				$arcom_tab ['status'] = $data_arcom['statut'];
				$arcom_tab ['msg'] = $commentaire;
				$arcom_tab ['date'] = $date_file;
			}

			return $arcom_tab;
	}

	public static function parseXmlFolders(BsodOrder $order,$array,$name_service){
		$results = [];
		$match_values_1 = [];
		$match_values_2 = [];
		$models = explode("T",$order->dateCommande);
		$date = new DateTime($models[0]);
		$model_1 = $date->format('Ymd');
		$model_1 = $date->format('Ym');
		$date->modify('+1 month');
		$model_2 = $date->format('Ym');
		$match_values_1 = array_filter($array, function($val,$key) use (&$model_1) { return stristr($val, $model_1);}, ARRAY_FILTER_USE_BOTH);
		$match_values_2 = array_filter($array, function($val,$key) use (&$model_2) { return stristr($val, $model_2);}, ARRAY_FILTER_USE_BOTH);

		$match_values = array_merge($match_values_1,$match_values_2);

		foreach ($match_values as $file) {
			$folderType = explode('/', $file);
			$entete = 'suiv:enTete';
			if (isset($folderType[1])) {
				if ($folderType[1] == 'ARCPTL' || $folderType[1] == 'CRCPTL'){
					$entete = 'ct:enTete';
				}
			}

			$f = ManangerFtpHelper::getFile($file,'test.xml');
			$parser = new Parser();
			$xml_content = File::get('test.xml');
			$data_orders = $parser::xml($xml_content);
			$typeMessage = $data_orders[$entete]['@typeMessage'];
			$tab_orders = $data_orders['suiv:commande'];

			if (isset($tab_orders['suiv:idCommande']) && $tab_orders['suiv:idCommande'] == $order->numCommande){
				$results[$tab_orders['suiv:idCommande']] = [
						'typeMessage'	=> $typeMessage,
						'code'			=> $tab_orders['suiv:etape']['@code'],
						'typeAction'	=> $tab_orders['suiv:etape']['@typeAction'],
						'status'		=> $tab_orders['suiv:etape']['@statut'],
						'date'			=> str_replace('-', '', explode('T',$tab_orders['suiv:etape']['@date'])[0]),
						'msg'			=> $tab_orders['suiv:erreur']['@messageErreur'],
					];
			}else{
				$key = array_search($order->numCommande, array_column($tab_orders, 'suiv:idCommande'));
				if (!is_bool($key)) {
					$results[$tab_orders[$key]['suiv:idCommande']] = [
						'typeMessage'	=> $typeMessage,
						'code'			=> $tab_orders[$key]['suiv:etape']['@code'],
						'typeAction'	=> $tab_orders[$key]['suiv:etape']['@typeAction'],
						'status'		=> $tab_orders[$key]['suiv:etape']['@statut'],
						'date'			=> str_replace('-', '', explode('T',$tab_orders[$key]['suiv:etape']['@date'])[0]),
						'msg'			=> $tab_orders[$key]['suiv:erreur']['@messageErreur'],
					];
				}
			}
		}

		if (empty($results)) {
			$results = ['typeMessage' => $name_service,'code' => '','typeAction'  => '', 'status'  => '', 'date'  => '', 'msg'  => ''];
		}

		return $results;
	}

	public static function  arFibreService(BsodOrder $order){
		$arFibreService = [];
		$res = ManangerFtpHelper::getDirectory(ManangerFtpHelper::getArFibreService());
		$name_service = explode('/', ManangerFtpHelper::getArFibreService())[1];
		$arFibreService = self::parseXmlFolders($order,$res,$name_service);

		return $arFibreService;
	}

	public static function  arCptl(BsodOrder $order){
		$res = ManangerFtpHelper::getDirectory(ManangerFtpHelper::getArCptl());
		$name_service = explode('/', ManangerFtpHelper::getArCptl())[1];
		$arCptl = self::parseXmlFolders($order,$res,$name_service);

		return $arCptl;
	}

	public static function  crCptl(BsodOrder $order) {
		$res = ManangerFtpHelper::getDirectory(ManangerFtpHelper::geCrCptl());
		$name_service = explode('/', ManangerFtpHelper::geCrCptl())[1];
		$crCptl = self::parseXmlFolders($order,$res,$name_service);

		return $crCptl;
	}

	public static function updateOptions($opt_avail,$opt){
			foreach ($opt as $value) {
				$newOptions = self::isAvailabe($opt_avail,$value);
				array_push($newOptions,$value);
			}

			return $newOptions;
	}

	public static function crFibreService(BsodOrder $order){
		$res = ManangerFtpHelper::getDirectory(ManangerFtpHelper::geCrFibreService());
		$orderStatus = [];
		$orderDetails = [];
		$detailOrders = [];

		$models = explode("T",$order->dateCommande);
		$date = new DateTime($models[0]);
		$model_1 = $date->format('Ymd');
		$date->modify('+1 day');
		$model_2 = $date->format('Ymd');

		$model = [$model_1,$model_2];

		$match_values_1 = array_filter($res, function($val,$key) use (&$model_1) { return stristr($val, $model_1);}, ARRAY_FILTER_USE_BOTH);
		$match_values_2 = array_filter($res, function($val,$key) use (&$model_2) { return stristr($val, $model_2);}, ARRAY_FILTER_USE_BOTH);

		$match_values = array_merge($match_values_1,$match_values_2);

		foreach ($match_values as $file) {
			$f = ManangerFtpHelper::getFile($file,'test.xml');
			$parser = new Parser();
			$xml_content = File::get('test.xml');
			$data_orders = $parser::xml($xml_content)['suiv:commande'];

			if(isset($data_orders['suiv:idClientExterne']) && $data_orders['suiv:idCommande'] == $order->numCommande) {
				$idClient = isset($data_orders['suiv:infosComplementaires']['suiv:abonneFibre']['@id']) ? $data_orders['suiv:infosComplementaires']['suiv:abonneFibre']['@id'] : '';
				$identifiantAS = isset($data_orders['suiv:infosComplementaires']['suiv:identifiantAS']) ? $data_orders['suiv:infosComplementaires']['suiv:identifiantAS'] : '';
				$orderStatus[$data_orders['suiv:idClientExterne']] = ['status' => $data_orders['suiv:etape']['@statut'], 'idClient' => $idClient ,'msg' => $data_orders['suiv:erreur']['@messageErreur']];
				$orderDetails []= ['idClientExterne' => $data_orders['suiv:idClientExterne'],'typeAction' => $data_orders['suiv:etape']['@typeAction'],'status' => $data_orders['suiv:etape']['@statut'], 'idClient' => $idClient ,'msg' => $data_orders['suiv:erreur']['@messageErreur']];
				$detailOrders[$data_orders['suiv:idClientExterne']][$data_orders['suiv:idCommande']][$data_orders['suiv:etape']['@typeAction']] = [
					'idClientExterne' => $data_orders['suiv:idClientExterne'],
					'typeMessage' => $data_orders['suiv:etape']['@code'],
					'typeAction' => $data_orders['suiv:etape']['@typeAction'],
					'status' => $data_orders['suiv:etape']['@statut'],
					'idClient' => $idClient,
					'identifiantAS' => $identifiantAS,
					'msg' => $data_orders['suiv:erreur']['@messageErreur'],
					'date' => str_replace('-', '', explode('T',$data_orders['suiv:etape']['@date'])[0])
				];
			} else{
				$key = array_search($order->numCommande, array_column($data_orders, 'suiv:idCommande'));
				if (!is_bool($key)) {
					$idClient = isset($data_orders[$key]['suiv:infosComplementaires']['suiv:abonneFibre']['@id']) ? $data_orders[$key]['suiv:infosComplementaires']['suiv:abonneFibre']['@id'] : '';
					$identifiantAS = isset($data_orders[$key]['suiv:infosComplementaires']['suiv:identifiantAS']) ? $data_orders[$key]['suiv:infosComplementaires']['suiv:identifiantAS'] : '';
					$orderStatus[$data_orders[$key]['suiv:idClientExterne']] =['status' => $data_orders[$key]['suiv:etape']['@statut'], 'idClient' => $idClient , 'msg' => $data_orders[$key]['suiv:erreur']['@messageErreur']];
					$orderDetails []= ['idClientExterne' => $data_orders[$key]['suiv:idClientExterne'],'typeAction' => $data_orders[$key]['suiv:etape']['@typeAction'],'status' => $data_orders[$key]['suiv:etape']['@statut'], 'idClient' => $idClient , 'msg' => $data_orders[$key]['suiv:erreur']['@messageErreur']];

					$detailOrders[$data_orders[$key]['suiv:idClientExterne']][$data_orders[$key]['suiv:idCommande']][$data_orders[$key]['suiv:etape']['@typeAction']] = [
						'idClientExterne' => $data_orders[$key]['suiv:idClientExterne'],
						'typeMessage' => $data_orders[$key]['suiv:etape']['@code'],
						'typeAction' => $data_orders[$key]['suiv:etape']['@typeAction'],
						'status' => $data_orders[$key]['suiv:etape']['@statut'],
						'idClient' => $idClient ,
						'identifiantAS' => $identifiantAS,
						'msg' => $data_orders[$key]['suiv:erreur']['@messageErreur'],
						'date' => str_replace('-', '', explode('T',$data_orders[$key]['suiv:etape']['@date'])[0])
					 ];
				}
			}

			File::delete('test.xml');
		}

		if (empty($detailOrders)) {
			$detailOrders = ['idClientExterne' => '','typeMessage' => 'CRFIBRESERVICE','typeAction'  => '', 'status'  => '', 'idClient'  => '', 'identifiantAS'  => '','msg'  => '', 'date'  => ''];
		}

		return $detailOrders;
	}

	public static function getFinalOrders(){
		$res = ManangerFtpHelper::getDirectory(ManangerFtpHelper::getCrcptl());
		$tabcrcptl = [];
		foreach ($res as $file) {
			$f = ManangerFtpHelper::getFile($file,'test.xml');
			$parser = new Parser();
			$xml_content = File::get('test.xml');
			$data_crcptl = $parser::xml($xml_content)['suiv:commande'];
			$tab[] = $data_crcptl;

			if (isset($data_crcptl['suiv:idCommande'])) {
				if (isset($data_crcptl['suiv:infosComplementaires'])) {
					$tabcrcptl[$data_crcptl['suiv:idCommande']] = ['idService' => $data_crcptl['suiv:infosComplementaires']['suiv:idService'], 'typeAction' => $data_crcptl['suiv:etape']['@typeAction']];
				}
			}else{
				foreach ($data_crcptl as  $value) {
					if (isset( $value['suiv:infosComplementaires'])) {
						$tabcrcptl[$value['suiv:idCommande']] = ['idService' => $value['suiv:infosComplementaires']['suiv:idService'], 'typeAction' => $value['suiv:etape']['@typeAction']];
					}
				}
			}
		}
		return $tabcrcptl;
	}

	public static function isCompleted($value){
		$num = $value->numCommande;
		$idExt = $value->externalSubscriberId;
		$arcomIsArrived = FollowUpBsodOrder::arcomIsArrived($value->order_file_name);
		$arFibreService = FollowUpBsodOrder::arFibreService($value);
		$crFibreService = FollowUpBsodOrder::crFibreService($value);
		$arCptl = FollowUpBsodOrder::arCptl($value);
		$crCptl = FollowUpBsodOrder::crCptl($value);

		if ($arcomIsArrived['status'] === 'OK') {
			return isset($crFibreService[$idExt][$num]) && isset($arFibreService[$num]) && isset($arCptl[$num]) && isset($crCptl[$num]);
		}

		return false;
	}
}