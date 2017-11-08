<?php

namespace Provisioning\Http\Controllers;

use Provisioning\BsodOrderToBill;

class BsodApiController extends Controller
{

	protected $bsodApiService;

	public function __construct()
	{
			//$this->bsodApiService = $bsodApiService;
	}

	public function bsodOrderToBill(){
		$bsodToBill = BsodOrderToBill::all()[0];//json_decode($value->bsodService->services)
//dd($bsodToBill);
		return response()->json(['data' => $bsodToBill->bsodService]);
	}

}
