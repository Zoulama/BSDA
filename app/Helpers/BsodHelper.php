<?php

namespace Provisioning\Helpers;
use SoapClient;
use Cache;
use Provisioning\BsodClient;
use Provisioning\BsodOrder;
use Provisioning\EligibilityAddress;
use Provisioning\Appointment;
use Provisioning\BsodOrderToBill;

class BsodHelper
{
	public static $typeRendezVous   = ['RACC','RACCCLEM','RACCLM','RACCDELTA','RACCPRO','SAV','SAVLM','SAVDELTA','SAVPRO','PRESTA','PRESTALM','PRESTADELTA','PRESTAPRO'];
	public static $RACC             = ['RACC', 'RACCCLEM', 'RACCLM', 'RACCDELTA', 'RACCPRO'];
	public static $PRESTA           = ['PRESTA', 'PRESTALM', 'PRESTADELTA', 'PRESTAPRO'];
	public static $SAV              = ['SAV', 'SAVLM', 'SAVDELTA', 'SAVPRO'];
	public static $typeRV           = [
		'prospect' => ['RACC'],
		'customer' => ['RACC','RACCCLEM','RACCLM','RACCDELTA','RACCPRO','SAV','SAVLM','SAVDELTA','SAVPRO','PRESTA','PRESTALM','PRESTADELTA','PRESTAPRO'],
	];

	public static $type_liv    = ['RDV','EXP','RMP','RACC','RACCCLEM','RACCLM','RACCDELTA','RACCPRO','PNP'];
	public static $Code_action = ['C','M','R','D','A','NA'];
	public static $typeC       = ['C','M','R','D','A','W','X','F','NA'];
	public static $type_racc   = ['RE','AC','INST','ACI','REACI'];

	public static $label_typeC = [
					'C'		=> 'CREATION',
					'M'		=> 'MODIFICATION',
					'R'		=> 'RESILIATION',
					'D'		=> 'SUSPENSION',
					'A'		=> 'REACTIVATION',
					'W'		=> '',
					'X'		=> '',
					'F'		=> 'ANNULATION',
					'NA'	=> 'AUCUNE ACTION',
	];

	public static $typeRacc = [
					'RE'	=> 'Réservation',
					'AC'	=> 'Activation',
					'INST'	=> 'Installation',
					'REAC'	=> 'Réservation et Activation',
					'ACI'	=> 'Activation et Installation',
					'REACI'	=> 'Réservation, Activation et Installation',
	];

	public static $optionPrestaCode = [
				'COS_VOIX_0.2'			=> 'BSOD_COS_VOIX_0.2',
				'COS_VOIX_0.4'			=> 'BSOD_COS_VOIX_0.4',
				'COS_VOIX_1.2'			=> 'BSOD_COS_VOIX_1.2',
				'COS_DATA_1'			=> 'BSOD_COS_DATA_1',
				'COS_DATA_2'			=> 'BSOD_COS_DATA_2',
				'COS_DATA_4'			=> 'BSOD_COS_DATA_4',
				'GTR4HO'				=> 'BSOD_GTR4HO',
				'LONGUEUR_SUPPLEMENT'	=> 'BSOD_LONGUEUR_SUPPLEMENT',
	];
	public static $basicPrestaCode =[
				'NETS'		=> 'BSOD_NETS',
				'NET10M'	=> 'BSOD_NET10M',
				'NET20M'	=> 'BSOD_NET20M',
				'NET30M'	=> 'BSOD_NET30M',
				'NET100M'	=> 'BSOD_NET100M',
				'NET200M'	=> 'BSOD_NET200M',
				'NET500M'	=> 'BSOD_NET500M',
				'NETVOIP'	=> 'BSOD_NETVOIP',
	];

	public static function InitClient($path)
	{
		$options = [
			'trace'        => 1,
			'exceptions'   => false,
			'soap_version' => SOAP_1_2,
			'cache_wsdl' => WSDL_CACHE_NONE,
		];
		$wsdl = base_path()."/resources/soap/".$path.".wsdl";

		return new SoapClient($wsdl, $options);
	}

	public static function checkDtataClient($path){
		$client = self::InitClient($path);
		$retailerId = "CODEPI";
		$vendorCode = "110834";
		$data              = new \stdClass();

		if ($path === "infosclients") {
			$data->Retailer    = $retailerId;
			$data->VendorCode  = $vendorCode;
		}else{
			$data->retailerId  = trim($retailerId);
			$data->vendorCode  = trim($vendorCode);
		}

		return [
				'client'	=> $client,
				'data'		=> $data,
		];
	}

	public static function getComFolder(){
		return 'CDPFIBRE_COM_';
	}

	public static function getArcomFolder(){
		return 'CDPFIBRE_ARCOM_';
	}

	public static function getArcptlFolder(){
		return 'CDPFIBRE_ARCPTL_';
	}

	public static function getArFolder(){
		return 'CDPFIBRE_ARFIBRESERVICE_';
	}

	public static function getCrFolder(){
		return 'CDPFIBRE_CRFIBRESERVICE_';
	}

	public static function getCrcptlFolder(){
		return 'CDPFIBRE_CRCPTL_';
	}

	public static function getSviFolder(){
		return 'SVICDP_';
	}

	public static function getPanneFolder(){
		return 'PANNECDP_';
	}

	public static function getPanneCloseFolder(){
		return 'PANNECLOSECDP_';
	}

	public static function uctrans($text)
	{
		return ucwords(trans($text));
	}

	public static function prepareOrdersValues($id, $type){
		$collection = BsodHelper::$typeC;
		$form_input = [];
		$typeAction = trim($type);
		if ($typeAction === 'createC') {
				$form_input['typeC'] = array_only($collection,['0']);
				$form_input['Code_action']			= array_only(BsodHelper::$Code_action,['0']);
				$form_input['codeAction_EquRef']	= array_only($collection,['0','2','8']);
				$form_input['type_liv']				= BsodHelper::$type_liv;
				$form_input['type_racc']			= BsodHelper::$type_racc;

				if (isset(BsodOrder::all()->last()->id)) {
					$last_inserId = BsodOrder::all()->last()->id +1;
					$last_inserId = 'CDP'.$last_inserId;
				}else{
					$last_inserId = 'CDP0';
				}

				$form_input['last_inserId'] = $last_inserId;
		}

		if ($typeAction === 'editC') {
			$numCommande = BsodOrder::find($id)->numCommande;
			$form_input['typeC'] = array_except($collection,['0']);
				$form_input['Code_action']			= array_except(BsodHelper::$Code_action,[0]);
				$form_input['codeAction_EquRef']	= array_only($collection,['8']);
				$form_input['type_racc']			= array_only(BsodHelper::$type_racc,['2','3']);
				$form_input['type_liv']				= BsodHelper::$type_liv;
				$form_input['order_id']				= $id;
				$form_input['numCommande']			= $numCommande;
		}

		return $form_input;
	}

	public static function transformeOffer($offers){

		$options = ['COS_VOIX_0.2','COS_VOIX_0.4','COS_VOIX_1.2','COS_DATA_1','COS_DATA_2','COS_DATA_4','GTR4HO','LONGUEUR_SUPPLEMENT'];
		$offers = (array)$offers;
		$basics = [];
		foreach ($offers as $key => $value) {
			$var = explode(' ', $key);
			if ($value === 'Oui' && $var[1] != 'VOIP' && $var[1] != 'S') {
				$basics [] = 'NET'.$var[1];
			}
		}

		return ['basic' => $basics,'option' => $options];
	}

	public static function controlRdvType($typeRDV){
		if (!in_array($typeRDV,['prospect','customer'])) {
			abort(500);
		}
	}
}