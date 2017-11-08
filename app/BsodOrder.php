<?php namespace Provisioning;

use Illuminate\Database\Eloquent\Model;

class BsodOrder extends Model {

		protected $table    = 'bsod_orders';
		protected $dates    = ['deleted_at'];
		protected $fillable = [
								'numCommande',
								'dateCommande',
								'typeCommande',
								'comment',
								'bsod_client_id',
								'bsod_service_id',
								'appointment_id',
								'order_file_name',
		];

		public function bsodService(){
			return $this->belongsTo('Provisioning\BsodService','bsod_service_id');
		}

		public function bsodClient(){
			return $this->belongsTo('Provisioning\BsodClient','bsod_client_id');
		}

		public function appointment(){
			return $this->belongsTo('Provisioning\Appointment','appointment_id');
		}

		public function aupdateProspects(){
			return self::join('bsod_clients','bsod_clients.id','=','bsod_orders.bsod_client_id')
							->whereNull('bsod_clients.customerId')
							->whereNull('bsod_clients.identifiantAS')
							->get();
		}

		public function BsodOrderToBill(){
			return $this->hasMany('Provisioning\BsodOrderToBill');
		}
}
