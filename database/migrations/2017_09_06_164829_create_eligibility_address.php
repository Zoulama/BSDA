<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEligibilityAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eligibility_address', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('accomodationId')->unique();
            $table->string('street_number');
            $table->string('street_number_complement');
            $table->string('street');
            $table->string('zipcode');
            $table->string('city');
            $table->string('code_insee');
            $table->text('offres');
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
        Schema::dropIfExists('elligibility_addresses');
    }
}
