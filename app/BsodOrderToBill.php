<?php

namespace Provisioning;

use Illuminate\Database\Eloquent\Model;

class BsodOrderToBill extends Model
{
	protected $table = 'bsod_order_to_bills';
	protected $fillable = [
		'bsod_order_id',
		'bsod_service_id',
	];

	public function bsodOrder(){
		return $this->belongsTo('Provisioning\BsodOrder');
	}

	public function bsodService(){
		return $this->belongsTo('Provisioning\BsodService');
	}
}
