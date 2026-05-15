<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LiaSpaceSpaceReserve extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lia_space_space_reserve', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lia_space_id');
            $table->unsignedBigInteger('space_reserve_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lia_space_space_reserve');
    }
}
