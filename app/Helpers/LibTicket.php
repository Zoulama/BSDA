<?php

namespace Provisioning\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class LibTicket
{

	public static function getTicket($prestation)
	{
		$client = new Client();
		$request = new Request(
			'GET',
			str_replace(["__PRESTAID__", "__APITOKEN__"], [$prestation, env('SUPPORT_API_TOKEN')], env('SUPPORT_GETOPENEDTICKET')),
			[
				'curl' => [CURLOPT_CAINFO => env('CAINFO')]
			]
		);
		try {
			$response = $client->send($request);
			$data = json_decode($response->getBody());
			return $data;
		} catch (Exception $e) {
			abort(500, $e);
		}
	}
}
