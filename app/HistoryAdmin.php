<?php

namespace Provisioning;

use Illuminate\Database\Eloquent\Model;

class HistoryAdmin extends Model {

	protected $table = "historyAdmin";

	protected $primaryKey = 'uniqID';

	public $timestamps = false;

}
