<?php namespace Provisioning\Helpers;

use Illuminate\Support\Facades\Lang;
use FTP;
class ManangerFtpHelper{

	public static function getArFolderName(){
		return 'Commandes/ARFIBRESERVICE';
	}

	public static function getComPath(){
		return 'Commandes/COM/';
	}

	public  static function getArComPath(){
		return 'Commandes/ARCOM/';
	}

	public static function getArCptlPath(){
		return 'Commandes/ARCPTL/';
	}

	public static function getCrcptl(){
		return 'Commandes/CRCPTL/';
	}

	public static function getArFibreService(){
		return 'Commandes/ARFIBRESERVICE/';
	}

	public static function geCrFibreService(){
		return 'Commandes/CRFIBRESERVICE/';
	}

	public static function getArCptl(){
		return 'Commandes/ARCPTL/';
	}

	public static function geCrCptl(){
		return 'Commandes/CRCPTL/';
	}

	public static function getTmpFile(){
		return 'tmp.xml';
	}

	public static function getDirectory($directory_name){
		return FTP::connection()->getDirListing($directory_name);
	}

	public static function listFile($directory_name){
		return FTP::connection()->getDirListing($directory_name);
	}

	public static function putFile($fileFrom, $fileTo){
		return FTP::connection()->uploadFile($fileFrom,$fileTo);
	}

	public static function getFile($fileFrom, $fileTo){
		return  FTP::connection()->downloadFile($fileFrom, $fileTo);
	}

}