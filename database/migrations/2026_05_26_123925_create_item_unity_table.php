<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemUnityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_unity', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('lia_code')->unique();

            $table->unsignedBigInteger('item_id');

            $table->unsignedBigInteger('kit_unity_id')
                  ->nullable();

            $table->unsignedBigInteger('item_unity_state_id');

            $table->timestamps();

            $table->foreign('item_id')
                  ->references('id')
                  ->on('item');

            $table->foreign('kit_unity_id')
                  ->references('id')
                  ->on('kit_unity');

            $table->foreign('item_unity_state_id')
                  ->references('id')
                  ->on('item_unity_states');
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

            $table->dropForeign(['item_id']);

            $table->dropForeign(['kit_unity_id']);

            $table->dropForeign(['item_unity_state_id']);
        });

        Schema::dropIfExists('item_unity');
    }
}
