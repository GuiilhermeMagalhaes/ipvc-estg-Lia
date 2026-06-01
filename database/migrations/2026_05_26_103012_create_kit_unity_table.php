<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKitUnityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kit_unity', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('lia_code');

            $table->unsignedBigInteger('kit_unity_state_id');

            $table->unsignedBigInteger('kit_id');

            $table->timestamps();

            $table->foreign('kit_unity_state_id')
                  ->references('id')
                  ->on('kit_unity_states');

            $table->foreign('kit_id')
                  ->references('id')
                  ->on('kits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kit_unity');
    }
}
