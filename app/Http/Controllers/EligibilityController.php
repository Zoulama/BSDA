<?php

namespace Provisioning\Http\Controllers;
ini_set('max_execution_time', 120);

use Provisioning\Http\Requests;
use Provisioning\Http\Controllers\Controller;
use Provisioning\Helpers\LibClient;
use Illuminate\Http\Request;
use Provisioning\Helpers\EligibilityHelper;
use Maatwebsite\Excel\Facades\Excel;
use Provisioning\ProspectAppointements;
use Provisioning\EligibilityAddress;
use Provisioning\Appointment;
use Provisioning\BsodOrderToBill;
use Provisioning\BsodOrder;
use Carbon\Carbon;
use Input;
use Response;
use Redirect;
use Session;

class EligibilityController extends BaseController
{
	public function index($client_id)
	{
		//$clientApi = LibClient::getClientById($client_id);
		$clientAdresse = explode(',', "38, RUE DE PARIS");
		if (!is_array($clientAdresse)) 
			return false;

		$eligibilityParam['street_number'] = $clientAdresse[0];
		$eligibilityParam['street'] = $clientAdresse[1];
		$eligibilityParam['clientVille'] = 'LES LILAS';//$clientApi['clientVille'];
		$clientVille = str_replace(" ", "-", $eligibilityParam['clientVille']);
		$eligibilityParam['zipcode'] = '93260'.' '.$clientVille; //$clientApi['clientCP'];
		$eligibilityParam['clientId'] = $client_id;


		return view('bsod.index',compact('eligibilityParam'));
	}

	public function autocomplete(Request $request){
		if ($request->ajax()){
			$zipcode = $request->term;
			$queries = EligibilityHelper::checkCodePostal(trim($zipcode));
			$results = [];

			if (isset($queries->EligibiliteCodePostalResult->Villes->VilleInsee)) {
					if (count($queries->EligibiliteCodePostalResult->Villes->VilleInsee)>1) {
						foreach ($queries->EligibiliteCodePostalResult->Villes->VilleInsee as $value) {
							$NomVille = str_replace(' ','-', $value->NomVille);
							$results []= ['value' => $zipcode.' '.$NomVille];
						}
					}else {
						$ville = $queries->EligibiliteCodePostalResult->Villes->VilleInsee->NomVille;
						$code_insee = $queries->EligibiliteCodePostalResult->Villes->VilleInsee->CodeInsee;
						$ville = str_replace(' ','-', $ville);
						$results = ['value' => $zipcode.' '.$ville];
					}
			}else {
				$dataResponse = json_decode($queries);
				$results = ['value' => $dataResponse->message];
			}

			return Response::json($results);
		}
	}

	public function autocompleteVoie(Request $request){
		if ($request->ajax()){
			$zipcode = $request->addr;
			$term = $request->term;
			$voie = explode(' ',$zipcode);
			$queries = EligibilityHelper::checkCodePostal($voie[0]);
			if (is_array( $queries->EligibiliteCodePostalResult->Villes->VilleInsee)) {
				foreach ($queries->EligibiliteCodePostalResult->Villes->VilleInsee as $VilleInsee) {
					if ($VilleInsee->NomVille == $voie[1]) {
						$code_insee = $VilleInsee->CodeInsee;
					}
				}
			}else {
				$code_insee = $queries->EligibiliteCodePostalResult->Villes->VilleInsee->CodeInsee;
			}

			$res = EligibilityHelper::checkVoie($code_insee);

			if (isset($res->EligibiliteCommuneResult->RuesEligibles->Rue->NomRue) && isset($res->EligibiliteCommuneResult->RuesEligibles->Rue->CodeRIVOLI)) {
					$result []= ['value' => $res->EligibiliteCommuneResult->RuesEligibles->Rue->NomRue,'rivoli' => $res->EligibiliteCommuneResult->RuesEligibles->Rue->CodeRIVOLI];
			}else{
				foreach ($res->EligibiliteCommuneResult->RuesEligibles->Rue as $value){
					$result []= ['value' => $value->NomRue,'rivoli' => $value->CodeRIVOLI];
				}
			}

			return Response::json($result);
		}
	}

	public function eligibility(Request $request)
	{
		$offres = [];

		$zipcode = explode(' ',$request->zipcode);
		$pattern ='/^\d+\w*\s*(?:[\-\/]?\s*)?\d*\s*\d+\/?\s*\d*\s*/';
		preg_match($pattern,$request->street, $matches, PREG_OFFSET_CAPTURE);
		preg_match("'(.*) (.*) ([0-9]{5})'s" ,$request->street,$infos);
		preg_match("/([^\d]+)\s?(.+)/",$request->street,$treet);
			if (!empty($matches)) {
				$nb = trim($matches[0][0]);
			}else{
				$nb ='';
			}

			if (!empty($treet)) {
				$streets =trim($treet[0]);
			}else{
				$streets = $request->street;
			}
		$nb = $request->street_number;
		$params = [
			'street_number' => $nb,
			'street_number_complement' => $request->street,
			'street' => $streets,
			'zipcode' => $zipcode[0],
		];

		if ($request->has('codeRivoli'))
		{
			$params['codeRivoli'] = $request->input('codeRivoli');
		}

		$checkEligibility = json_decode(EligibilityHelper::checkEligibility($params));

		if ($checkEligibility->eligibility)
		{
			$eligibility_detail = (array) $checkEligibility->result;

			foreach ($eligibility_detail as $key => $detail)
			{
				if (is_object($detail))
				{
					if ($key == 'DetailEligibiliteNET_VOIP') {
						$eligibility_detail[$key] = EligibilityHelper::decodeToBinary(strrev(decbin($checkEligibility->result->CodeEligibiliteNET_VOIP)));
						$offres = $eligibility_detail[$key];
					}else {
						$eligibility_detail[$key] = (array) $detail;
					}
				}
			}

			$CodeEligibiliteNET_VOIP = decbin($checkEligibility->result->CodeEligibiliteNET_VOIP);
			$EligibilityAddress = EligibilityAddress::where('accomodationId',$checkEligibility->id_prise)->first();

			if (is_null($EligibilityAddress)) {
				$addr = $params['street_number'].' '.$params['street_number_complement'].', '.$params['zipcode'].' '.$zipcode[1];
				$EligibilityAddress = EligibilityAddress::create([
								'accomodationId'			=> $checkEligibility->id_prise,
								'street_number'				=> $params['street_number'],
								'street_number_complement'	=> $addr,
								'street'					=> $params['street'],
								'zipcode'					=> $params['zipcode'],
								'city'						=> $zipcode[1],
								'code_insee'				=> $checkEligibility->code_insee,
								'offres'					=> json_encode($offres),
				]);
			}

			$params['city']= $zipcode[1];
			$params['code_insee'] = $checkEligibility->code_insee;
			//session()->put($checkEligibility->id_prise,$params);
			$params['result'] = $eligibility_detail;
			$params['id_prise'] = $checkEligibility->id_prise;
			$params['CodeEligibiliteNET_VOIP'] = $CodeEligibiliteNET_VOIP;
			$params['eligibilityAddress_id'] = $EligibilityAddress->id;
			$params['clientId'] = $request->clientId;
			return view('bsod.eligibility.result',compact('params'));
		} else {
			$message = Session::flash('message', $checkEligibility->message);
			return Redirect::route('bsod.index',[$request->clientId]);
		}
	}

	public function eligibilityAdress(){
		return view('bsod.eligibility_adress');
	}
	public function show($id){
		$eligibilityAddress = EligibilityAddress::find($id);
		$offres = json_decode($eligibilityAddress->offres);
		return view('bsod.eligibility_adress_detail',compact('eligibilityAddress','offres'));
	}


	public function getDatatable(Request $request){
		return EligibilityHelper::getData($request);
	}
}