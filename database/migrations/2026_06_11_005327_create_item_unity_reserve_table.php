<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemUnityReserveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_unity_reserve', function (Blueprint $table) {
           $table->bigIncrements('id');
            
            $table->unsignedBigInteger('item_reserve_id');
            $table->foreign('item_reserve_id')
                ->references('id')
                ->on('item_reserve')
                ->onDelete('cascade');
                
            $table->unsignedBigInteger('item_unity_id');
            $table->foreign('item_unity_id')
                ->references('id')
                ->on('item_unity') 
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_unity_reserve');
    }
}
