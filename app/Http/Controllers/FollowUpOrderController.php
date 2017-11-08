<?php namespace Provisioning\Http\Controllers;

use Provisioning\Http\Requests;
use Provisioning\Http\Controllers\Controller;
use Provisioning\ProspectAppointements;
use Illuminate\Http\Request;
use Provisioning\Helpers\PriseRendezVousHelper;
use Provisioning\Helpers\BsodHelper;
use Provisioning\Helpers\FollowUpBsodOrder;
//use Support\Helpers\ManangerFtpHelper;
use Provisioning\Client;
use Provisioning\Service;
use Provisioning\Order;
use File;
use Session;
use Response;
use Parser;
use Carbon\Carbon;
use Guzzle\Http\Client  as ClientHttp;

class FollowUpBsodOrderController  extends BaseController {

	public function index(){
		$res = ManangerFtpHelper::getDirectory('Commandes/CRFIBRESERVICE');
		$file = ManangerFtpHelper::getFile($res[2],'test.xml');
		$parser = new Parser();
		$xml_content = File::get('test.xml');
		$parsed = $parser::xml($xml_content);
		dd($parsed['suiv:commande'][0]['suiv:etape']);
	}
}