<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerialNumberAndIpvcRefToItemUnityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_unity', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->after('id'); 
            $table->string('ipvc_ref')->nullable()->after('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_unity', function (Blueprint $table) {
           $table->dropColumn(['serial_number', 'ipvc_ref']);
        });
    }
}
