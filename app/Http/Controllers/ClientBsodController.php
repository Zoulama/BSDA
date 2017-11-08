<?php namespace Provisioning\Http\Controllers;

use Provisioning\Http\Requests;
use Provisioning\Http\Controllers\Controller;
use Provisioning\Helpers\ClientBsodHelper;
use Provisioning\BsodClient;
use Provisioning\BsodService;
use Provisioning\EligibilityAddress;
use Illuminate\Http\Request;
use Session;

class ClientBsodController extends BaseController {

	public function index()
	{
		return view('bsod.clientbsod.index');
	}

	public function getDatatable(Request $request) {
		return ClientBsodHelper::getData($request);
	}

	public function show($id)
	{
		$client_bsod = BsodClient::find($id);
		$client_adresse = EligibilityAddress::where('accomodationId','=',$client_bsod->accomodationId)->first();
		return view('bsod.clientbsod.edit',compact('client_bsod','client_adresse'));
	}

	public function edit($id)
	{
		$client_bsod = BsodClient::find($id);
		$client_adresse = EligibilityAddress::where('accomodationId','=',$client_bsod->accomodationId)->first();
		return view('bsod.clientbsod.edit',compact('client_bsod','client_adresse'));
	}

	public function listeOrders($id){

		$clientBsod = BsodClient::find($id);

		$dataClient = [
		'id'			=> $clientBsod->id,
		'first_name'	=> $clientBsod->first_name,
		'last_name'		=> $clientBsod->last_name,
		];

		return view('bsod.clientbsod.list_order',compact('dataClient'));
	}

	public function getDataListOrder($id){
		return ClientBsodHelper::getDataListOrder($id);
	}


	public function update(Request $request)
	{
		if ($request->has('bsod_client_id') && $request->has('client_adresse_id') ) {
			$client_bsod = BsodClient::find($request->bsod_client_id);
			$client_adresse = EligibilityAddress::find($request->client_adresse_id);

			$client_bsod->gender     = $request->has('gender') ? $request->gender : $client_bsod->gender;
			$client_bsod->last_name  = $request->has('last_name') ? $request->last_name : $client_bsod->last_name;
			$client_bsod->first_name = $request->has('first_name') ? $request->first_name : $client_bsod->first_name;
			$client_bsod->client_type  = $request->has('typeClient') ? $request->typeClient : $client_bsod->typeClient;
			$client_bsod->telephone  = $request->has('telephone') ? $request->telephone : $client_bsod->telephone;
			$client_bsod->email      = $request->has('email') ? $request->email : $client_bsod->email;

			$client_adresse->street_number = $request->has('street_number') ? $request->street_number :$client_adresse->street_number;
			$client_adresse->street        = $request->has('street') ? $request->street : $client_adresse->street;
			$client_adresse->zipcode       = $request->has('zipcode') ? $request->zipcode : $client_adresse->zipcode;
			$client_adresse->city          = $request->has('city') ? $request->city : $client_adresse->city;

			$client_bsod->save();
			$client_adresse->save();
			Session::flash('success_message', trans('ClientBsod.success'));
		}else {
			Session::flash('error_message', trans('ClientBsod.error'));
		}

		return redirect()->route('ClientBsod.index');
	}


	public function destroy($id)
	{
		$clientBsod = BsodClient::find($id);
		return redirect()->route('ClientBsod.index');
	}

}
