<?php namespace Provisioning;

use Illuminate\Database\Eloquent\Model;

class BsodClient extends Model {

		protected $table    = 'bsod_clients';
		protected $dates    = ['deleted_at'];
		protected $fillable = [
								'first_name',
								'last_name',
								'accomodationId',
								'clientID',
								'eligibility_address_id',
		];

		public function bsodServices(){
			return $this->belongsToMany('Provisioning\BsodService','bsod_orders')
						->withPivot('id','numCommande','dateCommande','typeCommande','comment','bsod_client_id','bsod_service_id');
		}

		public function  bsodorders(){
			return $this->hasMany('Provisioning\BsodOrder');
		}

		public function appointments(){
			return $this->hasMany('Provisioning\Appointments','client_bsod_id');
		}

		public function externalSubscriberIdIsNull(){
			return is_null($this->externalSubscriberId);
		}

		public function customerIdIsNull(){
			return is_null($this->customerId);
		}

		public function identifiantASIsNull(){
			return is_null($this->identifiantAS);
		}

}
