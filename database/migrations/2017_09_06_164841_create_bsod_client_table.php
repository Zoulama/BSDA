<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBsodClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bsod_clients', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('gender');
            $table->string('telephone');
            $table->string('client_type');
            $table->string('customerId',25)->nullable();
            $table->string('identifiantAS',25)->nullable();
            $table->string('externalSubscriberId');
            $table->string('accomodationId');
            $table->unsignedInteger('eligibility_address_id')->index()->nullable();
            $table->foreign('eligibility_address_id')->references('id')->on('eligibility_address');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::dropIfExists('bsod_clients');
    }
}
