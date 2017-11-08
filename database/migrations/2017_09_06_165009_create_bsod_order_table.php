<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBsodOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bsod_orders', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('numCommande');
            $table->string('dateCommande');
            $table->string('typeCommande');
            $table->string('comment');
            $table->unsignedInteger('bsod_client_id')->index();
            $table->unsignedInteger('bsod_service_id')->index();
            $table->foreign('bsod_client_id')->references('id')->on('bsod_clients');
            $table->foreign('bsod_service_id')->references('id')->on('bsod_services')->onDelete('cascade');
            $table->unsignedInteger('appointment_id')->index();
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->enum('status', ['in_progress','completed']);
            $table->string('order_file_name');
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
        Schema::dropIfExists('bsod_orders');
    }
}
