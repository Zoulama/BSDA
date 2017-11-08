<?php

namespace Provisioning\Helpers;

use SoapClient;
use Cache;
use Provisioning\EligibilityAddress;
use Datatables;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;

class EligibilityHelper {

	public static function EligibilityClient()
	{
		$options = [
			'trace'        => 1,
			'exceptions'   => false,
			'soap_version' => SOAP_1_1,
		];

		$wsdl = "file://" . base_path() . "/resources/soap/eligibility.wsdl";

		return new SoapClient($wsdl, $options);
	}

	public static function formatStreet($street)
	{
		$street  = trim(strstr($street, " "));
		$pattern = "/^de la | de la |^des | des |^de | de |du | du /i";
		$street  = preg_replace($pattern, " ", $street);
		return trim($street);
	}

	public static function getMessage($label, $params = [])
	{
		switch($label)
		{
			case 'soap_fault':
				return Lang::get('bsod.eligibility.message.soap_fault', $params);
				break;
			case 'zipcode_no_result':
				return Lang::get('bsod.eligibility.message.zipcode_no_result');
				break;
			case 'unknown_street':
				return Lang::get('bsod.eligibility.message.unknown_street');
				break;
			case 'ineligible_street':
				return Lang::get('bsod.eligibility.message.ineligible_street');
				break;
			case 'specify_street':
				return Lang::get('bsod.eligibility.message.specify_street');
				break;
			case 'specify_street_number':
				return Lang::get('bsod.eligibility.message.specify_street_number');
				break;
			default:
				return Lang::get('bsod.eligibility.message.unknown');
				break;
		}
	}

	public static function getErrorMessage($code)
	{
		switch($code)
		{
			case '2':
				return Lang::get('bsod.code.unavailable_resource');
				break;
			case '3':
			case '4':
			case '5':
				return Lang::get('bsod.code.message.internal_error');
				break;
			case '6':
			case '106':
			case '206':
			case '306':
			case '406':
			case '506':
			case '606':
			case '706':
			case '806':
			case '906':
				return Lang::get('bsod.code.unknown_credential');
				break;
			case '101':
			case '201':
				return Lang::get('bsod.code.fix_zipcode');
				break;
			case '301':
			case '401':
			case '501':
			case '601':
			case '701':
			case '801':
			case '901':
				return Lang::get('bsod.code.check_params');
				break;
			default:
				return Lang::get('bsod.code.unknown');
				break;
		}
	}

	public static function getTypeLogement($code)
	{
		return Lang::get('bsod.TypeLogement.' . $code);
	}

	public static function getPositionnementPB($code)
	{
		return Lang::get('bsod.PositionnementPB.' . $code);
	}

	public static function getRaccordabilite($code)
	{
		return Lang::get('bsod.Raccordabilite.' . $code);
	}

	public static function getDetailEligibilite($code)
	{
		$data = new \stdClass();
		foreach ($code as $key => $value)
		{
			$data->$key = $value == "O" ? "Oui" : "Non";
		}
		return $data;
	}

	public static function getCompagnie($code)
	{
		switch ($code)
		{
			case '02':
				return 'NUMERICABLE';
				break;
			case '03':
				return 'NOOS';
				break;
			default:
				return Lang::get('bsod.unknown_company');
				break;
		}
	}

	public static function checkReturnedCode($code)
	{
		if ($code != 0)
		{
			return json_encode([
				'eligibility' => false,
				'message'     => self::getErrorMessage($code),
				'result'      => [],
				]);
		}

		return true;
	}

	public static function translateCode($property, $code)
	{
		switch ($property)
		{
			case 'TypeLogement':
				return self::getTypeLogement($code);
				break;
			case 'PositionnementPB':
				return self::getPositionnementPB($code);
				break;
			case 'Raccordabilite':
				return self::getRaccordabilite($code);
				break;
			case 'EligibiliteTV':
			case 'EligibiliteNET':
			case 'EligibiliteVOIP':
			case 'HORSRESEAU':
				return $code == "O" ? "Oui" : "Non";
				break;
			case 'DetailEligibiliteTV':
			case 'DetailEligibiliteNET_VOIP':
				return self::getDetailEligibilite($code);
				break;
			case 'COMPAGNIE':
				return self::getCompagnie($code);
				break;
			default:
				return $code;
				break;
		}
	}

	public static function checkDtataClient(){
		$client = self::EligibilityClient();
		$ws_id  = "GUBP5982";
		$data = new \stdClass();
		$data->Identifiant = $ws_id;
		return [
				'client'	=> $client,
				'data'		=> $data,
		];
	}

	public static function checkCodePostal($code_postal){
		$res = self::checkDtataClient();
		if (is_soap_fault($res))
		{
			return json_encode([
				'eligibility' => false,
				'code'        => 'soap_fault',
				'message'     => self::getMessage('soap_fault'),
				'result'      => [],
				]);
		}

		$res['data']->CodePostal = $code_postal;
		$result            = $res['client']->EligibiliteCodePostal($res['data']);

		if (!isset($result->EligibiliteCodePostalResult->Villes->VilleInsee))
		{
			return json_encode([
				'eligibility' => false,
				'code'        => 'zipcode_no_result',
				'message'     => self::getMessage('zipcode_no_result'),
				'result'      => [],
				]);
		}

		return  $result;
	}

	public static function checkVoie($codeInsee){
		$res = self::checkDtataClient();
		$res['data']->CodeInsee=$codeInsee;
		try
			{
				$result = $res['client']->EligibiliteCommune($res['data']);
			}
			catch(Exception $e)
			{
				return json_encode([
					'eligibility' => false,
					'code'        => 'soap_fault',
					'message'     => self::getMessage('soap_fault', ['method' => 'EligibiliteCommune', 'message' => $e->getMessage()]),
					'result'      => [],
				]);
			}

		return $result;
	}

	public static function checkRue($tabStreet,$rue){

		if (!is_array($tabStreet) && isset($tabStreet->NomRue) && $tabStreet->NomRue == strtoupper($rue)) {
			return $tabStreet;
		}elseif (is_array($tabStreet)) {
				foreach ($tabStreet as $value) {
					if ($value->NomRue == strtoupper($rue)) {
						return $value;
					}
				}
		}

		return false;
	}

	public static function checkNumberStreet($tab,$number){
		if (!is_array($tab) && isset($tab->NumeroRue) && $tab->NumeroRue == $number) {
			return $tab;
		}elseif (is_array($tab)) {
				foreach ($tab as $value) {
				if ($value->NumeroRue == $number) {
					return $value;
				}
			}
		}
		$code    = 'unknown_street';
		$message = self::getMessage($code);
		$message = 'Numero de rue inconnu';
		return  json_encode([
			'eligibility' => false,
			'code'        => $code,
			'message'     => $message,
			'result'      => $number,
			]);
	}

	public static function EligibiliteRue($codeInsee,$codeRIVOLI){
		$res=self::checkDtataClient();
		$res['data']->CodeInsee   = $codeInsee;
		$res['data']->CodeRIVOLI  = $codeRIVOLI;
		$result            = $res['client']->EligibiliteRue($res['data']);

		if (!isset($result->EligibiliteRueResult->NumerosRue->AdresseRue))
		{
			return json_encode([
				'eligibility' => false,
				'code'        => 'ineligible_street',
				'message'     => self::getMessage('ineligible_street'),
				'result'      => [],
				]);
		}

		return $result;
	}

	public static function checkEligibility($params)
	{
		$ws_id                    = "GUBP5982";
		$street_number            = $params['street_number'];
		$street_number_complement = $params['street_number_complement'];
		$street                   = self::formatStreet($params['street']);
		$zipcode                  = $params['zipcode'];
		$zipcode = explode(' ', $zipcode);
		$queries = self::checkCodePostal($zipcode[0]);
		if (isset($queries->EligibiliteCodePostalResult->Villes->VilleInsee)) {
				if (count($queries->EligibiliteCodePostalResult->Villes->VilleInsee)>1) {
					foreach ($queries->EligibiliteCodePostalResult->Villes->VilleInsee as $valueInsee) {
						$codeInsee [] = $valueInsee->CodeInsee;
					}
				}else {
					$codeInsee[] = $queries->EligibiliteCodePostalResult->Villes->VilleInsee->CodeInsee;
				}
		}else {
				$codeInsee[] = json_encode([
							'eligibility' => false,
							'code'        => 'zipcode_no_result',
							'message'     => self::getMessage('zipcode_no_result'),
							'result'      => [],
							]);
		}

		foreach ($codeInsee as $insee_code) {
			if (!is_array($insee_code)) {
				$results[] = self::checkVoie($insee_code);
			}else {
				$results[] = $insee_code;
			}
		}

		if (isset($results[0]->EligibiliteCommuneResult->RuesEligibles->Rue)) {
			$result_checkRue[] = self::checkRue($results[0]->EligibiliteCommuneResult->RuesEligibles->Rue,$params['street']);
		}else {
			$result_checkRue[] = $results[0];
		}

		if (!is_array($result_checkRue[0]) && $result_checkRue[0]!=false && isset($result_checkRue[0]->CodeRIVOLI)) {
			$result_eligibiliteRue[] = self::EligibiliteRue($codeInsee[0],$result_checkRue[0]->CodeRIVOLI);
		}else{
			$result_eligibiliteRue[] = $result_checkRue[0];
		}

		if (!is_array($result_eligibiliteRue[0]) && $result_checkRue[0]!=false && isset($result_eligibiliteRue[0]->EligibiliteRueResult)) {
			$streets_nb = isset($result_eligibiliteRue[0]->EligibiliteRueResult->NumerosRue->AdresseRue) ? $result_eligibiliteRue[0]->EligibiliteRueResult->NumerosRue->AdresseRue : [];
			$resNumberStreet = self::checkNumberStreet($streets_nb,$params['street_number']);
		    $codeInsee  = $result_eligibiliteRue[0]->EligibiliteRueResult->CodeInsee;
		    $codeRIVOLI = $result_eligibiliteRue[0]->EligibiliteRueResult->CodeRIVOLI;
		}else{
				$code    = 'ineligible_street';
				$message = self::getMessage($code);
			return json_encode([
				'eligibility' => false,
				'code'        => $code,
				'message'     => $message,
				'result'      => $street_number,
				]);
		}

		if (isset($resNumberStreet->eligibility)  || (!isset($resNumberStreet->NumeroRue))) {
			return $resNumberStreet;
		}

		$client = self::EligibilityClient();
			# EligibiliteLogementNearnet
			$data                = new \stdClass();
			$data->Identifiant   = $ws_id;
			$data->CodeInsee     = $codeInsee;
			$data->CodeRIVOLI    = $codeRIVOLI;
			$data->NumeroRue     = $resNumberStreet->NumeroRue;
			$data->ComplementRue = $resNumberStreet->ComplementRue;
			$data->Typologie     = "INV_1";
			$result              = $client->EligibiliteLogementNearnet($data);

			self::checkReturnedCode($result->EligibiliteLogementNearnetResult->CodeRetour);

			$plug_id = $result->EligibiliteLogementNearnetResult->DetailPriseNearnet->IdentifiantPrise;

			# EligibilitePrise
			$data                   = new \stdClass();
			$data->Identifiant      = $ws_id;
			$data->IdentifiantPrise = $plug_id;
			$result                 = $client->EligibilitePrise($data);

			self::checkReturnedCode($result->EligibilitePriseResult->CodeRetour);

			$original_result = $result->EligibilitePriseResult->DetailPrise;

			foreach ($result->EligibilitePriseResult->DetailPrise as $key => $value)
			{
				$result->EligibilitePriseResult->DetailPrise->$key = self::translateCode($key, $value);
			}

			return json_encode([
				'eligibility' => true,
				'code'        => '',
				'message'     => '',
				'result'      => $result->EligibilitePriseResult->DetailPrise,
				'id_prise'    => $plug_id,
				'code_insee'  => isset($codeInsee) ? $codeInsee :'',
			]);
	}

	public static function bToChar($var){
		if ($var == '1') {
			$res = 'Oui';
		}else {
			$res = 'Non';
		}
		return $res;
	}

	public static function decodeToBinary($binary){
		$detail = [];
		$eligibilite = ['Eligible S', 'Eligible 10M' ,'Eligible 20M' ,'Eligible 30M' ,'Eligible 100M', 'Eligible VOIP', 'Eligible 200M' ,'Eligible 4' ,'Eligible G'];
		$i = 0;

		foreach ($eligibilite as $key => $value) {
			if (isset($binary[$key])) {
				$detail[$value] = self::bToChar($binary[$key]);
			}
		}

		return $detail;
	}

		public static function getData(Request $request) {

		$clientBsod = EligibilityAddress::select(array('id','accomodationId','zipcode','street_number_complement'));

		if ($request->has('zipcode')) {
			$clientBsod->where('zipcode', 'like', '%'.$request->zipcode.'%');
		}

		if ($request->has('street')) {
			$clientBsod->where('street_number_complement', 'like', '%'.$request->street.'%');
		}

		$btn_action = '<div class="text-right">';

		$btn_action .='<div style="float: right; padding-left: 3px;">
										<a title="Detail" href="{{ URL::route( \'BsodAdress.show\', array($id)) }}" class="btn btn-info btn-mini" target="_blank">  <i class="icon-eye-open"></i></a></div>';

		$btn_action .= '</div>';

		$datatable = Datatables::of($clientBsod)->edit_column('accomodationId', '<strong><span class=\'label label-success\'>{{$accomodationId}}</span></strong>')
											->edit_column('zipcode', '<strong>{{$zipcode}}</strong>')
											->edit_column('street_number_complement', '<strong>{{$street_number_complement}}</strong>')
											->add_column('action', '')->addColumn('action', $btn_action)->removeColumn('id');

		return $datatable->make();
	}
}
