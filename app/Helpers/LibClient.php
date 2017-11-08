<?php

namespace Provisioning\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class LibClient
{

	/**
	 * @param $id
	 * @return array
	 */
	public static function getClientById($id)
	{
		$client = new Client();
		$request = new Request(
			'GET',
			str_replace(["__CLIENTID__", "__APITOKEN__"], [$id, env('CM_API_TOKEN')], env('CM_CLIENT_BY_ID')),
			[
				'curl' => [CURLOPT_CAINFO => env('CAINFO')]
			]
		);
		try {
			$response = $client->send($request);
			$data = json_decode($response->getBody(), true);
			return head($data);
		} catch (Exception $e) {
			abort(500, $e);
		}
	}
}
