<?php namespace Provisioning;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model {

		protected $table    = 'appointments';
		protected $dates    = ['deleted_at'];
		protected $fillable = [
								'externalSubscriberId',
								'accomodationId',
								'ScheduleID',
								'CalendarTypeDesc',
								'appointment_date',
								'ShiftDesc',
								'startDate',
								'endDate',
								'appointmentType',
								'type',
								'bsod_client_id',
		];

		public function bsodClient(){
			return $this->belongsTo('Provisioning\BsodClient');
		}

		public function eligibilityAddress(){
			return $this->belongsTo('Provisioning\EligibilityAddress');
		}

		public function bsodOrder(){
			return $this->hasMany('Provisioning\BsodOrder');
		}
}