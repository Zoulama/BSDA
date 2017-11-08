<?php namespace Provisioning\Http\Controllers;

use Provisioning\Http\Requests;
use Provisioning\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Provisioning\Helpers\BsodHelper;
use Provisioning\Helpers\XmlOrderFileHelper;
use Provisioning\Helpers\FollowUpBsodOrder;
use Provisioning\Helpers\ManangerFtpHelper;
use Provisioning\Helpers\AbandonHelper;
use Provisioning\Helpers\AppointmentHelper;
use Provisioning\ComptaPrestation as Prestation;
use Provisioning\BsodClient;
use Provisioning\BsodOrder;
use Provisioning\Client;
use Provisioning\BsodService;
use Provisioning\EligibilityAddress;
use Provisioning\BsodOrderToBill;
use File;
use DB;
use Provisioning\Appointment;
use Session;
use Response;
use DateTime;
use Carbon\Carbon;
use Provisioning\Helpers\InfoClientBsodHelper;

class CommandesController extends BaseController {

	public function index(){
		$label_typeC = BsodHelper::$label_typeC;
		return view('bsod.commandes.index',compact('label_typeC'));
	}

	public function getDatatable(Request $request) {
		return FollowUpBsodOrder::getData($request);
	}

	public function createCommande($id)
	{
		$date = Carbon::now();
		try {
			$appointements = Appointment::findOrFail($id);
		} catch (ModelNotFoundException $er) {
			Session::flash('error_message', trans('appointments.id_not_exist'));
			return redirect()->route('Appointment.show');
		}
		$date_appointment = explode('T', $appointements->endDate);
		$datetime2 = new DateTime($date_appointment[0]);
		$datetime1 = new DateTime('now');
		$interval = $datetime1->diff($datetime2);
		$interval = intval($interval->format('%R%a'));
		if ($interval < 3 ) {
			Session::flash('error_message', trans('appointments.create_order_impossible'));
			return redirect()->route('Appointment.show');
		}

		$client_adresse = EligibilityAddress::where('accomodationId',$appointements->accomodationId)->first();
		$offers = json_decode($client_adresse->offres);
		$bsod_offers = BsodHelper::transformeOffer($offers);

		$dataview = BsodHelper::prepareOrdersValues($id,'createC');
		$dataview['idAccesPrise']	= $appointements->accomodationId;
		$dataview['codeClient']		= $appointements->externalSubscriberId;
		$dataview['codeINSEE']		= $client_adresse->code_insee;
		$dataview['scheduleid']		= $appointements->ScheduleID;
		$dataview['appointment_id']	= $appointements->id;
		$dataview['eligibility_addresse_id'] = $client_adresse->id;
		$dataview['bsod_client_id'] = $appointements->bsod_client_id;
		$dataview['first_name'] = $appointements->bsodClient->first_name;
		$dataview['last_name'] = $appointements->bsodClient->last_name;

		return view('bsod.commandes.create',compact('dataview','client_adresse','bsod_offers'));
	}

	public function addOption(Request $request){
		if ($request->ajax())
		{
			$data = [
				'codeAction'	=> $request->code_action,
				'option'		=> $request->option,
				'valeur'		=> $request->valeur,
			];
			Session::push($request->key,$data);
			return Response::json($data);
		}
	}

	public function addEquipement(Request $request){
		if ($request->ajax())
		{
			$data =[
				'numSequence'	=> $request->num_sequence,
				'codeAction'	=> $request->code_action,
				'typequip'		=> $request->typequip,
				'numeroSerie'	=> $request->num_serie,
				'codeEAN13'		=> $request->code_ean13,
			];

			Session::push('equip_'.$request->key,$data);
			return Response::json($data);
		}
	}

	public function send(Request $request, Client $client)
	{
		$service = [];
		$DataXml = [];
		$appointment_id = $request->appointment_id;

		if ($request->has('fibre')) {
			$fibre = [
				'codeAction' => $request->has('codeAction_fibre') ? $request->codeAction_fibre[0] : [],
				'typeRaccordement' => $request->typeRaccordement_fibre,
				'CommentaireRaccordement' => trim($request->CommentaireRaccordement),
			];
		}

		if ($request->has('data')) {
			$option = Session::get($request->codeClient);
			$equipements = Session::get('equip_'.$request->codeClient);
			$data = [
				'codeAction' => $request->has('codeAction_data') ? $request->codeAction_data[0] : '',
				'typeRaccordement' => $request->typeRaccordement_data,
				'option' => $option,
				'codeAction_EquRef' => $request->codeAction_EquRef,
				'numSequence_EquRef' => $request->numSequence_EquRef,
				'equipements' => $equipements,
			];
		}

		//$client_exist = BsodClient::where('email','=',$request->email)->first();
		if ($request->has('bsod_client_id')) {
			try {
				$client = BsodClient::find($request->bsod_client_id);
			} catch (ModelNotFoundException $er) {
				Session::flash('error_message', trans('appointments.id_not_exist'));
				return redirect()->route('Appointment.show');
			}
		}
		$date = Carbon::now();

		if (!$client->externalSubscriberIdIsNull()) {
			$codeClientFinal = $client->externalSubscriberId;
		} else {
			$codeClientFinal = substr(uniqid(rand(),true),0,15);
			$codeClientFinal = 'CDP'.strtoupper($codeClientFinal);
			$identifiantClient = '0';

			$client->email = trim($request->email);
			$client->telephone = trim($request->numContact);
			$client->gender = trim($request->civilite);
			$client->client_type = trim($request->typeClient);
			$client->externalSubscriberId = trim($request->codeClient);

			$client->save();
		}

		if (!$client->customerIdIsNull()) {
			$identifiantClient = $clients->customerId;
		}

		$DataXml['Commande']['dateCommande'] = $date->toAtomString();
		$DataXml['Commande']['typeCommande'] = $request->typeCommande;

		$DataXml['ClientDemandeur'] = [
				'codeClient' => "CODEPI-FIBRE",
		];

		$DataXml['ClientFacture'] = [
				'codeClient' => "CODEPI-FIBRE",
		];

		$DataXml['ClientFinal'] = [
				'codeClient'	=> $request->codeClient,
				'civilite'		=> $request->civilite,
				'nom'			=> $request->nom,
				'prenom'		=> $request->prenom,
				'numContact'	=> $request->numContact,
				'email'			=> $request->email,
				'typeClient'	=> $request->typeClient,
		];

		$DataXml['Acces'] = [
				'idAccesPrise'	=> $request->idAccesPrise,
				'adresse'		=> [
						'numeroDansVoie'	=> $request->numeroDansVoie,
						'libelleVoie'		=> $request->libelleVoie,
						'codeINSEE'			=> $request->codeINSEE,
						'codePostal'		=> $request->codePostal,
						'commune'			=> $request->commune,
				],
		];

		if ($request->typeCommande == 'C') {
			$DataXml['Acces']['adresse']['scheduleid'] = $request->scheduleid;
		}

		if (in_array($request->typeCommande, ['R','M','F'])) {
			$DataXml['Acces']['identifiantClient'] = $identifiantClient;
		}

		if(!in_array($request->typeCommande, ['R','F'])){
				if ($request->typeCommande == 'C') {
					if ($request->has('fibre')) {
						$DataXml['FIBRE'] = [
								'codeAction' => $request->has('codeAction_fibre') ? $request->codeAction_fibre[0] : [],
								'typeRaccordement' => $request->typeRaccordement_fibre,
							'CommentaireRaccordement' => trim($request->CommentaireRaccordement),
						];
					}

					$DataXml['Equipement']['IAD']['codeActionOptionWifi'] = $request->typeCommande;
					$DataXml['Equipement']['IAD']['typeLivraison'] = $request->typeLivraison;
					$equ_code_action = $request->typeCommande;
				}

			if ($request->has('data')) {
				if ($request->has('client_id') && $request->has('order_id')) {
					$DetailOreder = FollowUpBsodOrder::getDetailOreder($request->client_id,$request->order_id);
					$option = FollowUpBsodOrder::updateOptions($DetailOreder['DATA'][2],$option);
				}

				$DataXml['Data'] = [
							'codeAction'		=> $request->typeCommande,
						'FluxData' => [
							'idFlux'			=> '1',
							'codeAction'		=> $request->has('codeAction_data') ? $request->codeAction_data[0] : '',
							'typeRaccordement'	=> 'FIBRE',
							'option'			=>  $option,
							'EquipementRef'		=>  ['codeAction'=> $request->codeAction_EquRef, 'numSequence' => $request->numSequence_EquRef],
						],
				];
			}

			if ($request->typeCommande != 'C') {
				$numeroSerie = $request->numeroSerie;
			}

			$DataXml['Equipement']['IAD']['numSequence'] = $request->numSequence_EquRef;
			$DataXml['Equipement']['IAD']['codeEAN13']   = $request->codeEAN13;
			$DataXml['Equipement']['IAD']['numeroSerie'] = isset($numeroSerie) ? $numeroSerie : "";
			$DataXml['Equipement']['IAD']['codeAction']  = $request->codeAction_EquRef;
		}

		if ($request->has('fibre')) {
			$attributs_fibre = ['FIBRE' =>['typeRaccordement'=>'REACI','codeAction'=>'C']];
			$child_fibre = ['CommentaireRaccordement' => $request->CommentaireRaccordement];
			$service['FIBRE'] = [$attributs_fibre,$child_fibre];
		}

		if ($request->has('data')) {
			$attributs_data = ['DATA' => ['codeAction' => $data['codeAction']]];
			$child_data = [
			'FluxData' => ['idFlux' => '1','codeAction' =>  $data['codeAction'],'typeRaccordement' => 'FIBRE'],
			'Equipement' => $DataXml['Equipement']['IAD'],
			];

			$options_data=[];
			if (!empty($option)) {
				foreach ($option as $dataOption) {
					if ($dataOption['option'] == 'BASIC') {
						$valeur =  BsodHelper::$basicPrestaCode[$dataOption['valeur']];
					} else {
						$valeur =  BsodHelper::$optionPrestaCode[$dataOption['valeur']];
					}
					$options_data [] = ['codeAction' => $dataOption['codeAction'],'option' => $dataOption['option'],'valeur' => $valeur,'bsod_prestacode' => ''];
				}
			}

			$options_data = ['options' => $options_data];
			$service['DATA'] = [$attributs_data,$child_data,$options_data];
		}

		$services = BsodService::create([
			'services' => json_encode($service,200)
		]);

		$comfoldername = BsodHelper::getComFolder();
		$file_caractere = substr(uniqid(rand(),true), 6, 6).'_'.$date->format('Y-m-d');
		$filename = $comfoldername.$file_caractere;
		$filename = str_replace('-', '',$filename);

		$orders = BsodOrder::create([
			'dateCommande'	=> $date->toAtomString(), 
			'typeCommande'	=> $request->typeCommande,
			'comment'		=> $request->has('CommentaireRaccordement') ? $request->CommentaireRaccordement : '', 
			'bsod_client_id'	=> $request->bsod_client_id,
			'bsod_service_id'	=> $services->id,
			'appointment_id'	=> $appointment_id,
			'order_file_name'	=> $file_caractere.'.xml',
		]);

		if ($request->typeCommande == 'C') {
			$orders->numCommande = 'CDP'.$orders->id;
			$DataXml['Commande']['numCommande'] = 'CDP'.$orders->id;
			BsodOrderToBill::create(['bsod_order_id' => $orders->id,'bsod_service_id' => $services->id]);
		} else {
			$DataXml['Commande']['numCommande']  = $request->numCommande;
			$orders->numCommande = $request->numCommande;
			$bsodOrderToBill = BsodOrderToBill::where('bsod_order_id','=',$orders->id)->first();
			$bsodOrderToBill->bsod_service_id = $services->id;
			$bsodOrderToBill->save();
		}
		$orders->save();

/*		 DB::transaction(function () use ($request, $client) {
            $prestation = Prestation::centileFactory(
                'Bsod',
                date('Y-m-d'),
                $client->getId(),
                $client->getGroupId(),
                $serviceProviderContext = PBXTrunking::getDefaultResellerContext($client->getResellerId()),
                null,
                $request->input('defaultPstn'),
                $request->input('label'),
                Prestation::STATUS_COMPLETION
            )};*/

		$XmlOrderFileHelper = new XmlOrderFileHelper($DataXml);
		$xmlData = $XmlOrderFileHelper::main();
		$xml_file = (string)view('bsod.commandes.xml_file', compact('xmlData'))->render();

		Session::forget($request->codeClient);
		File::put('orders/'.$filename.'.xml',$xml_file);
		$file = ManangerFtpHelper::putFile('orders/'.$filename.'.xml',ManangerFtpHelper::getComPath().$filename.'.xml');
		Session::flash('success_message', trans('commande.success_create'));
		return redirect()->route('Orders.index');
	}

	public function editOreder($client_id,$order_id){

		$dataOrder = BsodOrder::find($order_id);
		$dataIn = 1;

		$client_bsod = $dataOrder->bsodClient;
		if (is_null($client_bsod->customerId) || $client_bsod->customerId == '') {
			Session::flash('error_message', trans('commande.cannot_edited'));
			return redirect()->route('Orders.index');
		}

		$client_adresse = EligibilityAddress::where('accomodationId',$client_bsod->accomodationId)->first();
		$offers = json_decode($client_adresse->offres);
		$bsod_offers = BsodHelper::transformeOffer($offers);
		$dataServices = $dataOrder->bsodService;
		$equipements = isset(json_decode($dataServices->services)->DATA[1]->Equipement) ? json_decode($dataServices->services)->DATA[1]->Equipement :null;
		$numeroSerie = isset($equipements->numeroSerie) ? $equipements->numeroSerie : '';
		if ($client_bsod->customerId != '0') {
			$customerInfosParams['CustomerId'] = $client_bsod->customerId;
			$customerInfosParams['ExternalId'] = $client_bsod->externalSubscriberId;
			$dataIn = InfoClientBsodHelper::customerInfos($customerInfosParams);
		}

		if (isset($dataIn->GetCustomerInfosResult->ResponseStatus->Code) && $dataIn->GetCustomerInfosResult->ResponseStatus->Code == 0) {
			$numeroSerie = $dataIn->GetCustomerInfosResult->Customer->IadInfos->EquipmentInfo->SerialNumber;
		}

		$dataEquip =[
			'codeEAN13' => isset($equipements->codeEAN13) ? $equipements->codeEAN13 : '',
			'numeroSerie' => $numeroSerie,
		];

		$dataview = BsodHelper::prepareOrdersValues($order_id,'editC');
		$dataview['idAccesPrise']	= $client_bsod->accomodationId;
		$dataview['codeClient']		= $client_bsod->externalSubscriberId;
		$dataview['codeINSEE']		=  $client_adresse->code_insee;
		$dataview['appointment_id'] = $dataOrder->appointment_id;
		$dataview['eligibility_addresse_id'] = $client_adresse->id;
		$dataview['client_id'] = $client_bsod->id;

		return view('bsod.commandes.create',compact('dataview','client_adresse','client_bsod','dataEquip','bsod_offers'));
	}

	public function getDetailOreder($client_id,$order_id){

		$dataOrder = BsodOrder::find($order_id);
		$dataIn = 1;
		$ftp_follow = FollowUpBsodOrder::arcomIsArrived($dataOrder->order_file_name);
		$tabColumn = [];
		$column = '';
		$tab_ftp_follow['arcomIsArrived'] = FollowUpBsodOrder::arcomIsArrived($dataOrder->order_file_name);
		$tab_ftp_follow['arFibreService'] = FollowUpBsodOrder::arFibreService($dataOrder);
		$tab_ftp_follow['crFibreService'] = FollowUpBsodOrder::crFibreService($dataOrder);
		$tab_ftp_follow['arCptl']         = FollowUpBsodOrder::arCptl($dataOrder);
		$tab_ftp_follow['crCptl']         = FollowUpBsodOrder::crCptl($dataOrder);
		$client = $dataOrder->bsodClient;
		$dataServices = $dataOrder->bsodService;
		$dataServices = json_decode($dataServices->services);
		$DATA = isset($dataServices->DATA) ? $dataServices->DATA : [];
		$FIBRE = isset($dataServices->FIBRE) ? $dataServices->FIBRE : [];
		$label_typeC = BsodHelper::$label_typeC;
		$typeRacc = BsodHelper::$typeRacc;
		$numeroSerie = '';
		$orders = [
			'pivot_id' => $dataOrder->id,
			'numCommande' => $dataOrder->numCommande,
			'dateCommande' => $dataOrder->dateCommande,
			'typeCommande' => $dataOrder->typeCommande,
		];
		if ($client->customerId != '0') {
			$data['CustomerId'] = $client->customerId;
			$data['ExternalId'] = $client->externalSubscriberId;
			$customerInfo = InfoClientBsodHelper::customerInfos($data);
		}

		if (isset($customerInfo)) {
			if ($customerInfo->GetCustomerInfosResult->ResponseStatus->Code == 0) {
				$numeroSerie = $customerInfo->GetCustomerInfosResult->Customer->IadInfos->EquipmentInfo->SerialNumber;
			}
		}

		$OrderStatus = FollowUpBsodOrder::arFibreService($dataOrder);
		$orderType = [];
		if (isset($OrderStatus[$client->externalSubscriberId][$dataOrder->numCommande])) {
			$Orders = $OrderStatus[$client->externalSubscriberId][$dataOrder->numCommande];
		}else {
			$Orders = [];
		}

		$finalOrder = FollowUpBsodOrder::getFinalOrders();
		$terminedOrder = isset($finalOrder[$dataOrder->numCommande]) ? $finalOrder[$dataOrder->numCommande] : '';
		if (isset($tab_ftp_follow['crFibreService'][$client->externalSubscriberId])) {
			$tabColumn = $tab_ftp_follow['crFibreService'][$client->externalSubscriberId][$dataOrder->numCommande];
		}
		if (!empty($tabColumn)) {
			if (isset($tabColumn['RES'])){
				$column = 'RES';
				if (isset($tabColumn['ACT'])) {
					$column = 'ACT';
				}

				if (isset($tabColumn['INST'])) {
					$column = 'INST';
				}
			} elseif(isset($tabColumn['MOD'])) {
				$column = 'MOD';
			}
		}

		if (isset($tab_ftp_follow['crFibreService'][$client->externalSubscriberId][$dataOrder->numCommande][$column])) {
			$tab_ftp_follow['crFibreService'] = $tab_ftp_follow['crFibreService'][$client->externalSubscriberId][$dataOrder->numCommande][$column];
		}

		$ftp_follow = $tab_ftp_follow;
		if (!empty($Orders)) {
			switch ($dataOrder->typeCommande) {
				case 'C':
					if (isset($Orders['RES']) && isset($Orders['ACT']) && isset($Orders['INST']) ) {
						$orderType['C'] = ['status' => $Orders['RES']['status'], 'idClient' => isset($Orders['RES']['idClient']) ? $Orders['RES']['idClient'] : '', 'msg' => $Orders['RES']['msg']];
					}
					break;
				case 'M':
						if (isset($Orders['MOD'])) {
							$orderType['M'] = [ 'status' => $Orders['MOD']['status'], 'idClient' => isset($Orders['MOD']['idClient']) ? $Orders['MOD']['idClient'] : '', 'msg' => $Orders['MOD']['msg']];
						}
					break;
				case 'R':
					$orderType = [];
					break;
			}
		}
		return view('bsod.commandes.view',compact('client','orders','DATA','FIBRE','label_typeC','typeRacc','OrderStatus','orderType','terminedOrder','numeroSerie','ftp_follow'));
	}

	public function abandonOrder($id){
		$client_bsod = BsodClient::find($id);

		if ($client_bsod->customerId === '') {
			Session::flash('error_message', trans('commande.error_notyet_customer'));
			return redirect()->route('Orders.index');
		}

		$data['noAbo'] = $client_bsod->customerId;
		$result = AbandonHelper::abandonCommande($data);

		if ($result->AbandonCommandeResult->code != 0) {
			$dataError = ['code' => $result->AbandonCommandeResult->code ,'url'=> 'Orders.show'];
			return AppointmentHelper::getError($dataError);
		}
		$message_success = $result->AbandonCommandeResult->message;

		Session::flash('success_message', $message_success);
		return redirect()->route('Orders.show');
	}

}