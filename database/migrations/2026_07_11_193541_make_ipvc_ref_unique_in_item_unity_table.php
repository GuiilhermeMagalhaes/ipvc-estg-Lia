<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeIpvcRefUniqueInItemUnityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_unity', function (Blueprint $table) {
            $table->string('serial_number')->unique()->change();
            $table->string('ipvc_ref')->unique()->change();
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
            $table->dropUnique(['serial_number']);
            $table->dropUnique(['ipvc_ref']);
        });
    }
}
