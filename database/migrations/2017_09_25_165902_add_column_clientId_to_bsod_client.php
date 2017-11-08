<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnClientIdToBsodClient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bsod_clients', function($table){
           $table->unsignedInteger('clientID')->nullable()->after('accomodationId')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('bsod_clients', function ($table) {
           $table->dropColumn('clientID');
        });
    }
}
