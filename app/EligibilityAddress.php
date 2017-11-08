<?php namespace Provisioning;

use Illuminate\Database\Eloquent\Model;

class EligibilityAddress extends Model {

		protected $table    = 'eligibility_address';
		protected $dates    = ['deleted_at'];
		protected $fillable = [
								'accomodationId',
								'street_number',
								'street_number_complement',
								'street',
								'zipcode',
								'city',
								'code_insee',
								'offres',
		];
}
