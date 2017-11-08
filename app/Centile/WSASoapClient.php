<?php

namespace Provisioning\Centile;

use Provisioning\Centile\WSASoap;
use Illuminate\Support\Facades\Log;

class WSASoapClient extends \SoapClient
{
	protected $headers;

	public function __doRequest($request, $location, $action, $version, $one_way = NULL) {
		$dom = new \DOMDocument();
		$dom->loadXML($request);
		$wsasoap = new WSASoap($dom);
		$wsasoap->addAction($action);
		$wsasoap->addTo($location);
		$wsasoap->addMessageID();
		$wsasoap->addReplyTo();
		$request = $wsasoap->saveXML();
		return parent::__doRequest($request, $location, $action, $version);
	}

	public function __soapCall($function_name, $arguments, $options = NULL, $input_headers = NULL, &$output_headers = NULL)
	{
		if ($this->headers)
			$input_headers = new \SoapHeader($this->headers['namespace'], $this->headers['name'], $this->headers['data']);

		$arguments = booleanToString($arguments);
		$arguments = convertNull($arguments);

		if (env('CENTILE_DEBUG', false)) {
			if (!in_array($function_name, ['isConnected', 'login']))
				Log::debug('Centile Soapcall: ' . $function_name . '(' . associativeArrayToString(head($arguments)) . ')');
		}

		return parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);
	}

	public function __call($function_name, $arguments)
	{
		return $this->__soapCall($function_name, $arguments);
	}

	public function setHeaders($namespace, $name, $data)
	{
		$this->headers = ['namespace' => $namespace, 'name' => $name, 'data' => $data];
	}

	public function unsetHeaders()
	{
		$this->headers = null;
	}
}
