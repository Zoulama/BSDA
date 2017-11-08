<?php

namespace Provisioning\Centile;

use GuzzleHttp\Client as HTTPClient;

class INSEENumber
{
	protected $id;
	protected $value;
	protected $label;

    public static function isValid($id)
    {
    	return self::find($id) ? true : false;
    }

    public static function find($id)
    {
    	$client = new HTTPClient;
    	$response = $client->get(env('INSEE_API_URL') . '?term=' . $id);
    	if (!$obj = head(json_decode($response->getBody())))
    		return false;
    	$insee = new self;
    	$insee->id = $obj->id;
    	$insee->value = $obj->value;
    	$insee->label = $obj->label;
    	return $insee;
    }
}
