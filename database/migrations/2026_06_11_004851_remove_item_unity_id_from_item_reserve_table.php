<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveItemUnityIdFromItemReserveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_reserve', function (Blueprint $table) {

            $table->dropForeign(['item_unity_id']);
            $table->dropColumn('item_unity_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_reserve', function (Blueprint $table) {
            
            $table->unsignedBigInteger('item_unity_id')->nullable();
            $table->foreign('item_unity_id')->references('id')->on('item_unities');
        });
    }
}
