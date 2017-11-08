<?php namespace Provisioning;

use Illuminate\Database\Eloquent\Model;

class BsodService extends Model {

		protected $table    = 'bsod_services';
		protected $dates    = ['deleted_at'];
		protected $fillable = [
								'services',
		];

		public function bsodClient(){
			return $this->belongsToMany('Provisioning\BsodClient','bsod_orders');
		}

		public function bsodOrders(){
			return $this->hasMany('Provisioning\BsodOrder');
		}

		public function BsodOrderToBills(){
			return $this->hasMany('Provisioning\BsodOrderToBill');
		}
}
