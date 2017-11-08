<?php

namespace Provisioning;

use Illuminate\Database\Eloquent\Model;

class CentileContext extends Model
{
    protected $table = 'centile_context';
    protected $fillable = ['context', 'reseller_context'];
}
