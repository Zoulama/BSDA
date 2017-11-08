<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBsodOrderToBillTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bsod_order_to_bills', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('bsod_order_id')->index();
            $table->unsignedInteger('bsod_service_id')->index();
            $table->foreign('bsod_order_id')->references('id')->on('bsod_orders')->onDelete('cascade');
            $table->foreign('bsod_service_id')->references('id')->on('bsod_services')->onDelete('cascade');
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
        Schema::dropIfExists('bsod_order_to_bills');
    }
}
