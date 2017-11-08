<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCentileContext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       /* Schema::create('centile_context', function (Blueprint $table) {
            $table->increments('id');
            $table->string('context');
            $table->string('reseller_context');
            $table->integer('prestation_id')->unsigned();
            $table->timestamps();
            $table->foreign('prestation_id')->references('prestationID')->on('comptaPrestation')->onUpdate('cascade')->onDelete('cascade');
            $table->unique('prestation_id');
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('centile_context');
    }
}
