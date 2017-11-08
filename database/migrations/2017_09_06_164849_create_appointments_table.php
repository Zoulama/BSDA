<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('externalSubscriberId')->unique();
            $table->string('accomodationId');
            $table->string('ScheduleID');
            $table->string('CalendarTypeDesc');
            $table->string('appointment_date');
            $table->string('ShiftDesc');
            $table->string('startDate');
            $table->string('endDate');
            $table->string('appointmentType');
            $table->enum('type', ['prospect', 'customer']);
            $table->unsignedInteger('bsod_client_id')->index()->nullable();
            $table->enum('status', ['active', 'archived']);
            $table->foreign('bsod_client_id')->references('id')->on('bsod_clients');
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
        Schema::dropIfExists('appointments');
    }
}
