<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKitUnityReserveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kit_unity_reserve', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            
            $table->unsignedBigInteger('kit_reserve_id');
            $table->unsignedBigInteger('kit_unity_id');
            
            $table->timestamps();

            
            $table->foreign('kit_reserve_id')
                  ->references('id')
                  ->on('kit_reserve')
                  ->onDelete('cascade'); 

            $table->foreign('kit_unity_id')
                  ->references('id')
                  ->on('kit_unity')
                  ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kit_unity_reserve');
    }
}
