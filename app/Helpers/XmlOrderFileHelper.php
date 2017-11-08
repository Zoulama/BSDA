<?php

namespace Provisioning\Helpers;
use SoapClient;
use Cache;

class XmlOrderFileHelper{

	public static $balises = [
					'En-tete' => [
							'Commande' 			=> ['numCommande','dateCommande','typeCommande'] ,
							'ClientDemandeur' 	=> ['codeClient'],
							'ClientFacture' 	=> ['codeClient'],
							'ClientFinal' 		=> ['codeClient','civilite','nom','prenom','numContact','email','typeClient'],
							'Acces' 			=> [
								'idAccesPrise',
								'adresse'			=> ['numeroDansVoie','libelleVoie', 'codeINSEE', 'codePostal','commune','scheduleid'],
								'identifiantClient',
								'scheduleid',
							],
					],
					'Detail' => [
							'Service' => [
								'FIBRE'			=> ['typeRaccordement','codeAction','CommentaireRaccordement'],
								'Data'			=> [
									'codeAction',
									'FluxData'		=> ['idFlux', 'codeAction', 'typeRaccordement'],
									'EquipementRef' => ['codeAction', 'numSequence'],
									'Option'		=> ['codeAction', 'option', 'valeur'],
								],
								'Equipement'	=> [
									'IAD'			=> ['numSequence','codeEAN13','numeroSerie','codeAction','codeActionOptionWifi'],
									'DD'			=> ['numSequence','codeEAN13','numeroSerie','codeAction','codeActionOptionWifi'],
									'typeLivraison'	=> 'typeLivraison',
								],
							]
					]
	];

	public static $DataXml = [];

	public function __construct($DataXml){
			self::$DataXml = $DataXml;
	}

	public static function getDataXml(){
		return self::$DataXml;
	}

	public static function openBalise($startBalise){
		return '<'.$startBalise;
	}

	public static function closeBalise($endBalise){
		return '</'.$endBalise.'>'.PHP_EOL;
	}

	public static function baliseComplete($var){
		return '<'.$var.'>';
	}

	public static function commandes(){
		$entete = array_keys(self::$balises)[0];
		$nCom = self::$balises['En-tete']['Commande'][0];
		$Dcom = self::$balises['En-tete']['Commande'][1];
		$tCom = self::$balises['En-tete']['Commande'][2];
		$data = self::$DataXml['Commande'];
		return '<'.array_keys(self::$balises['En-tete'])[0].' '.$nCom.'="'.$data['numCommande'].'" '.$Dcom.'="'.$data['dateCommande'].'" '.$tCom.'="'.$data['typeCommande'].'"/>'.PHP_EOL;
	}

	public static function clientFinal(){
		$entete = array_keys(self::$balises)[0];
		$data =  self::$DataXml['ClientFinal'];
		$clientFinal = '<'.array_keys(self::$balises['En-tete'])[3].' '.self::$balises['En-tete']['ClientFinal'][0].'="'.$data['codeClient'].'" ';
		$clientFinal .= self::$balises['En-tete']['ClientFinal'][1].'="'.$data['civilite'].'" ';
		$clientFinal .= self::$balises['En-tete']['ClientFinal'][2].'="'.$data['nom'].'" ';
		$clientFinal .= self::$balises['En-tete']['ClientFinal'][3].'="'.$data['prenom'].'" ';
		$clientFinal .= self::$balises['En-tete']['ClientFinal'][4].'="'.$data['numContact'].'" ';
		$clientFinal .= self::$balises['En-tete']['ClientFinal'][5].'="'.$data['email'].'" ';
		$clientFinal .= self::$balises['En-tete']['ClientFinal'][6].'="'.$data['typeClient'].'"/>'.PHP_EOL;

		return $clientFinal;
	}

	public static function acces(){
		$entete = array_keys(self::$balises)[0];
		$name = array_keys(self::$balises[$entete])[4];
		$addr = array_keys(self::$balises[$entete][$name])[1];
		$data = self::$DataXml[$name][$addr];
		$idAccesPrise = self::$DataXml[$name]['idAccesPrise'];

		$acces =  self::openBalise(array_keys(self::$balises[$entete])[4]).' '.self::$balises['En-tete']['Acces'][0].'="'.$idAccesPrise.'" ';
		if (isset(self::$DataXml[$name]['identifiantClient'])) {
			$acces .= self::$balises[$entete][$name][1].'="'.self::$DataXml[$name]['identifiantClient'].'">'.PHP_EOL;
		}else{
			$acces .= '>'.PHP_EOL;
		}

		$acces .= self::openBalise(array_keys(self::$balises[$entete][$name])[1]).' '.self::$balises[$entete][$name][$addr][0].'="'.$data['numeroDansVoie'].'" ';
		$acces .= self::$balises[$entete][$name][$addr][1].'="'.$data['libelleVoie'].'" ';
		$acces .= self::$balises[$entete][$name][$addr][2].'="'.$data['codeINSEE'].'" ';
		$acces .= self::$balises[$entete][$name][$addr][3].'="'.$data['codePostal'].'" ';
		$acces .= self::$balises[$entete][$name][$addr][4].'="'.$data['commune'].'"/>'.PHP_EOL;

		if (isset($data['scheduleid']) ) {
			$acces .= '<'.self::$balises[$entete][$name][2].'>'.$data['scheduleid'].'</'.self::$balises[$entete][$name][2].'>'.PHP_EOL;
		}

		$acces .= self::closeBalise(array_keys(self::$balises[$entete])[4]);

		return $acces;
	}

	public static function entete(){
		$entet = array_keys(self::$balises)[0];
		$entete = '<'.array_keys(self::$balises)[0].'>'.PHP_EOL;
		$entete .= self::commandes();
		$entete .='<'.array_keys(self::$balises[$entet])[1].' '.self::$balises[$entet]['ClientDemandeur'][0].'="CODEPI-FIBRE"/>'.PHP_EOL;
		$entete .='<'.array_keys(self::$balises[$entet])[2].' '.self::$balises[$entet]['ClientFacture'][0].'="CODEPI-FIBRE"/>'.PHP_EOL;
		$entete .= self::clientFinal();
		$entete .= self::acces();
		$entete .= self::closeBalise(array_keys(self::$balises)[0]);

		return $entete;
	}

	public static function fibre(){
		$detail = array_keys(self::$balises)[1];
		$service = array_keys(self::$balises['Detail'])[0];
		$colum_name = array_keys(self::$balises['Detail']['Service'])[0];
		$fibre = '';
		if (isset(self::$DataXml[$colum_name])) {
			$data = self::$DataXml[$colum_name];
			$fibre  = self::openBalise($colum_name).' '.self::$balises['Detail']['Service'][$colum_name][0].'="'.$data['typeRaccordement'].'" ';
			$fibre .= self::$balises['Detail']['Service'][$colum_name][1].'="'.$data['codeAction'].'">'.PHP_EOL;
			$fibre .= self::baliseComplete(self::$balises['Detail']['Service'][$colum_name][2]);
			$fibre .= $data['CommentaireRaccordement'];
			$fibre .= self::closeBalise(self::$balises['Detail']['Service'][$colum_name][2]);
			$fibre .= self::closeBalise($colum_name); 
		}

		return isset($fibre) ? $fibre : '';
	}

	public static function data(){
		$colum_name = array_keys(self::$balises['Detail']['Service'])[1];
		$flData     = array_keys(self::$balises['Detail']['Service'][$colum_name])[1];
		$equip      = array_keys(self::$balises['Detail']['Service'][$colum_name])[2];
		$opt        = array_keys(self::$balises['Detail']['Service'][$colum_name])[3];
		$detail     = array_keys(self::$balises)[1];
		$service    = array_keys(self::$balises['Detail'])[0];
		$data       = '';
		if (isset(self::$DataXml[$colum_name])) {
			$xmlData = self::$DataXml[$colum_name]['FluxData'];
			$data  = self::openBalise($colum_name).' '.self::$balises['Detail']['Service'][$colum_name][0].'="'.$xmlData['codeAction'].'">'.PHP_EOL;
			$data .= self::openBalise(array_keys(self::$balises['Detail']['Service'][$colum_name])[1]).' ';
			$data .= self::$balises['Detail']['Service'][$colum_name][$flData][0].'="'.$xmlData['idFlux'].'" ';
			$data .= self::$balises['Detail']['Service'][$colum_name][$flData][1].'="'.$xmlData['codeAction'].'" ';
			$data .= self::$balises['Detail']['Service'][$colum_name][$flData][2].'="'.$xmlData['typeRaccordement'].'">'.PHP_EOL;

			$data .= self::openBalise(array_keys(self::$balises['Detail']['Service'][$colum_name])[2]).' ';
			$data .= self::$balises['Detail']['Service'][$colum_name][$equip][0].'="'.$xmlData['EquipementRef']['codeAction'].'" ';
			$data .= self::$balises['Detail']['Service'][$colum_name][$equip][1].'="'.$xmlData['EquipementRef']['numSequence'].'"/>'.PHP_EOL;

			foreach ($xmlData['option'] as $dataOption) {
				$data .= self::openBalise(array_keys(self::$balises['Detail']['Service'][$colum_name])[3]).' ';
				$data .= self::$balises['Detail']['Service'][$colum_name][$opt][0].'="'.$dataOption['codeAction'].'" ';
				$data .= self::$balises['Detail']['Service'][$colum_name][$opt][1].'="'.$dataOption['option'].'" ';
				$data .= self::$balises['Detail']['Service'][$colum_name][$opt][2].'="'.$dataOption['valeur'].'"/>'.PHP_EOL;
			}

			$data .= self::closeBalise($flData);
			$data .= self::closeBalise($colum_name);
		}

		return isset($data) ? $data : '';
	}

	public static function equipement(){
		$colum_name = array_keys(self::$balises['Detail']['Service'])[2];
		$iad = array_keys(self::$balises['Detail']['Service'][$colum_name])[0];
		$equipement  = self::baliseComplete($colum_name).PHP_EOL;
		$typeL = array_keys(self::$balises['Detail']['Service'][$colum_name])[2];
		
		if (isset(self::$DataXml[$colum_name])) {
			$data  = self::$DataXml[$colum_name];

			$equipement .= self::openBalise(array_keys(self::$balises['Detail']['Service'][$colum_name])[0]).' ';
			$equipement .= self::$balises['Detail']['Service'][$colum_name][$iad][0].'="'.$data[$iad]['numSequence'].'" ';
			$equipement .= self::$balises['Detail']['Service'][$colum_name][$iad][1].'="'.$data[$iad]['codeEAN13'].'" ';
			$equipement .= self::$balises['Detail']['Service'][$colum_name][$iad][2].'="'.$data[$iad]['numeroSerie'].'" ';
			$equipement .= self::$balises['Detail']['Service'][$colum_name][$iad][3].'="'.$data[$iad]['codeAction'].'" ';
			if (isset($data[$iad]['codeActionOptionWifi'])) {
				$equipement .= self::$balises['Detail']['Service'][$colum_name][$iad][4].'="'.$data[$iad]['codeActionOptionWifi'].'">'.PHP_EOL;
			}else {
				$equipement .= '>'.PHP_EOL;
			}

			if (isset($data[$iad]['typeLivraison'])) {
				$equipement .= self::baliseComplete($typeL).$data[$iad]['typeLivraison'].self::closeBalise($typeL);
			}

			$equipement .= self::closeBalise($iad);

			$equipement .= self::closeBalise($colum_name);
		}
		return isset($equipement) ? $equipement : '';
	}

	public static function service(){
		return  array_keys(self::$balises['Detail'])[0];
	}

	public static function detail(){
		if (self::$DataXml['Commande']['typeCommande'] != 'R' ) {
			$detail  = self::baliseComplete(array_keys(self::$balises)[1]).PHP_EOL;
			$detail .= self::baliseComplete(self::service()).PHP_EOL;
			$detail .= self::fibre();
			$detail .= self::data();
			$detail .= self::closeBalise(self::service());
			$detail .= self::equipement();
			$detail .= self::closeBalise(array_keys(self::$balises)[1]);
		}

		return isset($detail) ? $detail : '';
	}

	public static function main(){
		$Message  ='<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
		$Message .='<Message xmlns="http://completel.com/CommandeClientTHD/CommandeClient">'.PHP_EOL;
		$Commande  = self::baliseComplete('Commande').PHP_EOL;
		$Commande .= self::entete();
		$Commande .= self::detail();
		$Commande .= self::closeBalise('Commande');
		$Message  .= $Commande;

		$Message .= self::closeBalise('Message');
		return $Message;
	}

}